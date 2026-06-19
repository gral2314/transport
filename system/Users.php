<?php

namespace app\models\system;

use app\models\system\Permissions;

use Yii;
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\db\Command;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $usercode
 * @property string $username
 * @property string|null $last_name
 * @property int $idgroup
 * @property int|null $codeemploye
 * @property string $imagen
 * @property string|null $email
 * @property string|null $phone
 * @property string $password
 * @property string $authkey
 * @property string $accesstoken
 * @property string|null $fcm_token
 * @property string|null $fcm_expire
 * @property string|null $token_reset_pass
 * @property string|null $token_rest_time
 * @property string $active
 * @property string $locked
 * @property string $online
 * @property string|null $last_ip
 * @property string|null $lastconection
 * @property int $failed_login_attempts
 * @property string|null $blocked_until
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null $updateuser
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['usercode', 'username', 'idgroup', 'password', 'authkey', 'accesstoken'], 'required'],
            [['idgroup', 'codeemploye', 'failed_login_attempts', 'createuser', 'updateuser'], 'integer'],
            [['fcm_expire', 'token_rest_time', 'lastconection', 'blocked_until', 'createdate', 'createtime', 'updatedate', 'updatetime'], 'safe'],
            [['usercode'], 'string', 'max' => 50],
            [['username', 'last_name', 'password'], 'string', 'max' => 100],
            [['imagen', 'authkey', 'accesstoken'], 'string', 'max' => 250],
            [['email'], 'string', 'max' => 200],
            [['last_ip'], 'string', 'max' => 45],
            [['phone'], 'string', 'max' => 20],
            [['fcm_token'], 'string', 'max' => 500],
            [['token_reset_pass'], 'string', 'max' => 254],
            [['active', 'locked', 'online'], 'string', 'max' => 1],
        ];
    }

    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'usercode' => 'Usercode',
            'username' => 'Username',
            'last_name' => 'Last Name',
            'idgroup' => 'Idgroup',
            'codeemploye' => 'Codeemploye',
            'imagen' => 'Imagen',
            'email' => 'Email',
            'phone' => 'Phone',
            'password' => 'Password',
            'authkey' => 'Authkey',
            'accesstoken' => 'Accesstoken',
            'fcm_token' => 'Fcm Token',
            'fcm_expire' => 'Fcm Expire',
            'token_reset_pass' => 'Token Reset Pass',
            'token_rest_time' => 'Token Rest Time',
            'active' => 'Active',
            'locked' => 'Locked',
            'online' => 'Online',
            'last_ip' => 'Last Ip',
            'lastconection' => 'Lastconection',
            'failed_login_attempts' => 'Failed Login Attempts',
            'blocked_until' => 'Blocked Until',
            'createdate' => 'Createdate',
            'createtime' => 'Createtime',
            'createuser' => 'Createuser',
            'updatedate' => 'Updatedate',
            'updatetime' => 'Updatetime',
            'updateuser' => 'Updateuser',
        ];
    }
    public  function GetUserSelect(){
        try {
            $query = new \yii\db\Query();
            $query = new Query();
            $query->select(['T0.id', 'T0.usercode', 'T0.username', 'T0.imagen', 'T1.name as groupname'])
                ->from('users T0')
                ->innerJoin('usersgroup T1', 'T0.idgroup = T1.id')
                ->where(['T0.id'=>Yii::$app->user->identity->id]);
            $rows = $query->all();
            return json_decode(json_encode($rows[0]));
        } catch (\Throwable $th) {
            return json_encode(['Sucess'=>'Error']);
        }
    }

     public function selall()
    {
        try {
            $mod = new Query();
         	$comment = $mod
			->select([
                'a.id',
                'a.usercode',
                'a.username',
                'a.last_name',
                'a.active',
                'a.locked',
                'a.online',
                'a.last_ip',
                'a.lastconection',
                'a.failed_login_attempts',
                'a.blocked_until',
                'a.email',
                'a.phone',
                'a.imagen',
                'a.idgroup',
                'b.name as groupname',
                'a.codeemploye',
            ])
            ->from('users as a')
            ->leftJoin('usersgroup as b', 'a.idgroup = b.id')
            ->where(['<>', 'a.id', 1])
			->createCommand()
			->queryAll();
            return ['Sucess'=>'Sucess','Msg'=>'Data encontrada', 'Data'=> $comment];
        } catch (\Throwable $th) {
            return json_encode(['Sucess'=>'Error','Msg'=>$th]);
        }
       
    }

    public function SaveUser($data){
        try {
            $date = date('Y-m-d');
			$time = Yii::$app->formatter->asDateTime('now', 'php:H:i:s');
            $autKey= Users::randKey("abcdef0123456789", 200);
            $accToken = Users::randKey("abcdef0123456789", 200);
            $type='';
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            $user = User::findOne($data->userid);
            $isSave=false;
            if(!$user){
                $user = new Users();
                $user->authkey = $autKey;
                $user->accesstoken = $accToken;
                $user->locked = 'N';
                $user->online = 'N';
                $user->failed_login_attempts = 0;
                $user->blocked_until = null;
                $user->last_ip = null;
                $type='creado';
                $user->createdate = $date;
                $user->createtime = $time;
                $user->createuser = Yii::$app->user->identity->id;
            }else{
                $type='actualizado';
                $user->updatedate = $date;
                $user->updatetime = $time;
                $user->updateuser = Yii::$app->user->identity->id;
            }
            $user->usercode = $data->UserCode;
            $user->username = $data->UserName;
            $user->last_name = $data->LastName ?? $data->last_name ?? null;
            $user->idgroup = $data->UserGroup;
            $user->email = $data->UserEmail;
            $user->phone = $data->UserPhone;
            $user->imagen = $data->avatar;
            $user->codeemploye = $data->codeemploye;
            $user->active = $data->UserActivo;

            if (isset($data->UserLocked) && in_array($data->UserLocked, ['Y', 'N'], true)) {
                $user->locked = $data->UserLocked;
            }
            
            
            if($data->UserPass != '***********'){
                $user->password = md5($data->UserPass);
            }
            if($user->save()){
                $permitions = json_decode($data->permitions);
                foreach ($permitions as $key => $permiso) {
                    $parts = explode("-", $permiso->module);
                    $modulo = $parts[1];
                    $modelpermiso = Permissions::findOne(['iduser'=> $user->id, 'idmodulo'=>$modulo]);
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
                    
                    $modelpermiso->iduser =  $user->id;
                    $modelpermiso->idgroup = null;
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
                $isSave=false;
                $transaction->rollBack();
                $data =Users::selall();
                $result =['Success'=>'Error','Msg'=>'Error: '. print_r($user->getErrors()),'Data'=>$data['Data']];
            }
            if($isSave == true){
                $transaction->commit();
                $data =Users::selall();
                $result =['Success'=>'Ok','Msg'=>'Usuario '.$type.' exitosamente','Data'=>$data['Data']];
            }
            return $result;
        } catch (\Throwable $th) {
            return ['Sucess'=>'Error','Msg'=> var_dump($th), 'Data'=>[]];
        }
    }

    private function randKey($str='', $long=0)
    {
        $key = null;
        $str = str_split($str);
        $start = 0;
        $limit = count($str)-1;
        for($x=0; $x<$long; $x++)
        {
            $key .= $str[rand($start, $limit)];
        }
        return $key;
    }
}
