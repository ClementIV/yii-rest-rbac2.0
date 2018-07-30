<?php

namespace clement\rest\models\searchs;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AssignmentSearch represents the model behind the search form about Assignment.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Assignment extends Model
{
    public $id;
    public $username;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'username'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('rbac-admin', 'ID'),
            'username' => Yii::t('rbac-admin', 'Username'),
            'name' => Yii::t('rbac-admin', 'Name'),
        ];
    }

    /**
     * Create data provider for Assignment model.
     * @param  array                        $params
     * @param  \yii\db\ActiveRecord         $class
     * @param  string                       $usernameField
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params, $class, $usernameField)
    {
        $query = $class::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', $usernameField, $this->username]);

        return $dataProvider;
    }
    public function searchPage($params,$class)
    {
        $query = $class::find();

        //var_dump($params);die();
        $query->orderBy('id')
                ->offset($params["page"]*$params["pageLimit"])
                ->limit($params["pageLimit"]);



        if(array_key_exists("status",$params)){
            $query->where(['status'=>$params["status"]]);
        }
        if(array_key_exists("q",$params)){
            $query->andFilterWhere(['like','username', $params["q"] ]);
        }
        $count = $query->count();
        $res =$query->all(); 

        return ['count'=>$count,'items'=>$res];
    }
}
