<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\objects\SeriesServices;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Controlador para la gestión de series de numeración de documentos.
 *
 * Proporciona endpoints CRUD para administrar las series y un endpoint
 * para generar el siguiente número de documento de forma atómica.
 */
class SeriesController extends Controller
{
    /** @var SeriesServices */
    private SeriesServices $seriesServices;

    /**
     * {@inheritdoc}
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->seriesServices = new SeriesServices();
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'list' => ['GET'],
                    'get' => ['GET'],
                    'save' => ['POST'],
                    'delete' => ['POST'],
                    'get-form-options' => ['GET'],
                    'get-next-number' => ['GET'],
                    'peek-next-number' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Listar todas las series.
     *
     * @return array
     */
    public function actionList(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->seriesServices->list();
    }

    /**
     * Obtener una serie por ID.
     *
     * @param int $id
     * @return array
     */
    public function actionGet(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->seriesServices->get($id);
    }

    /**
     * Guardar (crear o actualizar) una serie.
     *
     * @return array
     */
    public function actionSave(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        return $this->seriesServices->save($data);
    }

    /**
     * Eliminar una serie por ID.
     *
     * @param int $id
     * @return array
     */
    public function actionDelete(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->seriesServices->delete($id);
    }

    /**
     * Obtener opciones para formularios.
     *
     * @return array
     */
    public function actionGetFormOptions(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->seriesServices->getFormOptions();
    }

    /**
     * Obtener el siguiente número de documento.
     *
     * @param string $objectName
     * @param int $seriesId
     * @return array
     */
    public function actionGetNextNumber(string $objectName, int $seriesId): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->seriesServices->getNextNumber($objectName, $seriesId);
    }

    /**
     * Obtener el siguiente número de documento sin incrementar (solo lectura).
     *
     * @param string $objectName
     * @param int $seriesId
     * @return array
     */
    public function actionPeekNextNumber(string $objectName, int $seriesId): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->seriesServices->peekNextNumber($objectName, $seriesId);
    }
}
