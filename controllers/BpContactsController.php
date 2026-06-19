<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\objects\BpContactsServices;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class BpContactsController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['list' => ['GET'], 'get' => ['GET'], 'save' => ['POST'], 'delete' => ['POST'], 'get-form-options' => ['GET']]],
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
        ];
    }

    public function actionList(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; try { return $this->asJson((new BpContactsServices())->list(Yii::$app->request->queryParams)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionGet(string $cardcode, string $contact_code): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; try { return $this->asJson((new BpContactsServices())->get($cardcode, $contact_code)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionSave(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; try { return $this->asJson((new BpContactsServices())->save(Yii::$app->request->post())); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionDelete(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; try { $card = Yii::$app->request->post('cardcode'); $contact = Yii::$app->request->post('contact_code'); if (empty($card) || empty($contact)) return $this->asJson(['Success'=>'Error','Msg'=>'Parámetros incompletos','Data'=>[]]); return $this->asJson((new BpContactsServices())->delete($card, $contact)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionGetFormOptions(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; try { return $this->asJson((new BpContactsServices())->getFormOptions()); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
}
