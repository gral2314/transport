<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\objects\DocTireDisposalServices;

use app\models\tables\DocTireDisposal;
use app\models\tables\DocTireDisposalDetail;
use Dompdf\Dompdf;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DocTireDisposalController extends Controller
{
    private ?DocTireDisposalServices $service = null;

    protected function getService(): DocTireDisposalServices
    {
        if ($this->service === null) {
            $this->service = new DocTireDisposalServices();
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
                    'get-available-tires' => ['GET'],
                    'get-next-docnum' => ['GET'],
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
        if (in_array($action->id, ['save'], true)) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex(): string
    {
        $config = $this->buildViewConfig();
        $kpis = $this->buildKpis();

        return $this->render('index', [
            'config' => $config,
            'kpis' => $kpis,
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

        return $this->render('form', [
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

        return $this->render('form', [
            'config' => $config,
            'document' => $document,
            'formOptions' => $formOptions,
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
        $config = $this->buildViewConfig();
        $document = $this->loadDocumentData($docentry);

        return $this->render('preview', [
            'config' => $config,
            'document' => $document,
            'autoPrint' => false,
            'renderMode' => 'html',
        ]);
    }

    public function actionPrint(int $docentry): string
    {
        $config = $this->buildViewConfig();
        $document = $this->loadDocumentData($docentry);

        return $this->render('preview', [
            'config' => $config,
            'document' => $document,
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

        $filename = 'dsp-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($document['docnum'] ?? 'sin-folio')) . '.pdf';

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
                    'subject' => 'Baja de llantas ' . ($document['docnum'] ?? ''),
                    'message' => 'Se adjunta el PDF del documento de baja ' . ($document['docnum'] ?? '') . '.',
                ],
            ]);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $email = trim((string) Yii::$app->request->post('email', ''));
            $subject = trim((string) Yii::$app->request->post('subject', 'Baja de llantas ' . ($document['docnum'] ?? '')));
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

            $filename = 'dsp-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($document['docnum'] ?? 'sin-folio')) . '.pdf';
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

            if (!$sent) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'No fue posible enviar el correo.', 'Data' => []]);
            }

            return $this->asJson(['Success' => 'Ok', 'Msg' => 'Correo enviado correctamente.', 'Data' => []]);
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

    public function actionGetAvailableTires(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $service = $this->getService();
            $tires = $service->getAvailableTires();
            return $this->asJson(['Success' => 'Ok', 'Msg' => '', 'Data' => $tires]);
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

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

    private function buildViewConfig(): array
    {
        return [
            'code' => 'DSP',
            'title' => 'Bajas de llantas',
            'titleSingular' => 'Documento de baja',
            'permissionPrefix' => 'tire.document.disposal',
            'headerClass' => DocTireDisposal::class,
            'headerFields' => [
                ['name' => 'docnum', 'label' => 'Folio', 'type' => 'readonly'],
                ['name' => 'series_id', 'label' => 'Serie', 'type' => 'select', 'optionsFrom' => 'series_options'],
                ['name' => 'doc_date', 'label' => 'Fecha documento', 'type' => 'date'],
                ['name' => 'disposal_date', 'label' => 'Fecha baja', 'type' => 'date'],
                ['name' => 'status', 'label' => 'Estado operativo', 'type' => 'select', 'options' => [
                    DocTireDisposal::STATUS_PLAN => 'Planeado',
                    DocTireDisposal::STATUS_VAL => 'Validado',
                    DocTireDisposal::STATUS_CLOSE => 'Cerrado',
                ]],
            ],
            'detailFields' => [
                ['name' => 'tire_code', 'label' => 'Llanta', 'type' => 'select', 'displayField' => 'tire_name'],
                ['name' => 'disposal_reason', 'label' => 'Motivo baja', 'type' => 'select', 'options' => [
                    DocTireDisposalDetail::DISPOSAL_REASON_WEAR => 'Desgaste',
                    DocTireDisposalDetail::DISPOSAL_REASON_DAMAGE => 'Danio',
                    DocTireDisposalDetail::DISPOSAL_REASON_ACCIDENT => 'Accidente',
                    DocTireDisposalDetail::DISPOSAL_REASON_THEFT => 'Robo',
                    DocTireDisposalDetail::DISPOSAL_REASON_RETREAD_LIMIT => 'Limite renovados',
                    DocTireDisposalDetail::DISPOSAL_REASON_SIDEWALL_DAMAGE => 'Danio lateral',
                    DocTireDisposalDetail::DISPOSAL_REASON_OTHER => 'Otro',
                ]],
                ['name' => 'estimated_loss', 'label' => 'Perdida estimada', 'type' => 'number', 'step' => '0.01'],
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
                ['field' => 'disposal_date', 'label' => 'Baja'],
                ['field' => 'status', 'label' => 'Estado', 'lookup' => [
                    DocTireDisposal::STATUS_PLAN => 'Planeado',
                    DocTireDisposal::STATUS_VAL => 'Validado',
                    DocTireDisposal::STATUS_CLOSE => 'Cerrado',
                ], 'badge' => true],
                ['field' => 'doc_status', 'label' => 'Documento', 'lookup' => [
                    DocTireDisposal::DOC_STATUS_O => 'Abierto',
                    DocTireDisposal::DOC_STATUS_C => 'Cerrado',
                ], 'badge' => true],
                ['field' => 'canceled', 'label' => 'Cancelado', 'lookup' => ['N' => 'No', 'Y' => 'Si'], 'badge' => true],
                ['field' => 'detail_count', 'label' => 'Detalles'],
            ],
            'detailTypeField' => 'disposal_reason',
            'detailTypeOptions' => [
                DocTireDisposalDetail::DISPOSAL_REASON_WEAR => 'Desgaste',
                DocTireDisposalDetail::DISPOSAL_REASON_DAMAGE => 'Danio',
                DocTireDisposalDetail::DISPOSAL_REASON_ACCIDENT => 'Accidente',
                DocTireDisposalDetail::DISPOSAL_REASON_THEFT => 'Robo',
                DocTireDisposalDetail::DISPOSAL_REASON_RETREAD_LIMIT => 'Limite renovados',
                DocTireDisposalDetail::DISPOSAL_REASON_SIDEWALL_DAMAGE => 'Danio lateral',
                DocTireDisposalDetail::DISPOSAL_REASON_OTHER => 'Otro',
            ],
            'controllerId' => $this->id,
            'routes' => [
                'index' => \yii\helpers\Url::to(['doc-tire-disposal/index']),
                'list' => \yii\helpers\Url::to(['doc-tire-disposal/list']),
                'get' => \yii\helpers\Url::to(['doc-tire-disposal/get']),
                'create' => \yii\helpers\Url::to(['doc-tire-disposal/create']),
                'updateBase' => \yii\helpers\Url::to(['doc-tire-disposal/update']),
                'save' => \yii\helpers\Url::to(['doc-tire-disposal/save']),
                'quickViewBase' => \yii\helpers\Url::to(['doc-tire-disposal/quick-view']),
                'previewBase' => \yii\helpers\Url::to(['doc-tire-disposal/preview']),
                'pdfBase' => \yii\helpers\Url::to(['doc-tire-disposal/pdf']),
                'printBase' => \yii\helpers\Url::to(['doc-tire-disposal/print']),
                'sendMailBase' => \yii\helpers\Url::to(['doc-tire-disposal/send-mail']),
                'close' => \yii\helpers\Url::to(['doc-tire-disposal/close']),
                'cancel' => \yii\helpers\Url::to(['doc-tire-disposal/cancel']),
                'getFormOptions' => \yii\helpers\Url::to(['doc-tire-disposal/get-form-options']),
                'getAvailableTires' => \yii\helpers\Url::to(['doc-tire-disposal/get-available-tires']),
                'getNextDocnum' => \yii\helpers\Url::to(['doc-tire-disposal/get-next-docnum']),
            ],
        ];
    }

    private function buildEmptyDocument(): array
    {
        return [
            'docentry' => null,
            'docnum' => 'Se asigna al guardar',
            'series_id' => null,
            'doc_date' => date('Y-m-d'),
            'disposal_date' => date('Y-m-d'),
            'doc_status' => 'O',
            'status' => 'PLAN',
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
        return [
            'total' => (int) DocTireDisposal::find()->count(),
            'open' => (int) DocTireDisposal::find()->where(['doc_status' => 'O', 'canceled' => 'N'])->count(),
            'closed' => (int) DocTireDisposal::find()->where(['doc_status' => 'C', 'canceled' => 'N'])->count(),
            'canceled' => (int) DocTireDisposal::find()->where(['canceled' => 'Y'])->count(),
        ];
    }
}