<?php


namespace clement\rest\models;

use yii\base\Object;

class Item extends \yii\rbac\Item
{
    /**
     * methods for routes verb
     * @var varchar
     */
    const TYPE_ROLE = 1;
    const TYPE_PERMISSION = 2;
    public $methods;
}
