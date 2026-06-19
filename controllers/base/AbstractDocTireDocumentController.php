<?php

declare(strict_types=1);

namespace app\controllers\base;

use Dompdf\Dompdf;
use Yii;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\mail\MailerInterface;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

abstract class AbstractDocTireDocumentController extends Controller
{
    abstract protected function documentService(): object;

    abstract protected function documentConfig(): array;

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET'],
                    'list' => ['GET'],
                    'get' => ['GET'],
                    'create' => ['GET'],
                    'update' => ['GET'],
                    'save' => ['POST'],
                    'quick-view' => ['GET'],
                    'preview' => ['GET'],
                    'pdf' => ['GET'],
                    'print' => ['GET'],
                    'send-mail' => ['GET', 'POST'],
                    'close' => ['POST'],
                    'cancel' => ['POST'],
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

    public function actionIndex(): string
    {
        //$this->requirePermission('index');

        return $this->render('index', [
            'config' => $this->buildViewConfig(),
            'kpis' => $this->buildKpis(),
        ]);
    }

    public function actionList(): Response
    {
        return $this->asJsonResponse('list', fn(): array => $this->documentService()->list(Yii::$app->request->queryParams));
    }

    public function actionGet(int $docentry): Response
    {
        return $this->asJsonResponse('get', fn(): array => $this->documentService()->get($docentry));
    }

    public function actionCreate(): string
    {
        //$this->requirePermission('create');

        return $this->render('form', [
            'config' => $this->buildViewConfig(),
            'document' => $this->buildEmptyDocument(),
            'formOptions' => $this->loadFormOptions(),
            'isNewRecord' => true,
        ]);
    }

    public function actionUpdate(int $docentry): string
    {
        //$this->requirePermission('update');

        return $this->render('form', [
            'config' => $this->buildViewConfig(),
            'document' => $this->loadDocumentData($docentry),
            'formOptions' => $this->loadFormOptions(),
            'isNewRecord' => false,
        ]);
    }

    public function actionSave(): Response
    {
        $data = Yii::$app->request->post();
        foreach (['vehicles', 'details', 'attachments'] as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $decoded = json_decode($data[$key], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $data[$key] = $decoded;
                }
            }
        }
        $permission = empty($data['docentry']) ? 'create' : 'update';

