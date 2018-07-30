<?php

namespace clement\rest\models\searchs;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use clement\rest\models\User as UserModel;

/**
 * User represents the model behind the search form about `clement\rest\models\User`.
 */
class User extends UserModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'auth_key', 'password_hash', 'password_reset_token', 'email'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UserModel::find();
        if(array_key_exists("page", $params)&&array_key_exists("pageLimit", $params)){
            $query = $query->orderBy('id')
                    ->offset($params["page"]*$params["pageLimit"])
                    ->limit($params["pageLimit"]);
        }
        if(array_key_exists("status",$params)){
            $query->where(['status'=>$params["status"]]);
        }
        if(array_key_exists("q",$params)){
            $query->andFilterWhere(['like','username', $params["q"] ]);
        }
        $count = $query->count();
        $query=$query->all();


        return ['count'=>$count,'items'=>$query];
    }
}
