<?php
namespace clement\rest\models;
use yii;
use yii\base\Model;
use clement\rest\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{

    public $username;
    public $password;
    public $status;
    public $_user;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => Yii::$app->getUser()->identityClass, 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            [['password','status'], 'required'],
            [['status'], 'integer'],
            ['password', 'string', 'min' => 6]
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->setPassword($this->password);
        $user->status = $this->status;
        $user->generateAuthKey();

        return $user->save() ? $user : null;
    }
    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
