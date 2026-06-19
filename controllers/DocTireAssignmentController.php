<?php
declare(strict_types=1);

namespace app\controllers;

use app\models\objects\DocTireMovementServices;
use app\models\objects\SeriesServices;
use app\models\tables\DocTireMovement;
use app\models\tables\DocTireMovementDetail;
use app\models\tables\Series;
use app\models\tables\Tire;
use app\models\tables\VehicleTypeAxle;
use app\models\tables\VehicleTire;
use Dompdf\Dompdf;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DocTireAssignmentController extends Controller
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
                    'index'            => ['GET'],
                    'list'             => ['GET'],
                    'get'              => ['GET'],
                    'create'           => ['GET'],
                    'update'           => ['GET'],
                    'save'             => ['POST'],
                    'quick-view'       => ['GET'],
                    'preview'          => ['GET'],
                    'pdf'              => ['GET'],
                    'print'            => ['GET'],
                    'send-mail'        => ['GET', 'POST'],
                    'close'            => ['POST'],
                    'cancel'           => ['POST'],
                    'get-form-options' => ['GET'],
                    'vehicle-layout'   => ['GET'],
                    'available-tires'  => ['GET'],
                    'calendar-events'  => ['GET'],
                    'update-date'      => ['POST'],
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

    public function beforeAction($action): bool
    {
        // Deshabilitar CSRF para endpoints JSON que reciben raw body
        if (in_array($action->id, ['update-date', 'calendar-events'], true)) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex(): string
    {
        $config = $this->buildViewConfig();
        $formOptions = $this->loadFormOptions();
        $mechanicOptions = $formOptions['mechanic_options'] ?? [];

        // Pasar mecánicos como JSON para el select del calendario
        $config['mechanicOptionsJson'] = json_encode($mechanicOptions, JSON_UNESCAPED_UNICODE);

        return $this->render('index', [
            'config' => $config,
            'kpis' => $this->buildKpis(),
        ]);
    }

    public function actionList(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            return $this->asJson($this->getService()->list(Yii::$app->request->queryParams));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGet(int $docentry): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            return $this->asJson($this->getService()->get($docentry));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionCreate(): string
    {
        $config = $this->buildViewConfig();
        $document = $this->buildEmptyDocument();
        $formOptions = $this->loadFormOptions();

        return $this->render('create/index', [
            'config' => $config,
            'document' => $document,
            'formOptions' => $formOptions,
            'isNewRecord' => true,
        ]);
    }

    public function actionUpdate(int $docentry): string
    {
        $config = $this->buildViewConfig();
        $document = $this->loadDocumentData($docentry);
        $formOptions = $this->loadFormOptions();

        return $this->render('create/index', [
            'config' => $config,
            'document' => $document,
            'formOptions' => $formOptions,
            'isNewRecord' => false,
        ]);
    }

    public function actionSave(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Deshabilitar CSRF para este endpoint porque el JS envía JSON sin form data
        $this->enableCsrfValidation = false;
        try {
            // Leer JSON del body (Yii2 no parsea application/json automáticamente)
            $rawBody = Yii::$app->request->getRawBody();
            $data = json_decode($rawBody, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                throw new \RuntimeException('JSON inválido en la petición: ' . json_last_error_msg());
            }
            return $this->asJson($this->getService()->save($data));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionQuickView(int $docentry): string
    {
        $config = $this->buildViewConfig();
        $document = $this->loadDocumentData($docentry);

        return $this->renderAjax('_quick_view', [
            'config' => $config,
            'document' => $document,
        ]);
    }

    public function actionPreview(int $docentry): string
    {
        return $this->render('preview/index', [
            'config' => $this->buildViewConfig(),
            'document' => $this->loadDocumentData($docentry),
            'autoPrint' => false,
            'renderMode' => 'html',
        ]);
    }

    public function actionPrint(int $docentry): string
    {
        return $this->render('preview/index', [
            'config' => $this->buildViewConfig(),
            'document' => $this->loadDocumentData($docentry),
            'autoPrint' => true,
            'renderMode' => 'html',
        ]);
    }

    public function actionPdf(int $docentry): Response
    {
        $config = $this->buildViewConfig();
        $document = $this->loadDocumentData($docentry);

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $html = $this->renderPartial('preview/index', [
            'config' => $config,
            'document' => $document,
            'autoPrint' => false,
            'renderMode' => 'pdf',
        ]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        $filename = 'asg-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($document['docnum'] ?? 'sin-folio')) . '.pdf';

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        $response->content = $dompdf->output();

        return $response;
    }

    public function actionSendMail(int $docentry): Response|string
    {
        $config = $this->buildViewConfig();
        $document = $this->loadDocumentData($docentry);

        if (Yii::$app->request->isGet) {
            return $this->renderAjax('_send_mail', [
                'config' => $config,
                'document' => $document,
                'mailDefaults' => [
                    'subject' => 'Asignacion de llantas ' . ($document['docnum'] ?? ''),
                    'message' => 'Se adjunta el PDF del documento de asignacion ' . ($document['docnum'] ?? '') . '.',
                ],
            ]);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $email = trim((string) Yii::$app->request->post('email', ''));
            $subject = trim((string) Yii::$app->request->post('subject', 'Asignacion de llantas ' . ($document['docnum'] ?? '')));
            $message = trim((string) Yii::$app->request->post('message', ''));

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'Debe indicar un correo valido.', 'Data' => []]);
            }

            $dompdf = new Dompdf(['isRemoteEnabled' => true]);
            $html = $this->renderPartial('preview/index', [
                'config' => $config,
                'document' => $document,
                'autoPrint' => false,
                'renderMode' => 'pdf',
            ]);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4');
            $dompdf->render();
            $pdfContent = $dompdf->output();

            $filename = 'asg-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($document['docnum'] ?? 'sin-folio')) . '.pdf';
            $params = Yii::$app->params;
            $fromEmail = (string) ($params['senderEmail'] ?? 'noreply@example.com');
            $fromName = (string) ($params['senderName'] ?? 'Sistema');

            $sent = Yii::$app->mailer->compose()
                ->setFrom([$fromEmail => $fromName])
                ->setTo($email)
                ->setSubject($subject)
                ->setHtmlBody(nl2br(Yii::$app->formatter->asText($message)))
                ->attachContent($pdfContent, [
                    'fileName' => $filename,
                    'contentType' => 'application/pdf',
                ])
                ->send();

            return $this->asJson($sent
                ? ['Success' => 'Ok', 'Msg' => 'Correo enviado correctamente.', 'Data' => []]
                : ['Success' => 'Error', 'Msg' => 'No fue posible enviar el correo.', 'Data' => []]);
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionClose(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $docentry = (int) Yii::$app->request->post('docentry');
            return $this->asJson($this->getService()->close($docentry));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionCancel(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $docentry = (int) Yii::$app->request->post('docentry');
            return $this->asJson($this->getService()->cancel($docentry));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionRelease(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $docentry = (int) Yii::$app->request->post('docentry');
            $technicianUserId = (int) Yii::$app->request->post('technician_user_id');
            return $this->asJson($this->getService()->release($docentry, $technicianUserId));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGetFormOptions(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            return $this->asJson($this->getService()->getFormOptions());
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    /**
     * Obtiene la configuración física del layout de una unidad (ejes, posiciones, llantas montadas)
     */
    public function actionVehicleLayout(string $vehicleCode): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            // Obtener configuración de ejes y posiciones del vehículo
            $vehicle = \app\models\tables\Vehicle::find()
                ->alias('v')
                ->select(['v.*', 'vt.name AS vehicle_type_name', 'vt.type_unidad'])
                ->leftJoin('vehicle_type vt', 'vt.code = v.vehicle_type_code')
                ->where(['v.vehicle_code' => $vehicleCode])
                ->asArray()
                ->one();

            if (!$vehicle) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'Vehículo no encontrado', 'Data' => []]);
            }

            $axles = \app\models\tables\VehicleTypeAxle::find()
                ->alias('vta')
                ->select(['vta.*', 'at.name AS axle_type_name', 'at.tire_qty'])
                ->leftJoin('axle_type at', 'at.code = vta.axle_type_code')
                ->where(['vta.code' => $vehicle['vehicle_type_code']])
                ->orderBy(['vta.line_num' => SORT_ASC])
                ->asArray()
                ->all();

            // Obtener llantas actualmente montadas en este vehículo
            $mountedTires = \app\models\tables\VehicleTire::find()
                ->alias('vt')
                ->select([
                    'vt.*',
                    't.tire_name',
                    't.tire_code',
                    't.size_code AS tire_size',
                    't.tread_design_code AS tread_design',
                ])
                ->leftJoin('tire t', 't.tire_code = vt.tire_code')
                ->where(['vt.vehicle_code' => $vehicleCode])
                ->asArray()
                ->all();

            return $this->asJson([
                'Success' => 'Ok',
                'Msg' => '',
                'Data' => [
                    'vehicle' => $vehicle,
                    'axles' => $axles,
                    'mounted_tires' => $mountedTires,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    /**
     * Obtiene llantas disponibles en almacén (no montadas en ninguna unidad activa)
     */
    public function actionAvailableTires(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $tireService = new \app\models\objects\TireServices();
            $result = $tireService->getAvailableForAssignment();

            return $this->asJson($result);
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    /**
     * Endpoint para FullCalendar: devuelve eventos con start/end dates.
     * GET ?technician_user_id=123 (opcional, filtra por mecánico)
     */
    public function actionCalendarEvents(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $params = Yii::$app->request->queryParams;
            return $this->asJson($this->getService()->getCalendarEvents($params));
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return $this->asJson(['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al obtener eventos', 'Data' => []]);
        }
    }

    /**
     * Endpoint para drag & drop: actualiza fechas de un documento.
     * POST body: { docentry, start_date, end_date }
     * Solo permite documentos en estado PLANNED.
     */
    public function actionUpdateDate(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $rawBody = Yii::$app->request->getRawBody();
            $data = json_decode($rawBody, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                throw new \RuntimeException('JSON inválido: ' . json_last_error_msg());
            }
            return $this->asJson($this->getService()->updateDate($data));
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return $this->asJson(['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al actualizar fecha', 'Data' => []]);
        }
    }

    private function buildViewConfig(): array
    {
        return [
            'code' => 'ASG',
            'title' => 'Asignaciones de llantas',
            'titleSingular' => 'Documento de asignacion',
            'permissionPrefix' => 'tire.document.assignment',
            'headerClass' => DocTireMovement::class,
            'defaults' => [
                'priority' => DocTireMovement::PRIORITY_LOW,
                'origin_type' => DocTireMovement::ORIGIN_TYPE_MANUAL,
            ],
            'headerFields' => [
                ['name' => 'docnum', 'label' => 'Folio', 'type' => 'readonly'],
                ['name' => 'series_id', 'label' => 'Serie', 'type' => 'select', 'optionsFrom' => 'series_options'],
                ['name' => 'status', 'label' => 'Estatus', 'type' => 'select', 'options' => [
                    DocTireMovement::STATUS_PLANNED => 'Planeado',
                    DocTireMovement::STATUS_RELEASED => 'Liberado',
                    DocTireMovement::STATUS_IN_PROGRESS => 'En Proceso',
                    DocTireMovement::STATUS_PENDING_VALIDATION => 'Pendiente Validar',
                    DocTireMovement::STATUS_CLOSED => 'Cerrado',
                    DocTireMovement::STATUS_CANCELLED => 'Cancelado',
                ]],
                ['name' => 'technician_user_id', 'label' => 'Tecnico asignado', 'type' => 'select', 'optionsFrom' => 'mechanic_options', 'optionFields' => ['employee_code', 'first_name', 'last_name']],
                ['name' => 'doc_date', 'label' => 'Fecha documento', 'type' => 'date'],
                ['name' => 'doc_duedate', 'label' => 'Fecha ejecucion', 'type' => 'date'],
                ['name' => 'priority', 'label' => 'Prioridad', 'type' => 'select', 'options' => [
                    DocTireMovement::PRIORITY_LOW => 'Baja',
                    DocTireMovement::PRIORITY_MEDIUM => 'Media',
                    DocTireMovement::PRIORITY_HIGH => 'Alta',
                    DocTireMovement::PRIORITY_URGENT => 'Urgente',
                ]],
                ['name' => 'origin_type', 'label' => 'Origen', 'type' => 'select', 'options' => [
                    DocTireMovement::ORIGIN_TYPE_MANUAL => 'Manual',
                    DocTireMovement::ORIGIN_TYPE_MAINTENANCE => 'Mantenimiento',
                    DocTireMovement::ORIGIN_TYPE_INSPECTION => 'Inspeccion',
                    DocTireMovement::ORIGIN_TYPE_REPAIR => 'Reparacion',
                    DocTireMovement::ORIGIN_TYPE_WAREHOUSE => 'Almacen',
                ]],
            ],
            'detailFields' => [
                ['name' => 'movement_type', 'label' => 'Tipo movimiento', 'type' => 'select', 'options' => [
                    DocTireMovementDetail::MOVEMENT_TYPE_ASSIGN => 'Asignacion',
                    DocTireMovementDetail::MOVEMENT_TYPE_ROTATE => 'Rotacion',
                    DocTireMovementDetail::MOVEMENT_TYPE_REMOVE => 'Retiro',
                    DocTireMovementDetail::MOVEMENT_TYPE_TRANSFER => 'Traslado',
                    DocTireMovementDetail::MOVEMENT_TYPE_REPAIR_SEND => 'Envio reparacion',
                    DocTireMovementDetail::MOVEMENT_TYPE_REPAIR_RETURN => 'Retorno reparacion',
                    DocTireMovementDetail::MOVEMENT_TYPE_SCRAP => 'Baja',
                ]],
                ['name' => 'tire_code', 'label' => 'Llanta', 'type' => 'select', 'displayField' => 'tire_name'],
                ['name' => 'related_tire_code', 'label' => 'Llanta relacionada', 'type' => 'select', 'displayField' => 'related_tire_name'],
                ['name' => 'vehicle_code_from', 'label' => 'Unidad origen', 'type' => 'select', 'displayField' => 'vehicle_from_name'],
                ['name' => 'vehicle_code_to', 'label' => 'Unidad destino', 'type' => 'select', 'displayField' => 'vehicle_to_name'],
                ['name' => 'warehouse_code_from', 'label' => 'Almacen origen', 'type' => 'select'],
                ['name' => 'warehouse_code_to', 'label' => 'Almacen destino', 'type' => 'select'],
                ['name' => 'position_from', 'label' => 'Posicion origen', 'type' => 'text'],
                ['name' => 'position_to', 'label' => 'Posicion destino', 'type' => 'text'],
                ['name' => 'comments', 'label' => 'Comentarios', 'type' => 'text'],
            ],
            'attachmentFields' => [
                ['name' => 'linenum', 'label' => 'Linea detalle', 'type' => 'number', 'step' => '1'],
                ['name' => 'filename', 'label' => 'Archivo', 'type' => 'text'],
                ['name' => 'filepath', 'label' => 'Ruta', 'type' => 'text'],
                ['name' => 'notes', 'label' => 'Notas', 'type' => 'text'],
            ],
            'listColumns' => [
                ['field' => 'docentry', 'label' => 'DocEntry'],
                ['field' => 'docnum', 'label' => 'Folio'],
                ['field' => 'doc_date', 'label' => 'Fecha'],
                ['field' => 'doc_duedate', 'label' => 'Programada'],
                ['field' => 'priority', 'label' => 'Prioridad', 'lookup' => [
                    DocTireMovement::PRIORITY_LOW => 'Baja',
                    DocTireMovement::PRIORITY_MEDIUM => 'Media',
                    DocTireMovement::PRIORITY_HIGH => 'Alta',
                    DocTireMovement::PRIORITY_URGENT => 'Urgente',
                ]],
                ['field' => 'status', 'label' => 'Estado', 'lookup' => [
                    DocTireMovement::STATUS_PLANNED => 'Planeado',
                    DocTireMovement::STATUS_RELEASED => 'Liberado',
                    DocTireMovement::STATUS_IN_PROGRESS => 'En Proceso',
                    DocTireMovement::STATUS_PENDING_VALIDATION => 'Pendiente Validar',
                    DocTireMovement::STATUS_CLOSED => 'Cerrado',
                    DocTireMovement::STATUS_CANCELLED => 'Cancelado',
                ], 'badge' => true],
                ['field' => 'doc_status', 'label' => 'Documento', 'lookup' => [
                    DocTireMovement::DOC_STATUS_O => 'Abierto',
                    DocTireMovement::DOC_STATUS_C => 'Cerrado',
                ], 'badge' => true],
                ['field' => 'canceled', 'label' => 'Cancelado', 'lookup' => ['N' => 'No', 'Y' => 'Si'], 'badge' => true],
                ['field' => 'vehicle_count', 'label' => 'Unidades'],
                ['field' => 'detail_count', 'label' => 'Detalles'],
            ],
            'detailTypeField' => 'movement_type',
            'detailTypeOptions' => [
                DocTireMovementDetail::MOVEMENT_TYPE_ASSIGN => 'Asignacion',
                DocTireMovementDetail::MOVEMENT_TYPE_ROTATE => 'Rotacion',
                DocTireMovementDetail::MOVEMENT_TYPE_REMOVE => 'Retiro',
                DocTireMovementDetail::MOVEMENT_TYPE_TRANSFER => 'Traslado',
                DocTireMovementDetail::MOVEMENT_TYPE_REPAIR_SEND => 'Envio reparacion',
                DocTireMovementDetail::MOVEMENT_TYPE_REPAIR_RETURN => 'Retorno reparacion',
                DocTireMovementDetail::MOVEMENT_TYPE_SCRAP => 'Baja',
            ],
            'controllerId' => $this->id,
            'routes' => [
                'index' => Url::to(['doc-tire-assignment/index']),
                'list' => Url::to(['doc-tire-assignment/list']),
                'get' => Url::to(['doc-tire-assignment/get']),
                'create' => Url::to(['doc-tire-assignment/create']),
                'updateBase' => Url::to(['doc-tire-assignment/update']),
                'save' => Url::to(['doc-tire-assignment/save']),
                'quickViewBase' => Url::to(['doc-tire-assignment/quick-view']),
                'previewBase' => Url::to(['doc-tire-assignment/preview']),
                'pdfBase' => Url::to(['doc-tire-assignment/pdf']),
                'printBase' => Url::to(['doc-tire-assignment/print']),
                'sendMailBase' => Url::to(['doc-tire-assignment/send-mail']),
                'close' => Url::to(['doc-tire-assignment/close']),
                'cancel' => Url::to(['doc-tire-assignment/cancel']),
                'release' => Url::to(['doc-tire-assignment/release']),
                'getFormOptions' => Url::to(['doc-tire-assignment/get-form-options']),
                'getNextNumber' => Url::to(['series/get-next-number']),
                'peekNextNumber' => Url::to(['series/peek-next-number']),
                'vehicleLayout' => Url::to(['doc-tire-assignment/vehicle-layout']),
                'availableTires' => Url::to(['doc-tire-assignment/available-tires']),
                'calendarEvents' => Url::to(['doc-tire-assignment/calendar-events']),
                'updateDate' => Url::to(['doc-tire-assignment/update-date']),
            ],
        ];
    }

    private function buildEmptyDocument(): array
    {
        // Buscar la serie default para DocTireMovement
        $defaultSeries = Series::find()
            ->where([
                'object_name' => 'DocTireMovement',
                'is_active' => Series::ACTIVE_Y,
                'is_default' => Series::IS_DEFAULT_Y,
            ])
            ->one();

        $seriesId = null;
        $docnum = 'Se asigna al guardar';

        if ($defaultSeries !== null) {
            $seriesId = (int)$defaultSeries->id;
            // Pre-resolver el siguiente número de la serie default (solo lectura, NO incrementa)
            $seriesServices = new SeriesServices();
            $result = $seriesServices->peekNextNumber('DocTireMovement', $seriesId);
            if (($result['Success'] ?? 'Error') === 'Ok' && $result['Data'] !== null) {
                $docnum = $result['Data']['docNum'];
            }
        }

        return [
            'docentry' => null,
            'docnum' => $docnum,
            'series_id' => $seriesId,
            'doc_date' => date('Y-m-d'),
            'doc_duedate' => date('Y-m-d'),
            'doc_status' => 'O',
            'status' => 'PLAN',
            'canceled' => 'N',
            'priority' => DocTireMovement::PRIORITY_LOW,
            'origin_type' => DocTireMovement::ORIGIN_TYPE_MANUAL,
            'comments' => '',
            'vehicles' => [],
            'details' => [],
            'attachments' => [],
        ];
    }

    private function loadDocumentData(int $docentry): array
    {
        $response = $this->getService()->get($docentry);
        if (($response['Success'] ?? 'Error') !== 'Ok') {
            throw new NotFoundHttpException($response['Msg'] ?? 'Documento no encontrado.');
        }
        return is_array($response['Data'] ?? null) ? $response['Data'] : [];
    }

    private function loadFormOptions(): array
    {
        $response = $this->getService()->getFormOptions();
        if (($response['Success'] ?? 'Error') !== 'Ok') {
            return [];
        }
        return is_array($response['Data'] ?? null) ? $response['Data'] : [];
    }

    private function buildKpis(): array
    {
        return [
            'total' => (int) DocTireMovement::find()->count(),
            'open' => (int) DocTireMovement::find()->where(['doc_status' => 'O', 'canceled' => 'N'])->count(),
            'closed' => (int) DocTireMovement::find()->where(['doc_status' => 'C', 'canceled' => 'N'])->count(),
            'canceled' => (int) DocTireMovement::find()->where(['canceled' => 'Y'])->count(),
        ];
    }
}