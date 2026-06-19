<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\objects\ItemWarehouseServices;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class ItemWarehouseController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['list' => ['GET'], 'get' => ['GET'], 'save' => ['POST'], 'delete' => ['POST'], 'get-form-options' => ['GET']]],
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
        ];
    }

    public function actionList(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;  try { return $this->asJson((new ItemWarehouseServices())->list(Yii::$app->request->queryParams)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionGet(string $itemcode, string $warehouse_code): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; try { return $this->asJson((new ItemWarehouseServices())->get($itemcode, $warehouse_code)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionSave(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; try { return $this->asJson((new ItemWarehouseServices())->save(Yii::$app->request->post())); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionDelete(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;  try { $item = Yii::$app->request->post('itemcode'); $wh = Yii::$app->request->post('warehouse_code'); if (empty($item) || empty($wh)) return $this->asJson(['Success'=>'Error','Msg'=>'Parámetros incompletos','Data'=>[]]); return $this->asJson((new ItemWarehouseServices())->delete($item, $wh)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionGetFormOptions(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;  try { return $this->asJson((new ItemWarehouseServices())->getFormOptions()); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
}
