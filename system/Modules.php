<?php

namespace app\models\system;

use Yii;
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\db\Command;
/**
 * This is the model class for table "modules".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $idparent
 * @property string $page
 * @property string $icon
 * @property string $active
 * @property int $order
 * @property string $type S= Submenu, M=Menu
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int $updateuser
 */
class Modules extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'modules';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description', 'page', 'order' ], 'required'],
            [['idparent', 'orden', 'createuser', 'updateuser'], 'integer'],
            [['createdate', 'createtime', 'updatedate', 'updatetime'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['description', 'icon'], 'string', 'max' => 100],
            [['page'], 'string', 'max' => 200],
            [['active', 'type'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'idparent' => 'idparent',
            'page' => 'Page',
            'icon' => 'Icon',
            'active' => 'Active',
            'order' => 'Order',
            'type' => 'Type',
            'createdate' => 'Createdate',
            'createtime' => 'Createtime',
            'createuser' => 'Createuser',
            'updatedate' => 'Updatedate',
            'updatetime' => 'Updatetime',
            'updateuser' => 'Updateuser',
        ];
    }
    public function menusByUser(){
        try {
            
            $mod = new Query();
            $query1 = $mod
                ->select([
                    'M.id as M_id',
                    'M.name as M_name',
                    'M.icon as M_icon',
                    'M.page as M_page',
                    'M.order as M_order',
                    'M.type as M_type',
                    'S.id AS S_id',
                    'S.name AS S_name',
                    'S.icon AS S_icon',
                    'S.page AS S_page',
                    'P2.admit as S_Admin'
                ])
                ->from('modules M')
                ->leftJoin('modules S', 'S.Idparent = M.id and S.type = \'S\'')
                ->leftJoin('permissions P1', 'P1.idmodulo = M.id and P1.admit = \'Y\'')
                ->leftJoin('permissions P2', 'P2.idmodulo = S.id and P2.iduser = P1.iduser')
                //->leftJoin('permissions P2', 'P2.idmodulo = S.id and P2.iduser = P1.iduser and P2.admit =\'Y\'')
                ->where(['M.idparent'=>0,'P1.iduser' => Yii::$app->user->identity->id])
                ->andWhere(['M.active' => 'Y'])
                ->andWhere(['S.active' => 'Y'])
                ->orderby(['M.type' => SORT_DESC, 'M.order' => SORT_ASC, 'S.order' =>SORT_ASC])
                ->createCommand()
			    ->queryAll();

            //$rows = $query1->union($query2)->all();
            return json_decode(json_encode($query1));
        } catch (\Throwable $th) {
            return json_encode(['Sucess'=>'Error','Msg'=>print_r($th,true)]);
        }
    }
    public function menusPermision(){
        try {
            
            $mod = new Query();
            $query1 = $mod
                ->select([
                    'M.id as M_id',
                    'M.name as M_name',
                    'M.icon as M_icon',
                    'M.page as M_page',
                    'M.order as M_order',
                    'M.type as M_type',
                    'S.id AS S_id',
                    'S.name AS S_name',
                    'S.icon AS S_icon',
                    'S.page AS S_page'
                ])
                ->from('modules M')
                ->leftJoin('modules S', 'S.Idparent = M.id and S.type = \'S\'')
                ->where(['M.idparent'=>0])
                ->orderby(['M.type' => SORT_DESC, 'M.order' => SORT_ASC,'S.id' =>SORT_ASC])
                ->createCommand()
			    ->queryAll();

            //$rows = $query1->union($query2)->all();
            return json_decode(json_encode($query1));
        } catch (\Throwable $th) {
            return json_encode(['Sucess'=>'Error','Msg'=>print_r($th,true)]);
        }
    }
   
    public function menus()
	{
		if(isset(Yii::$app->user->identity->id)){
		$mod = new Query();
		$Modules_raiz = $mod
		->select(['m.*'])
		->distinct()
	   ->from('modules as m')
	   ->InnerJoin('permissions as p', 'm.id = p.idmodulo')
	   ->where(['m.idparent' => '0','m.active' => 'Y', 'p.iduser' =>Yii::$app->user->identity->id])
	   ->orderBy(['m.Orden' => 'DESC'])
	   ->createCommand()
	   ->queryAll();
		return $Modules_raiz;
		}else{
			$Modules_raiz=[];
			return $Modules_raiz;
		}
	}

	   public function hijos($id='')
	{
		if(isset(Yii::$app->user->identity->id)){
		$mod = new Query();
		$menuhijos =$mod
		->select(['m.*'])
		->distinct()
		->from('modules as m')
		->InnerJoin('permissions as p', 'm.id = p.idmodulo')
		->where(['m.idparent' => $id,'m.active' => 'Y','p.iduser' =>Yii::$app->user->identity->id])
		//, 'p.IdUser' =>Yii::$app->user->identity->id
		->orderBy(['m.Orden' => 'DESC'])
		->createCommand()
		->queryAll();
	   return $menuhijos;
		}else{
			$menuhijos=[];
			return $menuhijos;
		}
	}

	public function menusall()
	{
		
		$mod = new Query();
		$Modules_raiz = $mod
		
	   ->select(['m.*'])
	   ->from('modules as m')
	   //->InnerJoin('permisos as p', 'm.id = p.IdModulo')
	   ->where(['m.idparent' => '0'])
	   ->orderBy(['m.order' => 'DESC'])
	   ->createCommand()
	   ->queryAll();
		return $Modules_raiz;		
	}
    public function menuid($MenuId ='')
	{
		$mod = new Query();
		$Module = $mod
	   ->select(['m.*'])
	   ->from('modules as m')
	   //->InnerJoin('permisos as p', 'm.id = p.IdModulo')
	   ->where(['m.id' => $MenuId])
	   //->orderBy(['m.Orden' => 'DESC'])
	   ->createCommand()
	   ->queryAll();
		return $Module;		
	}


	public function submenuall()
	{
		
		$mod = new Query();
		$menuhijos =$mod
		->select(['m.*'])
		->from('modules as m')
		//->InnerJoin('permisos as p', 'm.id = p.IdModulo')
		//->where(['m.IdParent' => $id, 'm.type' => 'S'])
		->where(['m.type' => 'S'])
		//, 'p.IdUser' =>Yii::$app->user->identity->id
		->orderBy(['m.order' => 'DESC'])
		->createCommand()
		->queryAll();
	   return $menuhijos;
		
	}
}
