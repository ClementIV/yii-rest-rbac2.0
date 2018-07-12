<?php

namespace clement\rest\controllers;

use Yii;
use clement\rest\models\BizRule;
use clement\rest\models\searchs\BizRule as BizRuleSearch;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use clement\rest\components\Helper;
use clement\rest\components\Configs;
use clement\rest\controllers\base\BaseController;
use yii\helpers\ArrayHelper;
/**
 * @author clement <fyqnankai@gmial.com>
 * <fyqnankai@gmail.com>
 */
class RuleController extends BaseController
{


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
                'update' => ['PUT', 'PATCH', 'OPTIONS'],
                'view' =>['GET','OPTIONS']
            ]
        );
    }

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
        try{
            $searchModel = new BizRuleSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
            //var_dump($dataProvider);die();
            return $dataProvider;
        }catch(Exception $e){
            throw new Exception($e);
        }

    }

    /**
     * Displays a single AuthItem model.
     * @param  string $id
     * @return mixed
     */
    public function actionView($id)
    {
        try{

            $request = \Yii::$app->request;
            if ($request->getIsOptions()) {
                return $this->ResponseOptions($this->verbs()['view']);
            }
            $model = $this->findModel($id);
            if($model!=null){
                $result= ['success'=>true,'rules'=>$model];
            } else {
                $result= ['success'=>false,'message'=>'没有对应规则'];
            }

            return $result;
        }catch(Exception $e){
            throw new Exception($e);
        }
        //return $this->render('view', ['model' => $model]);
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
        try
        {
            $model = new BizRule(null);
            if(!array_key_exists('BizRule', Yii::$app->request->post())){
                return ['success'=>false,'message'=>'参数错误！'];
            }
            $mo =$model->find(Yii::$app->request->post()['BizRule']['name']);
            if($mo==null){
                if ($model->load(Yii::$app->request->post()) ) {
                    $classExists = $model->classExists();
                    if($classExists==$model::RIGHTCLASS){
                        $model->save();
                        $result=['success'=>true,'message'=>'规则已成功添加！'];
                        return $result;
                    }else{
                        if($classExists==$model::NOCLASS){
                            return ['success'=>false,'message'=>'规则类不存在！'];
                        }else{
                            return ['success'=>false,'message'=>'规则类不存在！'];
                        }
                    }


                } else {
                    return ['success'=>false,'message'=>'参数错误！'];
                }
            }else{
                return ['success'=>false,'message'=>'规则已存在！'];
            }

        }catch(Exception $e){
            throw new \yii\web\ServerErrorHttpException();
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
        if($model = $this->findModel($id)){


            var_dump($model->load(Yii::$app->request->post()));
            die();
            //($model->save());die();
            if ($model->load(Yii::$app->request->post())&& $model->save()) {
                //Helper::invalidate();

                return ['success'=>true];
            }else {
                throw new Exception($e);
            }
        }
        throw new \yii\web\ServerErrorHttpException();
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

        }catch (Exception $e){
            throw new Exception($e);
        }


        return ['success'=>true];
    }

    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param  string        $id
     * @return AuthItem      the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $item = Configs::authManager()->getRule($id);
        if ($item) {
            return new BizRule($item);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
