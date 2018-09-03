<?php

namespace clement\rest\components;

use Yii;
use clement\rest\models\AuthItem;
use clement\rest\models\searchs\AuthItem as AuthItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\NotSupportedException;
use yii\filters\VerbFilter;
use clement\rest\models\Item;
use clement\rest\controllers\base\BaseController;
use yii\helpers\ArrayHelper;
/**
 * AuthItemController implements the CRUD actions for AuthItem model.
 *
 * @property integer $type
 * @property array $labels
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class ItemController extends BaseController
{

    public $enableCsrfValidation = false;
    /**
     * @inheritdoc
     */
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
                'delete' => ['DELETE', 'OPTIONS'],
                'create' => ['POST', 'OPTIONS'],
                'index' => ['GET', 'OPTIONS'],
                'update' => ['PUT', 'PATCH','POST', 'OPTIONS'],
                'assign' => ['POST','GET','OPTIONS'],
                'view'=>['GET','OPTIONS'],
                'view-path'=>['GET','OPTIONS'],
                'remove' => ['POST','OPTIONS']
            ]
        );
    }
    /**
     * @inheritdoc
     */

    /**
     * Lists all AuthItem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['index']);
        }
        $searchModel = new AuthItemSearch(['type' => $this->type]);
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
        return $dataProvider;
    }

    /**
     * Displays a single AuthItem model.
     * @param  string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['view']);
        }
        $model = $this->findModel($id);

        return  ['model'=>$model,'items'=>$model->getItems(),];
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
        $model = new AuthItem(null);

        $model->type = $this->type;
        try{

             // $model->load(Yii::$app->getRequest()->post());
             // return $model;
            if ($model->load(Yii::$app->getRequest()->post()) && (!$model->find($model['name']))&&$model->save()) {
                return ['success'=>true,'message'=>'创建成功！'];
            }
            return ['success'=>false,'message'=>'参数错误！'];

        } catch(Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Updates an existing AuthItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param  string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['update']);
        }
        $model = $this->findModel($id);
        try{
            if ($model->load(Yii::$app->getRequest()->post()) && $model->save()) {
                return ['success'=>true,'message'=>'更新成功！'];
            }else{
                return ['success'=>false,'message'=>'更新失败，参数错误'];
            }
        }catch (Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Deletes an existing AuthItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param  string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['delete']);
        }
        try{
            $model = $this->findModel($id);
            Configs::authManager()->remove($model->item);
            Helper::invalidate();
            return ['success'=>true,'message'=>'已成功删除'];
        }catch( Exception $e){
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
        try {
            if ($request->getIsOptions()) {
                return $this->ResponseOptions($this->verbs()['assign']);
            }
            $items = Yii::$app->getRequest()->post('items', []);
            $model = $this->findModel($id);
            $success = $model->addChildren($items);

            return array_merge($model->getItems(), ['success' => $success]);
        } catch(Exception $e){
            throw new Exception ($e);
        }

    }

    /**
     * Assign or remove items
     * @param string $id
     * @return array
     */
    public function actionRemove($id)
    {
        $request = \Yii::$app->request;
        if ($request->getIsOptions()) {
            return $this->ResponseOptions($this->verbs()['remove']);
        }
        $items = Yii::$app->getRequest()->post('items', []);
        $model = $this->findModel($id);
        $success = $model->removeChildren($items);

        return array_merge($model->getItems(), ['success' => $success]);
    }

    /**
     * @inheritdoc
     */
    public function getViewPath()
    {
        return $this->module->getViewPath() . DIRECTORY_SEPARATOR . 'item';
    }

    /**
     * Label use in view
     * @throws NotSupportedException
     */
    public function labels()
    {
        throw new NotSupportedException(get_class($this) . ' does not support labels().');
    }

    /**
     * Type of Auth Item.
     * @return integer
     */
    public function getType()
    {

    }

    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $auth = Configs::authManager();
        $item = $this->type === Item::TYPE_ROLE ? $auth->getRole($id) : $auth->getPermission($id);
        if ($item) {
            return new AuthItem($item);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
