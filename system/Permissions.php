<?php

namespace app\models\system;

use Yii;
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\db\Command;
/**
 * This is the model class for table "permissions".
 *
 * @property int $id
 * @property int|null $iduser
 * @property int|null $idgroup
 * @property int $idmodulo
 * @property string $admit
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int $updateuser
 */
class Permissions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'permissions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['iduser', 'idgroup', 'idmodulo', 'createuser','updateuser'], 'integer'],
            [['idmodulo'], 'required'],
            [['createdate', 'createtime', 'updatedate', 'updatetime'], 'safe'],
            [['admit'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'iduser' => 'Iduser',
            'idgroup' => 'Idgroup',
            'idmodulo' => 'Idmodulo',
            'admit' => 'Admit',
            'createdate' => 'Createdate',
            'createtime' => 'Createtime',
            'createuser' => 'Createuser',
            'updatedate' => 'Updatedate',
            'updatetime' => 'Updatetime',
            'updateuser' => 'Updateuser',
        ];
    }
    public function permisionByUser($userid){
        try {
            
            $mod = new Query();
            $query1 = $mod
                ->select(['P.idmodulo as id','P.admit'])
                ->from('permissions P')
                ->where(['P.iduser' => $userid])
                ->orderby(['P.id' => SORT_DESC])
                ->createCommand()
			    ->queryAll();

            //$rows = $query1->union($query2)->all();
            return json_decode(json_encode($query1));
        } catch (\Throwable $th) {
            return json_encode(['Sucess'=>'Error','Msg'=>print_r($th,true)]);
        }
    }
    public function permisionByGroup($groupid){
        try {
            
            $mod = new Query();
            $query1 = $mod
                ->select(['P.idmodulo as id','P.admit'])
                ->from('permissions P')
                ->where(['P.idgroup' => $groupid])
                ->orderby(['P.id' => SORT_DESC])
                ->createCommand()
			    ->queryAll();

            //$rows = $query1->union($query2)->all();
            return json_decode(json_encode($query1));
        } catch (\Throwable $th) {
            return json_encode(['Sucess'=>'Error','Msg'=>print_r($th,true)]);
        }
    }
}
