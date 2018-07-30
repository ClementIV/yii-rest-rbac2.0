<?php

namespace clement\rest\controllers;

use Yii;
use clement\rest\models\Route;
use yii\web\Controller;
use yii\rest\ActiveController;
use yii\filters\VerbFilter;
use clement\rest\controllers\base\BaseController;
use yii\helpers\ArrayHelper;
/**
 * Description of RuleController
 *
 * @author clement <fyqnankai@gmial.com>
 * @since 2.0
 */
class RouteController extends BaseController
{
    //public $modelClass = 'clement\rest\models\Route';
    public $enableCsrfValidation = false;
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
               'delete' => ['DELETE', 'OPTIONS'],
               'create' => ['POST', 'OPTIONS'],
               'index' => ['GET','OPTIONS'],
               'assign' => ['POST','GET','OPTIONS'],
               'remove' => ['POST','OPTIONS'],
               'refresh' => ['POST','OPTIONS'],
           ]
       );
   }
    /**
     * Lists all Route models.
     * @return mixed
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['index']);
        }
        try{
            $model = new Route();
            Yii::$app->getResponse()->format = 'json';
            return $model->getRoutes();
        }catch(Exception $e){
            throw new Exception($e);
        }
    }

    /**
     * Creates a new AuthItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['create']);
        }
        try{
            $routes = Yii::$app->getRequest()->post('route', '');
            $routes = preg_split('/\s*,\s*/', trim($routes), -1, PREG_SPLIT_NO_EMPTY);
            $methods = Yii::$app->getRequest()->post('methods', '');
            $model = new Route();
            $model->addNewMethod($routes,$methods);
            return $model->getRoutes();
        }catch(Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Assign routes
     * @return array
     */
    public function actionAssign()
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['assign']);
        }
        try{
            $routes = Yii::$app->getRequest()->post('routes', []);
            $model = new Route();
            $model->addNew($routes);
            //var_dump();die();

            Yii::$app->getResponse()->format = 'json';

            return $model->getRoutes();
        }catch(Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Remove routes
     * @return array
     */
    public function actionRemove()
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['remove']);
        }
        try{
            $routes = Yii::$app->getRequest()->post('routes', []);
            $model = new Route();
            $model->remove($routes);
            Yii::$app->getResponse()->format = 'json';
            return $model->getRoutes();
        }catch(Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Refresh cache
     * @return type
     */
    public function actionRefresh()
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['refresh']);
        }
        try{
            $model = new Route();
            $model->invalidate();
            Yii::$app->getResponse()->format = 'json';
            return $model->getRoutes();
        }catch(Exception $e){
            throw new Exception($e);
        }

    }
}
