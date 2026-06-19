<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\objects\VehicleServices;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class VehicleController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index'             => ['GET'],
                    'list'              => ['GET'],
                    'get'               => ['GET'],
                    'save'              => ['POST'],
                    'delete'            => ['POST'],
                    'get-form-options'  => ['GET'],
                    'save-document'     => ['POST'],
                    'delete-document'   => ['POST'],
                    'save-tire-line'    => ['POST'],
                    'delete-tire-line'  => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    public function actionIndex(): string|\yii\web\Response
    {
        $service = new VehicleServices();
        $result  = $service->list(Yii::$app->request->queryParams);
        return $this->render('index', ['result' => $result]);
    }

    public function actionList(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $service = new VehicleServices();
            return $this->asJson($service->list(Yii::$app->request->queryParams));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGet(string $pk): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        yii::info("Obteniendo información de vehículo con PK: $pk", __METHOD__);
        try {
            $service = new VehicleServices();
            return $this->asJson($service->get($pk));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionSave(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data  = Yii::$app->request->post();
        $vehicleCode = $data['vehicle']['vehicle_code'] ?? $data['vehicle_code'] ?? null;
        $isNew = empty($vehicleCode);
        try {
            $service = new VehicleServices();
            return $this->asJson($service->save($data));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionDelete(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $pk = Yii::$app->request->post('vehicle_code');
            if (empty($pk)) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'Código no proporcionado', 'Data' => []]);
            }
            $service = new VehicleServices();
            return $this->asJson($service->delete($pk));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGetFormOptions(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $service = new VehicleServices();
            return $this->asJson($service->getFormOptions());
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    // -------------------------------------------------------------------------
    // Vehicle Documents
    // -------------------------------------------------------------------------

    public function actionSaveDocument(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data  = Yii::$app->request->post();
        $isNew = empty($data['line_num']);
        try {
            $service = new VehicleServices();
            return $this->asJson($service->saveDocument($data));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionDeleteDocument(string $vehicleCode, int $lineNum): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $service = new VehicleServices();
            return $this->asJson($service->deleteDocument($vehicleCode, $lineNum));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    // -------------------------------------------------------------------------
    // Vehicle Tires
    // -------------------------------------------------------------------------

    public function actionSaveTireLine(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data  = Yii::$app->request->post();
        $isNew = empty($data['line_num']);
        try {
            $service = new VehicleServices();
            return $this->asJson($service->saveTireLine($data));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionDeleteTireLine(string $vehicleCode, int $lineNum): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $service = new VehicleServices();
            return $this->asJson($service->deleteTireLine($vehicleCode, $lineNum));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }
}
