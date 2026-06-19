<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\objects\ItemsService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class ItemsController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['index' => ['GET'], 'list' => ['GET'], 'get' => ['GET'], 'save' => ['POST'], 'delete' => ['POST'], 'get-form-options' => ['GET']]],
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
        ];
    }

    public function actionIndex(): string
    {
        if (!Yii::$app->user->can('items.view')) {
            throw new \yii\web\ForbiddenHttpException('Acceso denegado.');
        }
        return $this->render('index');
    }

    public function actionList(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; try { return $this->asJson((new ItemsService())->list(Yii::$app->request->queryParams)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionGet(string $pk): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; try { return $this->asJson((new ItemsService())->get($pk)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionSave(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; $data = Yii::$app->request->post(); $isNew = true; if (!empty($data['itemcode'])) $isNew = !(\app\models\tables\Items::find()->where(['itemcode'=>$data['itemcode']])->exists()); try { return $this->asJson((new ItemsService())->save($data)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionDelete(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; try { $pk = Yii::$app->request->post('itemcode'); if (empty($pk)) return $this->asJson(['Success'=>'Error','Msg'=>'Código no proporcionado','Data'=>[]]); return $this->asJson((new ItemsService())->delete($pk)); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
    public function actionGetFormOptions(): \yii\web\Response { Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; try { return $this->asJson((new ItemsService())->getFormOptions()); } catch (\Throwable $e) { return $this->asJson(['Success'=>'Error','Msg'=>$e->getMessage(),'Data'=>[]]); } }
}
