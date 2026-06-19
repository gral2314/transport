<?php

namespace app\models\system;

use Yii;
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\db\Command;
/**
 * This is the model class for table "appsettings".
 *
 * @property int $id
 * @property string|null $nameapp
 * @property string|null $titlepage
 * @property string|null $logosm
 * @property string|null $logolg
 * @property string|null $navbarcolor
 * @property string|null $sidebarcolor
 * @property string|null $imglogin
 * @property string|null $sidebarstartup
 * @property string|null $server
 */
class Appsettings extends \yii\db\ActiveRecord
{
    public static function normalizeSapServer(?string $server): string
    {
        $server = trim((string) $server);
        if ($server === '') {
            return '';
        }

        $server = preg_replace('#/b1s/v1/?$#i', '', $server);
        $server = rtrim($server, '/');

        if (!preg_match('#^https?://#i', $server)) {
            $server = 'https://' . $server;
        }

        return $server;
    }

    public static function buildSapServiceLayerBaseUrl(?string $server): string
    {
        $normalized = self::normalizeSapServer($server);
        return $normalized !== '' ? $normalized . '/b1s/v1/' : '';
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'appsettings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nameapp','titlepage','logosm', 'logolg', 'imglogin'], 'string', 'max' => 100],
            [['navbarcolor', 'sidebarcolor', 'sidebarstartup'], 'string', 'max' => 50],
            [['server'],'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nameapp'=> 'Nameapp',
            'titlepage'=> 'Titlepage',
            'logosm' => 'Logosm',
            'logolg' => 'Logolg',
            'navbarcolor' => 'Navbarcolor',
            'sidebarcolor' => 'Sidebarcolor',
            'imglogin' => 'Imglogin',
            'sidebarstartup' => 'Sidebarstartup',
            'server' => 'server',
        ];
    }
    public function getdataconnect()
    {
        try {
            
            $mod = new Query();
            $query1 = $mod
                ->select(['T0.*'])
                ->from('appsettings T0')
                ->where(['T0.id'=>1])
                ->createCommand()
			    ->queryAll();

            //$rows = $query1->union($query2)->all();
            return json_decode(json_encode($query1));
        } catch (\Throwable $th) {
            return json_encode(['Sucess'=>'Error','Msg'=>print_r($th,true)]);
        }
    }
    public function getConfigSap()
    {
         try {
            $query = Appsettings::find()->select(['server'])->where(['id' => 1]);
            $data = $query->asArray()->all();
            return ['Success' => 'Ok', 'Msg' => 'Servidor SAP obtenido', 'Data' => $data];

        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            $errorMsg = 'Error al listar servidor de SAP';
            if (YII_DEBUG) { $errorMsg .= ': ' . $e->getMessage(); }
            return ['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []];
        }
    }
    
    public function saveConfigSap($post)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $appconfig = self::findOne(1);
            if (!$appconfig) {return ['Success' => 'Error','Msg' => 'No se encontró la configuración.','Data' => []];}

            // ✔️ asignar valor del POST
            $appconfig->server = $post['server'] ?? null;

            if (!$appconfig->save()) {
                return ['Success' => 'Error','Msg' => 'No se pudo guardar la configuración.','Data' => $appconfig->errors];
            }
            $transaction->commit();
            return ['Success' => 'Ok','Msg' => 'Se guardó la configuración exitosamente.','Data' => []];

        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error','Msg' => YII_DEBUG ? $e->getMessage() : 'Error al guardar configuración SAP','Data' => []];
        }
    }

    public function updateConfigAPP($data=[])
    {
        try {
            $date = date('Y-m-d');
			$time = Yii::$app->formatter->asDateTime('now', 'php:H:i:s');
            $type='';
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();

            $appconfig = Appsettings::findOne(1);
            //$appconfig->nameapp = $data->
            //$appconfig->titlepage = $data->
            //$appconfig->logosm = $data->
            //$appconfig->logolg = $data->
            $appconfig->navbarcolor = $data['navbarcolor'];
            $appconfig->sidebarstartup = $data['sidebarmode'];
            //$appconfig->imglogin = $data->
            //$appconfig->sidebarstartup = $data->

            if($appconfig->save()){
                $transaction->commit();
                $result =['Success'=>'Ok','Msg'=>'Se guardo la configuracion exitosamente.','Data'=>[]];
            }else{
                $transaction->rollBack();
                $result =['Success'=>'Error','Msg'=>'Error: '. print_r($appconfig->getErrors()),'Data'=>[]];
            }
    
            return $result;
        } catch (\Throwable $th) {
            $error = 'Error: '. $th->getMessage().'. File: '.$th->getFile().'. No line: ' .$th->getLine() .';';
            return ['Success'=>'Error','Msg'=>$error,'Data'=>[]];
        }
    }
    public function updateConnectSAP($data=[])
    {
        try {
            $date = date('Y-m-d');
			$time = Yii::$app->formatter->asDateTime('now', 'php:H:i:s');
            $type='';
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            $appconfig = Appsettings::findOne(1);

            $appconfig->server = $data['server'];
            $appconfig->dbservertype = $data['dbservertype'];
            $appconfig->companydb = $data['companydb'];
            $appconfig->username = $data['username'];
            $appconfig->password = $data['password'];
            $appconfig->dbusername = $data['dbusername'];
            $appconfig->dbpassword = $data['dbpassword'];
            $appconfig->usetrusted = $data['usetrusted'];
            $appconfig->licenseserver = $data['licenseserver'];

            if($appconfig->save()){
                $transaction->commit();
                $result =['Success'=>'Ok','Msg'=>'Se guardo la configuracion exitosamente.','Data'=>[]];
            }else{
                $transaction->rollBack();
                $result =['Success'=>'Error','Msg'=>'Error: '. print_r($appconfig->getErrors()),'Data'=>[]];
            }
    
            return $result;
        } catch (\Throwable $th) {
            $error = 'Error: '. $th->getMessage().'. File: '.$th->getFile().'. No line: ' .$th->getLine() .';';
            return ['Success'=>'Error','Msg'=>$error,'Data'=>[]];
        }
    }

    public function updateConfigRhFaltas($data=[])
    {
        try {
            $date = date('Y-m-d');
			$time = Yii::$app->formatter->asDateTime('now', 'php:H:i:s');
            $type='';
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            $appconfig = Appsettings::findOne(1);

            $appconfig->asistcode = $data['asistcode'];
            $appconfig->faltacode = $data['faltacode'];
            $appconfig->extratimecode = $data['extratimecode'];
            $appconfig->rh_nomina_files_max = $data['rh_nomina_files_max'];
            $appconfig->rh_nomina_files_size_total_max = $data['rh_nomina_files_size_total_max'];
            
            if($appconfig->save()){
                $transaction->commit();
                $result =['Success'=>'Ok','Msg'=>'Se guardo la configuracion exitosamente.','Data'=>[]];
            }else{
                $transaction->rollBack();
                $result =['Success'=>'Error','Msg'=>'Error: '. print_r($appconfig->getErrors()),'Data'=>[]];
            }
    
            return $result;
        } catch (\Throwable $th) {
            $error = 'Error: '. $th->getMessage().'. File: '.$th->getFile().'. No line: ' .$th->getLine() .';';
            return ['Success'=>'Error','Msg'=>$error,'Data'=>[]];
        }
    }
    public function updateConfigSAP($data=[])
    {
        try {
            $date = date('Y-m-d');
			$time = Yii::$app->formatter->asDateTime('now', 'php:H:i:s');
            $type='';
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            $appconfig = Appsettings::findOne(1);

            $appconfig->usecentrocostos = $data['usecentrocostos'];
            $appconfig->ordrcc = $data['ordrcc'];
            $appconfig->ordrcm = $data['ordrcm'];
            $appconfig->odlncc = $data['odlncc'];
            $appconfig->odlncm = $data['odlncm'];
            
            if($appconfig->save()){
                $transaction->commit();
                $result =['Success'=>'Ok','Msg'=>'Se guardo la configuracion exitosamente.','Data'=>[]];
            }else{
                $transaction->rollBack();
                $result =['Success'=>'Error','Msg'=>'Error: '. print_r($appconfig->getErrors()),'Data'=>[]];
            }
    
            return $result;
        } catch (\Throwable $th) {
            $error = 'Error: '. $th->getMessage().'. File: '.$th->getFile().'. No line: ' .$th->getLine() .';';
            return ['Success'=>'Error','Msg'=>$error,'Data'=>[]];
        }
    }
}
