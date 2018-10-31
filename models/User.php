<?php

namespace clement\rest\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\filters\RateLimitInterface;
use yii\web\IdentityInterface;
use clement\rest\components\Configs;
use Lcobucci\JWT\Signer\Hmac\Sha256;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email_validate_token
 * @property string $email
 * @property int $role
 * @property int $status
 * @property string $avatar
 * @property int $vip_lv
 * @property int $created_at
 * @property int $updated_at
 */
class User extends ActiveRecord implements IdentityInterface, RateLimitInterface
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return Configs::instance()->userTable;
    }
    /**
     * {@inheritdoc}
     */
     public function rules()
     {
         return [
             ['status', 'default', 'value' => self::STATUS_ACTIVE],
             ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
         ];
     }
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    // 返回在单位时间内允许的请求的最大数目，例如，[10, 60] 表示在60秒内最多请求10次。
    public function getRateLimit($request, $action)
    {
        // var_dump("c");
        return [30, 100];
    }

    // 返回剩余的允许的请求数。
    public function loadAllowance($request, $action)
    {
        return [$this->allowance, $this->allowance_updated_at];
    }

    // 保存请求时的UNIX时间戳。
    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $this->allowance = $allowance;
        $this->allowance_updated_at = $timestamp;
        $this->save();
    }

    /**
     * {@inheritdoc}
     */
    public function loginByAccessToken($token, $type = null)
    {
        return static::findIdentityByAccessToken($token, $type);
    }

    /**
     * Finds user by username.
     *
     * @param string $username
     *
     * @return null|static
     */
    public static function findByUsername($username)
    {
        //var_dump(static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE,]));
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE,]);

    }
    public static function CheckUserName($username){
        return static::findOne(['username' => $username]);
    }

    /**
     * Finds user by password reset token.
     *
     * @param string $token password reset token
     *
     * @return null|static
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid.
     *
     * @param string $token password reset token
     *
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['passwordResetTokenExpire'];

        return $timestamp + $expire >= time();
    }

    /**
     * Validates access_token.
     *
     * @param string $token token to validate
     *
     * @return bool if token provided is valid for current user
     */
    public static function isAccessTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $data = Yii::$app->jwt->getValidationData(); // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer(Yii::getAlias("@restIssuer"));
        $data->setAudience(Yii::getAlias("@restAudience"));
        $data->setId(Yii::getAlias("@restId")+strtotime(date('Y-m-d',time())), true);
        if (is_string($token))
            $token = Yii::$app->jwt->getParser()->parse($token);

        return $token->validate($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password.
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token.
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new api access token.
     */
    public function generateAccessToken()
    {

        // $this->access_token = Yii::$app->security->generateRandomString() . '_' . time();
        $signer = new Sha256();
        $token = Yii::$app->jwt->getBuilder()->setIssuer(Yii::getAlias("@restIssuer"))// Configures the issuer (iss claim)
        ->setAudience(Yii::getAlias("@restAudience"))// Configures the audience (aud claim)
        ->setId(Yii::getAlias("@restId")+ strtotime(date('Y-m-d',time())), true)
            ->setExpiration(time() + Yii::$app->params['accessTokenExpire'])// Configures exp time
            ->setIssuedAt(time())// Configures the time that the token was issue (iat claim)

            ->sign($signer, Yii::$app->jwt->key)
            ->getToken(); // Retrieves the generated token
        //$this->auth_key=$this->id;
        $this->access_token = (string)$token;
    }


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        // if token is not valid
        if (!static::isAccessTokenValid($token)) {
            throw new \yii\web\UnauthorizedHttpException('token is invalid.');
        }

        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
         // throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

     /**
      * Removes password reset token
      */
     public function removePasswordResetToken()
     {
         $this->password_reset_token = null;
     }

     public static function getDb()
     {
         return Configs::userDb();
     }

    /**
     * {@inheritdoc}
     */

    // /**
    //  * @return \yii\db\ActiveQuery
    //  */
    // public function getCcStudents()
    // {
    //     return $this->hasMany(CcStudent::className(), ['id' => 'id']);
    // }
    //

}
