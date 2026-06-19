<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\objects\AxleTypeServices;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class AxleTypeController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index'  => ['GET'],
                    'list'   => ['GET'],
                    'get'    => ['GET'],
                    'save'   => ['POST'],
                    'delete' => ['POST'],
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
        
        $Services = new AxleTypeServices();
        $result  = $Services->list(Yii::$app->request->queryParams);
        return $this->render('index', ['result' => $result]);
    }

    public function actionList(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            
            $Services = new AxleTypeServices();
            return $this->asJson($Services->list(Yii::$app->request->queryParams));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGet(string $pk): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
           
            $Services = new AxleTypeServices();
            return $this->asJson($Services->get($pk));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionSave(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $isUpdate = !empty(Yii::$app->request->post('code'));
            
            $Services = new AxleTypeServices();
            return $this->asJson($Services->save(Yii::$app->request->post()));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionDelete(string $pk): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
           
            $Services = new AxleTypeServices();
            return $this->asJson($Services->delete($pk));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }
}
