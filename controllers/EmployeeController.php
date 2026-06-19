<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;
use app\models\objects\EmployeeServices;

class EmployeeController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index'            => ['GET'],
                    'list'             => ['GET', 'POST'],
                    'get'              => ['GET', 'POST'],
                    'save'             => ['POST'],
                    'delete'           => ['POST'],
                    'get-form-options' => ['GET', 'POST'],
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

    public function actionIndex()
    {
        
        return $this->render('@app/views/rrhh/employees/index');
    }

    public function actionList(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            $Services = new EmployeeServices();
            $params = array_merge(Yii::$app->request->queryParams, Yii::$app->request->post());
            return $this->asJson($Services->list($params));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGet(string $pk): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            $Services = new EmployeeServices();
            return $this->asJson($Services->get($pk));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionSave(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        
        $employeeCode = $data['employee']['employee_code'] ?? $data['employee_code'] ?? null;
        $isNew = true;
        if (!empty($employeeCode)) {
            $isNew = !(\app\models\tables\Employee::find()->where(['employee_code' => $employeeCode])->exists());
        }
        
        try {
            // Extraer datos del empleado del formato de formulario
            $employeeData = $data['employee'] ?? $data;
            if (isset($data['documents'])) {
                $employeeData['documents'] = $data['documents'];
            }
            if (isset($data['roles'])) {
                $employeeData['roles'] = $data['roles'];
            }
            
            $Services = new EmployeeServices();
            $res = $Services->save($employeeData);
            
            // Adjust return message formatting for sweetalert/toastr
            if ($res['Success'] === 'OK') {
                $res['Success'] = 'Ok'; // Match front-end expected casing 'Ok'
            }
            return $this->asJson($res);
        } catch (\Throwable $e) {
            \Yii::error([
                'message' => 'Error en EmployeeController::actionSave',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ], __METHOD__);
            
            $errorMsg = 'Error al procesar la solicitud';
            if (YII_DEBUG) {
                $errorMsg .= ': ' . $e->getMessage();
            }
            
            return $this->asJson(['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []]);
        }
    }

    public function actionDelete(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            $pk = Yii::$app->request->post('code');
            if (empty($pk)) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'Código no proporcionado', 'Data' => []]);
            }
            
            $Services = new EmployeeServices();
            $res = $Services->delete($pk);
            if ($res['Success'] === 'OK') {
                $res['Success'] = 'Ok'; // Match front-end expected casing 'Ok'
            }
            return $this->asJson($res);
        } catch (\Throwable $e) {
            $errorMsg = 'Error al eliminar el empleado';
            if (YII_DEBUG) {
                $errorMsg .= ': ' . $e->getMessage();
            }
            return $this->asJson(['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []]);
        }
    }

    public function actionGetFormOptions(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            $Services = new EmployeeServices();
            return $this->asJson($Services->getFormOptions());
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }
}
