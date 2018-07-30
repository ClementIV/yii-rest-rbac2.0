<?php

namespace clement\rest\controllers;

use Yii;
use clement\rest\models\Menu;
use clement\rest\models\searchs\Menu as MenuSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use clement\rest\components\Helper;
use clement\rest\controllers\base\BaseController;
use yii\helpers\ArrayHelper;
/**

 * MenuController implements the CRUD actions for Menu model.
 *
 * @author clement <fyqnankai@gmial.com>
 * @since 1.0
 */
class MenuController extends BaseController
{

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['delete'], $actions['create'], $actions['index'], $actions['view'],$actions['update']);
        return $actions;
    }
    protected function verbs()
    {
       return ArrayHelper::merge(
           parent::verbs(),
           [
               'index' => ['GET','OPTIONS'],
               'view' => ['GET','OPTIONS'],
               'create'=>['POST','OPTIONS'],
               'update'=>['POST','OPTIONS'],
               'delete'=>['DELETE','OPTIONS'],
           ]
       );
    }

    /**
     * Lists all Menu models.
     * @return mixed
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['index']);
        }
        try{
            return Menu::getPageMenus(Yii::$app->request->queryParams);

        }catch(Exception $e)
        {
            throw new Exception($e);
        }



    }

    /**
     * list all menus
     * @return json list
     */
    public function actionGetMenus()
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['index']);
        }
        try{
            return Menu::getMenuSource();
        }catch(Exception $e){
            throw new Exception($e);
        }
    }

    /**
     * list all menus route
     * @return json list
     */
    public function actionGetSavedRoutes()
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['index']);
        }
        try{
            return Menu::getSavedRoutes();
        }catch(Exception $e){
            throw new Exception($e);
        }
    }
    /**
     * Displays a single Menu model.
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
            return $this->findModel($id);
        }catch(Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Creates a new Menu model.
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
            $model = new Menu;

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Helper::invalidate();
                return ["success"=> true];
            } else {
                return ["success"=> false];
            }
        }catch(Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Updates an existing Menu model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param  integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['update']);
        }
        try{
            $model = $this->findModel($id);
            if ($model->menuParent) {
                $model->parent_name = $model->menuParent->name;
            }
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Helper::invalidate();
                return ["success"=> true];
            } else {
                return ["success"=> false];
            }
        }catch(Exception $e){
            throw new Exception($e);
        }
    }

    /**
     * Deletes an existing Menu model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param  integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['delete']);
        }
        $this->findModel($id)->delete();
        Helper::invalidate();

        return ["success"=> true];
    }

    /**
     * Finds the Menu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param  integer $id
     * @return Menu the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Menu::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
