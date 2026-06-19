<?php

namespace app\models\system;

use Yii;
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\db\Command;

/**
 * This is the model class for table "Users".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int $updateuser
 */
class Usergroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'usersgroup';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description'], 'required'],
            [['name'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 100],
            [['createuser', 'updateuser'], 'integer'],
            [['createdate', 'createtime', 'updatedate', 'updatetime'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'name',
            'description' => 'description',
            'datecreated' => 'datecreated',
            'timecreated' => 'timecreated',
            'dateupdated' => 'dateupdated',
            'timeupdated' => 'timeupdated',
            'creatoruser' => 'creatoruser',
            'updateuser' => 'updateuser',
        ];
    }

    //busca todos los registros
    public function selall()
    {
        $mod = new Query();
         	$comment = $mod
			->select(['b.id','b.name','b.description'])
			->from('usersgroup as b')
			->createCommand()
			->queryAll();
            return $comment;
    }
	public function SaveGroup($data){
        try {
            $date = date('Y-m-d');
			$time = Yii::$app->formatter->asDateTime('now', 'php:H:i:s');
			$type='';
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            $isSave=false;
            $model = Usergroup::findOne($data->groupid);
            if(!$model){
                $model = new Usergroup();
                $type='creado';
                $model->createdate = $date;
                $model->createtime = $time;
                $model->createuser = Yii::$app->user->identity->id;
            }else{
                $type='actualizado';
                $model->updatedate = $date;
                $model->updatetime = $time;
                $model->updateuser = Yii::$app->user->identity->id;
            }
            $model->name = $data->name;
            $model->description = $data->description;
            if($model->save()){
                $permitions = json_decode($data->permitions);
                foreach ($permitions as $key => $permiso) {
                    $parts = explode("-", $permiso->module);
                    $modulo = $parts[1];
                    $modelpermiso = Permissions::findOne(['idgroup'=> $model->id, 'idmodulo'=>$modulo]);
                    if(!$modelpermiso){
                        $modelpermiso = new Permissions();
                        $modelpermiso->createdate = $date;
                        $modelpermiso->createtime = $time;
                        $modelpermiso->createuser = Yii::$app->user->identity->id;
                    }else{
                        $modelpermiso->updatedate = $date;
                        $modelpermiso->updatetime = $time;
                        $modelpermiso->updateuser = Yii::$app->user->identity->id;
                    }
                    
                    $modelpermiso->iduser =  null;
                    $modelpermiso->idgroup = $model->id;
                    $modelpermiso->idmodulo = $modulo;
                    $modelpermiso->admit = $permiso->state;
                    if($modelpermiso->save()){
                        $isSave=true;
                    }else{
                        $isSave=false;
                        $transaction->rollBack();
                        $data =Users::selall();
                        $result =['Success'=>'Error','Msg'=>'Error: '. print_r($modelpermiso->getErrors()),'Data'=>$data['Data']];
                    }
                }
            }else{
                $transaction->rollBack();
                $data =Usergroup::selall();
                $result =['Success'=>'Error','Msg'=>'Error: '. print_r($model->getErrors()),'Data'=>$data];
            }
            if($isSave == true){
                $transaction->commit();
                $data =Usergroup::selall();
                $result =['Success'=>'Ok','Msg'=>'El grupo '.$type.' exitosamente','Data'=>$data];
            }

            return $result;
        } catch (\Throwable $th) {
            return ['Sucess'=>'Error','Msg'=> var_dump($th), 'Data'=>[]];
        }
    }

}
	
?>