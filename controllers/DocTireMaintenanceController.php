<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\objects\DocTireRepairServices;
use app\models\tables\DocTireRepair;
use app\models\tables\DocTireRepairDetail;
use Dompdf\Dompdf;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DocTireMaintenanceController extends Controller
{
    private ?DocTireRepairServices $service = null;

    protected function getService(): DocTireRepairServices
    {
        if ($this->service === null) {
            $this->service = new DocTireRepairServices();
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
                    'close'              => ['POST'],
                    'cancel'             => ['POST'],
                    'release'            => ['POST'],
                    'start'              => ['POST'],
                    'execute'            => ['POST'],
                    'validate'           => ['POST'],
                    'reject'             => ['POST'],
                    'get-form-options'   => ['GET'],
                    'get-available-tires' => ['GET'],
                    'get-next-docnum'    => ['GET'],
                    'calendar-events'    => ['GET'],
                    'update-date'        => ['POST'],
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

    public function actionIndex(): string
    {
        $config = $this->buildViewConfig();
        $formOptions = $this->loadFormOptions();
        $providerOptions = $formOptions['provider_options'] ?? [];

        // Pasar proveedores como JSON para el select del calendario
        $config['providerOptionsJson'] = json_encode($providerOptions, JSON_UNESCAPED_UNICODE);

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
        return $this->render('form', [
            'config' => $this->buildViewConfig(),
            'document' => $this->buildEmptyDocument(),
            'formOptions' => $this->loadFormOptions(),
            'isNewRecord' => true,
        ]);
    }

    public function actionUpdate(int $docentry): string
    {
        return $this->render('form', [
            'config' => $this->buildViewConfig(),
            'document' => $this->loadDocumentData($docentry),
            'formOptions' => $this->loadFormOptions(),
            'isNewRecord' => false,
        ]);
    }

    public function actionSave(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $data = Yii::$app->request->post();
            foreach (['details', 'attachments'] as $key) {
                if (isset($data[$key]) && is_string($data[$key])) {
                    $decoded = json_decode($data[$key], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $data[$key] = $decoded;
                    }
                }
            }

            // Procesar archivos adjuntos subidos
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $idx => &$attach) {
                    $fileKey = 'attach_file_' . $idx;
                    $uploadedFile = \yii\web\UploadedFile::getInstanceByName($fileKey);
                    if ($uploadedFile !== null && $uploadedFile->error === UPLOAD_ERR_OK) {
                        $attach['_uploaded_file'] = $uploadedFile;
                    }
                }
                unset($attach);
            }

            return $this->asJson($this->getService()->save($data));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionQuickView(int $docentry): string
    {
        return $this->renderAjax('_quick_view', [
            'config' => $this->buildViewConfig(),
            'document' => $this->loadDocumentData($docentry),
        ]);
    }

    public function actionPreview(?int $docentry = null): string
    {
        if ($docentry === null) {
            $docentry = (int)($this->request->get('docentry') ?? 0);
        }
        return $this->render('preview', [
            'config' => $this->buildViewConfig(),
            'document' => $this->loadDocumentData($docentry),
            'autoPrint' => false,
            'renderMode' => 'html',
        ]);
    }

    public function actionPrint(int $docentry): string
    {
        return $this->render('preview', [
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
        $html = $this->renderPartial('preview', [
            'config' => $config,
            'document' => $document,
            'autoPrint' => false,
            'renderMode' => 'pdf',
        ]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        $filename = 'mnt-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($document['docnum'] ?? 'sin-folio')) . '.pdf';

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
                    'subject' => 'Mantenimiento de llantas ' . ($document['docnum'] ?? ''),
                    'message' => 'Se adjunta el PDF del documento de mantenimiento ' . ($document['docnum'] ?? '') . '.',
                ],
            ]);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $email = trim((string) Yii::$app->request->post('email', ''));
            $subject = trim((string) Yii::$app->request->post('subject', 'Mantenimiento de llantas ' . ($document['docnum'] ?? '')));
            $message = trim((string) Yii::$app->request->post('message', ''));

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'Debe indicar un correo valido.', 'Data' => []]);
            }

            $dompdf = new Dompdf(['isRemoteEnabled' => true]);
            $html = $this->renderPartial('preview', [
                'config' => $config,
                'document' => $document,
                'autoPrint' => false,
                'renderMode' => 'pdf',
            ]);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4');
            $dompdf->render();
            $pdfContent = $dompdf->output();

            $filename = 'mnt-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($document['docnum'] ?? 'sin-folio')) . '.pdf';
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
     * GET calendar-events — Eventos FullCalendar para el calendario de mantenimientos
     */
    public function actionCalendarEvents(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $params = Yii::$app->request->get();
            return $this->asJson($this->getService()->getCalendarEvents($params));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    /**
     * POST update-date — Actualiza fecha por drag & drop en calendario
     */
    public function actionUpdateDate(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $data = Yii::$app->request->post();
            return $this->asJson($this->getService()->updateDate($data));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    // ── AJAX POST: Flujo de trazabilidad (7 estados) ───────────────────────

    /**
     * Libera una orden de mantenimiento: PLAN → LIB
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
     * Inicia trabajo de taller: LIB → TALLER
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
     * Ejecuta el mantenimiento: TALLER → EXEC
     * POST: {docentry}
     */
    public function actionExecute(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $docentry = (int) Yii::$app->request->post('docentry');
            return $this->asJson($this->getService()->execute($docentry));
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return $this->asJson(['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al ejecutar', 'Data' => []]);
        }
    }

    /**
     * Valida la ejecución: EXEC → VAL
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
     * Rechaza la validación y regresa a taller: VAL → TALLER
     * POST: {docentry, rejection_notes}
     */
    public function actionReject(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $docentry = (int) Yii::$app->request->post('docentry');
            $rejectedByUserId = (int) (Yii::$app->user->identity->id ?? 0);
            $rejectionNotes = trim((string) Yii::$app->request->post('rejection_notes', ''));
            return $this->asJson($this->getService()->reject($docentry, $rejectedByUserId, $rejectionNotes));
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return $this->asJson(['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al rechazar', 'Data' => []]);
        }
    }

    /**
     * GET get-available-tires — Llantas disponibles para mantenimiento
     */
    public function actionGetAvailableTires(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $params = Yii::$app->request->get();
            return $this->asJson($this->getService()->getAvailableTires($params));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    /**
     * GET get-next-docnum — Siguiente número de documento por serie
     */
    public function actionGetNextDocnum(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $seriesId = (int) Yii::$app->request->get('series_id');
            return $this->asJson($this->getService()->getNextDocnum($seriesId));
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    private function buildEmptyDocument(): array
    {
        return [
            'docentry' => null,
            'docnum' => 'Se asigna al guardar',
            'series_id' => null,
            'doc_date' => date('Y-m-d'),
            'return_date' => date('Y-m-d'),
            'provider_code' => null,
            'doc_status' => DocTireRepair::DOC_STATUS_O,
            'status' => DocTireRepair::STATUS_PLAN,
            'canceled' => 'N',
            'comments' => '',
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
        $activeStatuses = [DocTireRepair::STATUS_PLAN, DocTireRepair::STATUS_LIB, DocTireRepair::STATUS_TALLER, DocTireRepair::STATUS_EXEC, DocTireRepair::STATUS_VAL];
        return [
            'total' => (int) DocTireRepair::find()->count(),
            'open' => (int) DocTireRepair::find()->where(['doc_status' => DocTireRepair::DOC_STATUS_O, 'canceled' => 'N'])->count(),
            'active' => (int) DocTireRepair::find()->where(['status' => $activeStatuses, 'canceled' => 'N'])->count(),
            'closed' => (int) DocTireRepair::find()->where(['doc_status' => DocTireRepair::DOC_STATUS_C, 'canceled' => 'N'])->count(),
            'canceled' => (int) DocTireRepair::find()->where(['canceled' => 'Y'])->count(),
        ];
    }

    private function buildViewConfig(): array
    {
        return [
            'code' => 'MNT',
            'title' => 'Mantenimientos de llantas',
            'titleSingular' => 'Documento de mantenimiento',
            'permissionPrefix' => 'tire.document.maintenance',
            'headerClass' => DocTireRepair::class,
            'headerFields' => [
                ['name' => 'docnum', 'label' => 'Folio', 'type' => 'readonly'],
                ['name' => 'doc_date', 'label' => 'Fecha documento', 'type' => 'date', 'required' => true],
                ['name' => 'repair_date', 'label' => 'Fecha de reparacion', 'type' => 'date', 'required' => true],
                ['name' => 'return_date', 'label' => 'Fecha de retorno', 'type' => 'date'],
                ['name' => 'provider_code', 'label' => 'Proveedor', 'type' => 'select', 'optionsFrom' => 'provider_options'],
                ['name' => 'series_id', 'label' => 'Serie', 'type' => 'select', 'optionsFrom' => 'series_options'],
                ['name' => 'status', 'label' => 'Estado operativo', 'type' => 'select', 'options' => [
                    DocTireRepair::STATUS_PLAN      => 'Planeado',
                    DocTireRepair::STATUS_LIB       => 'Liberado',
                    DocTireRepair::STATUS_TALLER    => 'Taller',
                    DocTireRepair::STATUS_EXEC      => 'Ejecutado',
                    DocTireRepair::STATUS_VAL       => 'Validado',
                    DocTireRepair::STATUS_CLOSE     => 'Cerrado',
                    DocTireRepair::STATUS_CANCELLED => 'Cancelado',
                ]],
                ['name' => 'technician_user_id', 'label' => 'Técnico', 'type' => 'select', 'optionsFrom' => 'technician_options'],
            ],
            'detailFields' => [
                ['name' => 'tire_code', 'label' => 'Llanta', 'type' => 'text', 'readonly' => true],
                ['name' => 'tire_km', 'label' => 'Kilometraje', 'type' => 'number', 'readonly' => true],
                ['name' => 'tread_depth', 'label' => 'Profundidad (mm)', 'type' => 'text', 'readonly' => true],
                ['name' => 'repair_type', 'label' => 'Tipo reparacion', 'type' => 'select', 'options' => [
                    DocTireRepairDetail::REPAIR_TYPE_PUNCTURE => 'Ponchadura',
                    DocTireRepairDetail::REPAIR_TYPE_PATCH => 'Parche',
                    DocTireRepairDetail::REPAIR_TYPE_RETREAD => 'Renovado',
                    DocTireRepairDetail::REPAIR_TYPE_BALANCE => 'Balanceo',
                    DocTireRepairDetail::REPAIR_TYPE_ALIGNMENT => 'Alineacion',
                    DocTireRepairDetail::REPAIR_TYPE_ROTATION => 'Rotacion',
                    DocTireRepairDetail::REPAIR_TYPE_OTHER => 'Otro',
                ]],
                ['name' => 'comments', 'label' => 'Comentarios', 'type' => 'text'],
            ],
            'attachmentFields' => [
                ['name' => 'filename', 'label' => 'Archivo', 'type' => 'file'],
                ['name' => 'notes', 'label' => 'Notas', 'type' => 'text'],
            ],
            'listColumns' => [
                ['field' => 'docentry', 'label' => 'DocEntry'],
                ['field' => 'docnum', 'label' => 'Folio'],
                ['field' => 'doc_date', 'label' => 'Fecha'],
                ['field' => 'return_date', 'label' => 'Retorno'],
                ['field' => 'provider_name', 'label' => 'Proveedor'],
                ['field' => 'status', 'label' => 'Estado', 'lookup' => [
                    DocTireRepair::STATUS_PLAN      => 'Planeado',
                    DocTireRepair::STATUS_LIB       => 'Liberado',
                    DocTireRepair::STATUS_TALLER    => 'Taller',
                    DocTireRepair::STATUS_EXEC      => 'Ejecutado',
                    DocTireRepair::STATUS_VAL       => 'Validado',
                    DocTireRepair::STATUS_CLOSE     => 'Cerrado',
                    DocTireRepair::STATUS_CANCELLED => 'Cancelado',
                ], 'badge' => true],
                ['field' => 'doc_status', 'label' => 'Documento', 'lookup' => [
                    DocTireRepair::DOC_STATUS_O => 'Abierto',
                    DocTireRepair::DOC_STATUS_C => 'Cerrado',
                ], 'badge' => true],
                ['field' => 'canceled', 'label' => 'Cancelado', 'lookup' => ['N' => 'No', 'Y' => 'Si'], 'badge' => true],
                ['field' => 'detail_count', 'label' => 'Detalles'],
            ],
            'detailTypeField' => 'repair_type',
            'detailTypeOptions' => [
                DocTireRepairDetail::REPAIR_TYPE_PUNCTURE => 'Ponchadura',
                DocTireRepairDetail::REPAIR_TYPE_PATCH => 'Parche',
                DocTireRepairDetail::REPAIR_TYPE_RETREAD => 'Renovado',
                DocTireRepairDetail::REPAIR_TYPE_BALANCE => 'Balanceo',
                DocTireRepairDetail::REPAIR_TYPE_ALIGNMENT => 'Alineacion',
                DocTireRepairDetail::REPAIR_TYPE_ROTATION => 'Rotacion',
                DocTireRepairDetail::REPAIR_TYPE_OTHER => 'Otro',
            ],
            'controllerId' => $this->id,
            'routes' => [
                'index'            => Url::to(['doc-tire-maintenance/index']),
                'list'             => Url::to(['doc-tire-maintenance/list']),
                'get'              => Url::to(['doc-tire-maintenance/get']),
                'create'           => Url::to(['doc-tire-maintenance/create']),
                'updateBase'       => Url::to(['doc-tire-maintenance/update']),
                'save'             => Url::to(['doc-tire-maintenance/save']),
                'quickViewBase'    => Url::to(['doc-tire-maintenance/quick-view']),
                'previewBase'      => Url::to(['doc-tire-maintenance/preview']),
                'pdfBase'          => Url::to(['doc-tire-maintenance/pdf']),
                'printBase'        => Url::to(['doc-tire-maintenance/print']),
                'sendMailBase'     => Url::to(['doc-tire-maintenance/send-mail']),
                'close'              => Url::to(['doc-tire-maintenance/close']),
                'cancel'             => Url::to(['doc-tire-maintenance/cancel']),
                'release'            => Url::to(['doc-tire-maintenance/release']),
                'start'              => Url::to(['doc-tire-maintenance/start']),
                'execute'            => Url::to(['doc-tire-maintenance/execute']),
                'validate'           => Url::to(['doc-tire-maintenance/validate']),
                'reject'             => Url::to(['doc-tire-maintenance/reject']),
                'getFormOptions'     => Url::to(['doc-tire-maintenance/get-form-options']),
                'getAvailableTires'  => Url::to(['doc-tire-maintenance/get-available-tires']),
                'getNextDocnum'      => Url::to(['doc-tire-maintenance/get-next-docnum']),
                'calendarEvents'     => Url::to(['doc-tire-maintenance/calendar-events']),
                'updateDate'         => Url::to(['doc-tire-maintenance/update-date']),
            ],
            'providerOptions' => $this->loadProviderOptions(),
        ];
    }

    private function loadProviderOptions(): array
    {
        $formOptions = $this->loadFormOptions();
        return $formOptions['provider_options'] ?? [];
    }
}