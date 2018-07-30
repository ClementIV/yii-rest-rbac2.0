<?php

namespace clement\rest\controllers;

use Yii;
use clement\rest\models\form\Login;
use clement\rest\models\form\PasswordResetRequest;
use clement\rest\models\form\ResetPassword;
use clement\rest\models\SignupForm;
use clement\rest\models\form\ChangePassword;
use clement\rest\models\User;
use clement\rest\models\searchs\User as UserSearch;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\base\UserException;
use yii\mail\BaseMailer;
use clement\rest\controllers\base\BaseController;
use yii\helpers\ArrayHelper;
use clement\rest\components\Configs;
/**
 * @author clement <fyqnankai@gmial.com>
 */
class UserController extends BaseController
{
    private $_oldMailPath;

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['delete'], $actions['create'], $actions['index'], $actions['view']);
        return $actions;
    }
    protected function verbs()
    {
       return ArrayHelper::merge(
           parent::verbs(),
           [
               'index' => ['GET','OPTIONS'],
               'assign' => ['POST','GET','OPTIONS'],
               'check' => ['POST','OPTIONS'],
               'signup' => ['POST','OPTIONS'],
               'view' => ['GET','OPTIONS'],
               'revoke' => ['POST','GET','OPTIONS'],
           ]
       );
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (Yii::$app->has('mailer') && ($mailer = Yii::$app->getMailer()) instanceof BaseMailer) {
                /* @var $mailer BaseMailer */
                $this->_oldMailPath = $mailer->getViewPath();
                $mailer->setViewPath('@clement/rest/mail');
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {

        if ($this->_oldMailPath !== null) {
            Yii::$app->getMailer()->setViewPath($this->_oldMailPath);
        }
        return parent::afterAction($action, $result);
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['index']);
        }
        try{
            $searchModel = new UserSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $dataProvider;
        }catch(Exception $e)
        {
            throw new Exception($e);
        }


    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['view']);
        }
        try{
            return $this->findModel($id);
        }catch(Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        try{
            return $this->findModel($id)->delete();
        }catch(Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Login
     * @return string
     */

    /**
     * Signup new user
     * @return string
     */
    public function actionSignup()
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['signup']);
        }
        try{
            $model = new SignupForm();
            $model->setAttributes(Yii::$app->request->post());
            $user = $model->signup();
            if ($user !=null) {
                return ["success"=>true,"message"=>"创建成功！"];
            }else{
                return ["success"=>false,"message"=>"参数错误！"];
            }
        }catch(Exception $e){
            throw new Exception($e);
        }



    }

    /**
     * Request reset password
     * @return string
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequest();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
                'model' => $model,
        ]);
    }

    /**
     * Reset password
     * @return string
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPassword($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
                'model' => $model,
        ]);
    }

    /**
     * Reset password
     * @return string
     */
    public function actionChangePassword()
    {
        $model = new ChangePassword();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->change()) {
            return $this->goHome();
        }

        return $this->render('change-password', [
                'model' => $model,
        ]);
    }

    /**
     * Activate new user
     * @param integer $id
     * @return type
     * @throws UserException
     * @throws NotFoundHttpException
     */
    public function actionActivate($id)
    {
        /* @var $user User */
        $user = $this->findModel($id);
        if ($user->status == User::STATUS_INACTIVE) {
            $user->status = User::STATUS_ACTIVE;
            if ($user->save()) {
                return $this->goHome();
            } else {
                $errors = $user->firstErrors;
                throw new UserException(reset($errors));
            }
        }
        return $this->goHome();
    }

    public function actionCheck(){
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['check']);
        }
        try{
            $username=Yii::$app->request->post("username");
            $user = User::CheckUserName($username);
            if($user!=null)
            {
                return ["success"=>true,"isNewName"=>false];
            }else{
                return ["success"=>true,"isNewName"=>true];
            }
        }catch(Exception $e){
            throw new Exception ($e);
        }

    }
    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
