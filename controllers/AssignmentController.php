<?php

namespace clement\rest\controllers;

use Yii;
use clement\rest\models\Assignment;
use clement\rest\models\searchs\Assignment as AssignmentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use clement\rest\controllers\base\BaseController;
use yii\helpers\ArrayHelper;
/**
 * AssignmentController implements the CRUD actions for Assignment model.
 *
 *  @author clement <fyqnankai@gmial.com>
 * @since 1.0
 */
class AssignmentController extends BaseController
{
    public $userClassName;
    public $usernameField = 'username';

    public $searchClass;



    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->userClassName === null) {
            $this->userClassName = Yii::$app->getUser()->identityClass;
            $this->userClassName = $this->userClassName ? : 'clement\rest\models\User';
        }
    }
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
               'view' => ['GET','OPTIONS'],
               'revoke' => ['POST','GET','OPTIONS'],
           ]
       );
    }
    /**
     * @inheritdoc
     */

    /**
     * Lists all Assignment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['index']);
        }
        try{
            if ($this->searchClass === null) {
                $searchModel = new AssignmentSearch;
                $dataProvider = $searchModel->search(Yii::$app->getRequest()->getQueryParams(), $this->userClassName, $this->usernameField);
                //var_dump(Yii::$app->getRequest()->getQueryParams());die();
            } else {
                $class = $this->searchClass;
                $searchModel = new $class;
                $dataProvider = $searchModel->search(Yii::$app->getRequest()->getQueryParams());
            }
            //
            return  $dataProvider;

        } catch(Exception $e){
            throw new Exception($e);
        }

    }
    public function actionSearch()
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['index']);
        }
        try{
            if ($this->searchClass === null) {
                $searchModel = new AssignmentSearch;
                $res = $searchModel->searchPage(Yii::$app->getRequest()->getQueryParams(), $this->userClassName);
            } else {
                $class = $this->searchClass;
                $searchModel = new $class;
                $dataProvider = $searchModel->searchPage(Yii::$app->getRequest()->getQueryParams());
            }
            //
            return  $res;

        } catch(Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Displays a single Assignment model.
     * @param  integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['view']);
        }
        try{
            $model = $this->findModel($id);

            return   ['model'=>$model,'items'=>$model->getItems(),];
        }catch(Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Assign items
     * @param string $id
     * @return array
     */
    public function actionAssign($id)
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['assign']);
        }
        try{
            $items = Yii::$app->getRequest()->post('items', []);
            $model = new Assignment($id);
            $success = $model->assign($items);
            Yii::$app->getResponse()->format = 'json';
            return array_merge($model->getItems(), ['success' => $success]);
        }catch(Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Assign items
     * @param string $id
     * @return array
     */
    public function actionRevoke($id)
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['revoke']);
        }
        try{
            $items = Yii::$app->getRequest()->post('items', []);
            $model = new Assignment($id);
            $success = $model->revoke($items);
            Yii::$app->getResponse()->format = 'json';
            return array_merge($model->getItems(), ['success' => $success]);
        }catch(Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Finds the Assignment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param  integer $id
     * @return Assignment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $class = $this->userClassName;
        if (($user = $class::findIdentity($id)) !== null) {
            return new Assignment($id, $user);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
