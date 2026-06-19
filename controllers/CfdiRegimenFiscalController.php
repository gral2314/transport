<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\objects\CfdiRegimenFiscalServices;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class CfdiRegimenFiscalController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['list' => ['GET'], 'get' => ['GET'], 'save' => ['POST'], 'delete' => ['POST'], 'get-form-options' => ['GET']]],
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
        ];
    }

    public function actionList(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;  try { return $this->asJson((new CfdiRegimenFiscalServices())->list(Yii::$app->request->queryParams)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionGet(string $pk): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; try { return $this->asJson((new CfdiRegimenFiscalServices())->get($pk)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionSave(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; $data = Yii::$app->request->post(); $isNew = true; if (!empty($data['code'])) $isNew = !(\app\models\tables\CfdiRegimenFiscal::find()->where(['code'=>$data['code']])->exists());  try { return $this->asJson((new CfdiRegimenFiscalServices())->save($data)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionDelete(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;  try { $pk = Yii::$app->request->post('code'); if (empty($pk)) return $this->asJson(['Success'=>'Error','Msg'=>'Código no proporcionado','Data'=>[]]); return $this->asJson((new CfdiRegimenFiscalServices())->delete($pk)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionGetFormOptions(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;  try { return $this->asJson((new CfdiRegimenFiscalServices())->getFormOptions()); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
}
