<?php
declare(strict_types=1);

namespace app\controllers;

use app\models\objects\DocTireMovementServices;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class TallerController extends Controller
{
    private ?DocTireMovementServices $service = null;

    protected function getService(): DocTireMovementServices
    {
        if ($this->service === null) {
            $this->service = new DocTireMovementServices();
        }
        return $this->service;
    }

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index'           => ['GET'],
                    'view'            => ['GET'],
                    'list'            => ['GET'],
                    'get'             => ['GET'],
                    'release'         => ['POST'],
                    'start'           => ['POST'],
                    'finish'          => ['POST'],
                    'validate'        => ['POST'],
                    'cancel'          => ['POST'],
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

    public function beforeAction($action)
    {
        // Deshabilitar CSRF para endpoints POST del flujo de taller
        $csrfExceptions = ['release', 'start', 'finish', 'validate', 'cancel'];
        if (in_array($action->id, $csrfExceptions, true)) {
            $this->enableCsrfValidation = false;
        }

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login'])->send();
        }
        return parent::beforeAction($action);
    }

    // ── Vistas ────────────────────────────────────────────────────────────

    /**
     * Dashboard técnico: muestra órdenes asignadas al técnico logueado.
     * Diseño mobile-first con cards en lugar de DataTable.
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    /**
     * Workbench: detalle de una orden para trabajar (evidencia, completar).
     * GET: /taller/view?id={ID}
     */
    public function actionView(int $id): string
    {
        $result = $this->getService()->get($id);
        $document = ($result['Success'] ?? '') === 'Ok' ? ($result['Data'] ?? []) : [];

        return $this->render('view', [
            'document' => $document,
        ]);
    }

    // ── AJAX GET ──────────────────────────────────────────────────────────

    /**
     * Lista órdenes del taller asignadas al técnico logueado.
     * Filtra automáticamente por technician_user_id = usuario actual.
     */
    public function actionList(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $params = Yii::$app->request->queryParams;
            // Filtrar por el técnico logueado (mobile-first: cada técnico ve solo sus órdenes)
            $params['technician_user_id'] = (int) Yii::$app->user->id;
            // Limitar órdenes cerradas/canceladas a últimos 30 días
            $params['days_back'] = 30;
            return $this->asJson($this->getService()->list($params));
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return $this->asJson(['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al listar', 'Data' => []]);
        }
    }

    /**
     * Obtiene una orden completa por docentry.
     */
    public function actionGet(int $docentry): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            return $this->asJson($this->getService()->get($docentry));
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return $this->asJson(['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al obtener', 'Data' => []]);
        }
    }

    /**
     * Opciones para formularios del taller.
     */
    public function actionGetFormOptions(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            return $this->asJson($this->getService()->getFormOptions());
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return $this->asJson(['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al obtener opciones', 'Data' => []]);
        }
    }

    // ── AJAX POST: Flujo de trazabilidad ──────────────────────────────────

    /**
     * Libera una orden: PLANNED → RELEASED
     * POST: {docentry, technician_user_id}
     */
    public function actionRelease(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $docentry = (int) Yii::$app->request->post('docentry');
            $technicianUserId = (int) Yii::$app->request->post('technician_user_id');
            return $this->asJson($this->getService()->release($docentry, $technicianUserId));
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return $this->asJson(['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al liberar', 'Data' => []]);
        }
    }

    /**
     * Inicia una orden: RELEASED → IN_PROGRESS
     * POST: {docentry}
     */
    public function actionStart(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $docentry = (int) Yii::$app->request->post('docentry');
            return $this->asJson($this->getService()->start($docentry));
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return $this->asJson(['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al iniciar', 'Data' => []]);
        }
    }

    /**
     * Finaliza una orden: IN_PROGRESS → PENDING_VALIDATION
     * POST: {docentry}
     */
    public function actionFinish(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $docentry = (int) Yii::$app->request->post('docentry');
            $odometer = Yii::$app->request->post('odometer');
            $odometer = $odometer !== null && $odometer !== '' ? (int) $odometer : null;
            return $this->asJson($this->getService()->complete($docentry, $odometer));
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return $this->asJson(['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al finalizar', 'Data' => []]);
        }
    }

    /**
     * Valida una orden: PENDING_VALIDATION → CLOSED
     * POST: {docentry, validated_by_user_id}
     */
    public function actionValidate(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $docentry = (int) Yii::$app->request->post('docentry');
            $validatedByUserId = (int) Yii::$app->request->post('validated_by_user_id');
            return $this->asJson($this->getService()->validate($docentry, $validatedByUserId));
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return $this->asJson(['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al validar', 'Data' => []]);
        }
    }

    /**
     * Cancela una orden desde el taller.
     * POST: {docentry}
     */
    public function actionCancel(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $docentry = (int) Yii::$app->request->post('docentry');
            return $this->asJson($this->getService()->cancel($docentry));
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return $this->asJson(['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al cancelar', 'Data' => []]);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────

}
