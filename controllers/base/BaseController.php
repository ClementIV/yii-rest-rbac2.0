<?php
/**
 * base Controller for rbac
 * @author clement <fyqnankai@gmial.com>
 */

namespace clement\rest\controllers\base;
use Yii;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use clement\rest\auth\APIAuth;
use clement\rest\components\AccessControl;
use yii\web\Response;
class BaseController extends ActiveController
{
    /**
     * 设置返回头部的allow部分
     * @param array $collection allow的方法集合
     */
    public $modelClass = 'clement\rest\models\RestModel';
    public $enableCsrfValidation = false;
    public function ResponseOptions($collection = [])
    {
        $collectionOptions = ['GET', 'POST', 'HEAD', 'OPTIONS'];
        if (!empty($collection)) {
            $collectionOptions = $collection;
        }
        Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $collectionOptions));

    }


    public function behaviors()
    {
        $behaviors = parent::behaviors();

        //启用JSON返回
        //$behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Allow-Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
            ]
        ];
        $behaviors['authenticator'] = [
            'class' => APIAuth::className(),
            'except' => ['OPTIONS'],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
        ];
        return $behaviors;
    }


}
