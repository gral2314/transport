<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\objects\VehicleServices;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class FleetController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'units' => ['GET'],
                    'tires' => ['GET'],
                    'list' => ['GET'],
                    'get' => ['GET'],
                    'unit-save' => ['POST'],
                    'delete' => ['POST'],
                    'get-form-options' => ['GET'],
                    'get-axle-config' => ['GET'],
                    'save-document' => ['POST'],
                    'delete-document' => ['POST'],
                    'save-tire-line' => ['POST'],
                    'delete-tire-line' => ['POST'],
                    'vehicle-types' => ['GET'],
                    'brands' => ['GET'],
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

    public function actionUnits()
    {
        return $this->render('units/index');
    }

    public function actionTires()
    {
        return $this->render('tires/index');
    }

    public function actionList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $service = new VehicleServices();
        return $this->asJson($service->list(Yii::$app->request->queryParams));
    }

    public function actionGet(string $pk)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $service = new VehicleServices();
        return $this->asJson($service->get($pk));
    }

    public function actionUnitSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        $files = $_FILES; // Archivos subidos
        
        $vehicleCode = $data['vehicle']['vehicle_code'] ?? $data['vehicle_code'] ?? null;
        $isNew = empty($vehicleCode);
        try {
            // Procesar vehicle_tire desde JSON string si viene así
            if (isset($data['vehicle_tire']) && is_string($data['vehicle_tire'])) {
                $data['vehicle_tire'] = json_decode($data['vehicle_tire'], true) ?: [];
            }
            
            $service = new VehicleServices();
            return $this->asJson($service->save($data, $files));
        } catch (\Throwable $e) {
            \Yii::error([
                'message' => 'Error en FleetController::actionUnitSave',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
            return $this->asJson(['Success' => 'Error', 'Msg' => 'Error al procesar la solicitud. Verifique los datos e intente nuevamente.', 'Data' => []]);
        }
    }

    public function actionGetAxleConfig(string $vehicleTypeCode, ?string $vehicleCode = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $service = new \app\models\objects\VehicleTypeService();
        return $this->asJson($service->getAxleConfiguration($vehicleTypeCode, $vehicleCode));
    }

    public function actionDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try{
            $pk = Yii::$app->request->post('code');
            if (empty($pk)) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'Código no proporcionado', 'Data' => []]);
            }
            $service = new VehicleServices();
            return $this->asJson($service->delete($pk));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGetFormOptions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $service = new VehicleServices();
        return $this->asJson($service->getFormOptions());
    }

    public function actionVehicleTypes()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $service = new VehicleServices();
        $opts = $service->getFormOptions();
        $list = $opts['Data']['vehicle_type_list'] ?? [];
        $out = [];
        foreach ($list as $k => $v) {
            $out[] = ['code' => $k, 'name' => $v];
        }
        return $this->asJson($out);
    }

    public function actionBrands()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $service = new VehicleServices();
        $opts = $service->getFormOptions();
        $list = $opts['Data']['brand_list'] ?? [];
        $out = [];
        foreach ($list as $k => $v) {
            $out[] = ['code' => $k, 'name' => $v];
        }
        return $this->asJson($out);
    }

    public function actionSaveDocument()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        $isNew = empty($data['line_num']);
        try {
            $service = new VehicleServices();
            return $this->asJson($service->saveDocument($data));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionDeleteDocument(string $vehicleCode, int $lineNum)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $service = new VehicleServices();
            return $this->asJson($service->deleteDocument($vehicleCode, $lineNum));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionSaveTireLine()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        $isNew = empty($data['line_num']);
        try {
            $service = new VehicleServices();
            return $this->asJson($service->saveTireLine($data));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionDeleteTireLine(string $vehicleCode, int $lineNum)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $service = new VehicleServices();
            return $this->asJson($service->deleteTireLine($vehicleCode, $lineNum));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

}
