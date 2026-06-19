<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\objects\StatesServices;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class StatesController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'list' => ['GET'], 'get' => ['GET'], 'save' => ['POST'],
                    'delete' => ['POST'], 'get-form-options' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }

    public function actionList(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try { return $this->asJson((new StatesServices())->list(Yii::$app->request->queryParams)); }
        catch (\Throwable $e) { return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]); }
    }

    public function actionGet(string $code, string $country): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try { return $this->asJson((new StatesServices())->get($code, $country)); }
        catch (\Throwable $e) { return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]); }
    }

    public function actionSave(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        $isNew = true;
        if (!empty($data['code']) && !empty($data['country'])) {
            $isNew = !(\app\models\tables\States::find()->where(['code' => $data['code'], 'country' => $data['country']])->exists());
        }
        try { return $this->asJson((new StatesServices())->save($data)); }
        catch (\Throwable $e) { return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]); }
    }

    public function actionDelete(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $code = Yii::$app->request->post('code');
            $country = Yii::$app->request->post('country');
            if (empty($code) || empty($country)) return $this->asJson(['Success' => 'Error', 'Msg' => 'Parámetros incompletos', 'Data' => []]);
            return $this->asJson((new StatesServices())->delete($code, $country));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGetFormOptions(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try { return $this->asJson((new StatesServices())->getFormOptions()); }
        catch (\Throwable $e) { return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]); }
    }
}
