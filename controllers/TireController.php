<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\objects\TireServices;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class TireController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index'            => ['GET'],
                    'list'             => ['GET'],
                    'get'              => ['GET'],
                    'save'             => ['POST'],
                    'delete'           => ['POST'],
                    'get-form-options' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['?','@']],
                ],
            ],
        ];
    }

    public function actionIndex(): string|\yii\web\Response
    {
        // Renderizar vista de fleet/tires en lugar de vista simple
        return $this->render('/fleet/tires/index');
    }

    public function actionList(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $Services = new TireServices();
            return $this->asJson($Services->list(Yii::$app->request->queryParams));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGet(string $pk): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $Services = new TireServices();
            return $this->asJson($Services->get($pk));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionSave(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data  = Yii::$app->request->post('tire', []);
        $isNew = empty($data['tire_code']);
        try {
            $Services = new TireServices();
            return $this->asJson($Services->save($data));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionDelete(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $pk = Yii::$app->request->post('tire_code');
            if (empty($pk)) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'Código no proporcionado', 'Data' => []]);
            }
            $Services = new TireServices();
            return $this->asJson($Services->delete($pk));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGetFormOptions(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $Services = new TireServices();
            return $this->asJson($Services->getFormOptions());
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }
}