        return $this->asJsonResponse($permission, fn(): array => $this->documentService()->save($data));
    }

    public function actionQuickView(int $docentry): string
    {
        //$this->requirePermission('quick-view');

        return $this->renderAjax('_quick_view', [
            'config' => $this->buildViewConfig(),
            'document' => $this->loadDocumentData($docentry),
        ]);
    }

    public function actionPreview(int $docentry): string
    {
        //$this->requirePermission('preview');

        return $this->render('preview', $this->buildPreviewPayload($docentry));
    }

    public function actionPrint(int $docentry): string
    {
        //$this->requirePermission('print');

        return $this->render('preview', $this->buildPreviewPayload($docentry, true));
    }

    public function actionPdf(int $docentry): Response
    {
        //$this->requirePermission('pdf');

        $document = $this->loadDocumentData($docentry);
        $pdfContent = $this->renderPdfContent($document);
        $filename = $this->buildDocumentFilename($document) . '.pdf';

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        $response->content = $pdfContent;

        return $response;
    }

    public function actionSendMail(int $docentry): Response|string
    {
        //$this->requirePermission('send-mail');

        $document = $this->loadDocumentData($docentry);

        if (Yii::$app->request->isGet) {
            return $this->renderAjax('_send_mail', [
                'config' => $this->buildViewConfig(),
                'document' => $document,
                'mailDefaults' => [
                    'subject' => $this->defaultMailSubject($document),
                    'message' => $this->defaultMailMessage($document),
                ],
            ]);
        }

        return $this->asJsonResponse(null, fn(): array => $this->sendDocumentMail($document, Yii::$app->request->post()), ['send-mail']);
    }

    public function actionClose(): Response
    {
        $docentry = (int) Yii::$app->request->post('docentry');

        return $this->asJsonResponse('close', fn(): array => $this->documentService()->close($docentry));
    }

    public function actionCancel(): Response
    {
        $docentry = (int) Yii::$app->request->post('docentry');

        return $this->asJsonResponse('cancel', fn(): array => $this->documentService()->cancel($docentry));
    }

    public function actionGetFormOptions(): Response
    {
        return $this->asJsonResponse(null, fn(): array => $this->documentService()->getFormOptions(), ['index', 'get', 'create', 'update']);
    }

    protected function buildViewConfig(): array
    {
        $config = $this->documentConfig();
        $config['controllerId'] = $this->id;
        $config['routes'] = [
            'index' => Url::to([$this->id . '/index']),
            'list' => Url::to([$this->id . '/list']),
            'get' => Url::to([$this->id . '/get']),
            'create' => Url::to([$this->id . '/create']),
            'updateBase' => Url::to([$this->id . '/update']),
            'save' => Url::to([$this->id . '/save']),
            'quickViewBase' => Url::to([$this->id . '/quick-view']),
            'previewBase' => Url::to([$this->id . '/preview']),
            'pdfBase' => Url::to([$this->id . '/pdf']),
            'printBase' => Url::to([$this->id . '/print']),
            'sendMailBase' => Url::to([$this->id . '/send-mail']),
            'close' => Url::to([$this->id . '/close']),
            'cancel' => Url::to([$this->id . '/cancel']),
            'getFormOptions' => Url::to([$this->id . '/get-form-options']),
        ];

        return $config;
    }

    protected function buildEmptyDocument(): array
    {
        $config = $this->documentConfig();

        return array_replace_recursive([
            'docentry' => null,
            'docnum' => 'Se asigna al guardar',
            'doc_date' => date('Y-m-d'),
            'doc_duedate' => date('Y-m-d'),
            'doc_status' => 'O',
            'status' => 'PLAN',
            'canceled' => 'N',
            'comments' => '',
            'vehicles' => [],
            'details' => [],
            'attachments' => [],
        ], $config['defaults'] ?? []);
    }

    protected function loadDocumentData(int $docentry): array
    {
        $response = $this->documentService()->get($docentry);
        if (($response['Success'] ?? 'Error') !== 'Ok') {
            throw new NotFoundHttpException($response['Msg'] ?? 'Documento no encontrado.');
        }

        return is_array($response['Data'] ?? null) ? $response['Data'] : [];
    }

    protected function loadFormOptions(): array
    {
        $response = $this->documentService()->getFormOptions();
        if (($response['Success'] ?? 'Error') !== 'Ok') {
            throw new BadRequestHttpException($response['Msg'] ?? 'No fue posible cargar las opciones del formulario.');
        }

        return is_array($response['Data'] ?? null) ? $response['Data'] : [];
    }

    protected function buildKpis(): array
    {
        $config = $this->documentConfig();
        $headerClass = $config['headerClass'] ?? null;
        if (!is_string($headerClass) || !is_subclass_of($headerClass, ActiveRecord::class)) {
            return ['total' => 0, 'open' => 0, 'closed' => 0, 'canceled' => 0];
        }

        return [
            'total' => (int) $headerClass::find()->count(),
            'open' => (int) $headerClass::find()->where(['doc_status' => 'O', 'canceled' => 'N'])->count(),
            'closed' => (int) $headerClass::find()->where(['doc_status' => 'C', 'canceled' => 'N'])->count(),
            'canceled' => (int) $headerClass::find()->where(['canceled' => 'Y'])->count(),
        ];
    }

    protected function buildPreviewPayload(int $docentry, bool $autoPrint = false): array
    {
        return [
            'config' => $this->buildViewConfig(),
            'document' => $this->loadDocumentData($docentry),
            'autoPrint' => $autoPrint,
            'renderMode' => 'html',
        ];
    }

    protected function renderPdfContent(array $document): string
    {
        $dompdf = new Dompdf([
            'isRemoteEnabled' => true,
        ]);

        $html = $this->renderPartial('preview', [
            'config' => $this->buildViewConfig(),
            'document' => $document,
            'autoPrint' => false,
            'renderMode' => 'pdf',
        ]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    protected function buildDocumentFilename(array $document): string
    {
        $config = $this->documentConfig();
        $prefix = (string) ($config['code'] ?? 'DOC');
        $docnum = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($document['docnum'] ?? 'sin-folio'));

        return strtolower($prefix . '-' . trim((string) $docnum, '-'));
    }

    protected function defaultMailSubject(array $document): string
    {
        $config = $this->documentConfig();

        return ($config['titleSingular'] ?? 'Documento') . ' ' . ($document['docnum'] ?? '');
    }

    protected function defaultMailMessage(array $document): string
    {
        $config = $this->documentConfig();

        return 'Se adjunta el PDF del ' . mb_strtolower((string) ($config['titleSingular'] ?? 'documento')) . ' ' . ($document['docnum'] ?? '') . '.';
    }

    protected function sendDocumentMail(array $document, array $payload): array
    {
        $email = trim((string) ($payload['email'] ?? ''));
        $subject = trim((string) ($payload['subject'] ?? $this->defaultMailSubject($document)));
        $message = trim((string) ($payload['message'] ?? $this->defaultMailMessage($document)));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['Success' => 'Error', 'Msg' => 'Debe indicar un correo valido.', 'Data' => []];
        }

        $pdfContent = $this->renderPdfContent($document);
        $filename = $this->buildDocumentFilename($document) . '.pdf';
        $params = Yii::$app->params;
        $fromEmail = (string) ($params['senderEmail'] ?? 'noreply@example.com');
        $fromName = (string) ($params['senderName'] ?? 'Sistema');

        /** @var MailerInterface $mailer */
        $mailer = Yii::$container->get(MailerInterface::class);
        $sent = $mailer->compose()
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
            return ['Success' => 'Error', 'Msg' => 'No fue posible enviar el correo.', 'Data' => []];
        }

        return ['Success' => 'Ok', 'Msg' => 'Correo enviado correctamente.', 'Data' => []];
    }

    protected function permissionName(string $action): string
    {
        $config = $this->documentConfig();

        return (string) ($config['permissionPrefix'] ?? '') . '.' . $action;
    }

    protected function requirePermission(string $action): void
    {
        if (!Yii::$app->user->can($this->permissionName($action))) {
            throw new ForbiddenHttpException('Acceso denegado.');
        }
    }

    protected function requireAnyPermission(array $actions): void
    {
        foreach ($actions as $action) {
            if (Yii::$app->user->can($this->permissionName($action))) {
                return;
            }
        }

        throw new ForbiddenHttpException('Acceso denegado.');
    }

    protected function asJsonResponse(?string $permissionAction, callable $callback, array $anyPermissionActions = []): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            // if ($permissionAction !== null) {
            //     $this->requirePermission($permissionAction);
            // } elseif ($anyPermissionActions !== []) {
            //     $this->requireAnyPermission($anyPermissionActions);
            // }

            return $this->asJson($callback());
        } catch (ForbiddenHttpException $exception) {
            return $this->asJson(['Success' => 'Error', 'Msg' => 'Sin permiso', 'Data' => []]);
        } catch (\Throwable $exception) {
            return $this->asJson([
                'Success' => 'Error',
                'Msg' => YII_DEBUG ? $exception->getMessage() : 'Ocurrio un error al procesar la solicitud.',
                'Data' => [],
            ]);
        }
    }
}