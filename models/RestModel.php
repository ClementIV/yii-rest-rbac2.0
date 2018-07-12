<?php
namespace clement\rest\models;
use yii\db\ActiveRecord;


class RestModel extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%auth_item}}';
    }
}
 ?>
