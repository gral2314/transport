<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\objects\EmployeeRoleServices;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class EmployeeRoleController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index'            => ['GET'],
                    'list'             => ['GET'],
                    'save-batch'       => ['POST'],
                    'get-form-options' => ['GET'],
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

    public function actionList(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $Services = new EmployeeRoleServices();
            return $this->asJson($Services->list(Yii::$app->request->queryParams));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionSaveBatch(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        try {
            $employee = $data['employee_code'] ?? null;
            $roles = $data['roles'] ?? [];
            if (empty($employee)) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'Empleado no proporcionado', 'Data' => []]);
            }
            $Services = new EmployeeRoleServices();
            return $this->asJson($Services->saveBatch($employee, $roles));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGetFormOptions(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $Services = new EmployeeRoleServices();
            return $this->asJson($Services->getFormOptions());
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }
}
