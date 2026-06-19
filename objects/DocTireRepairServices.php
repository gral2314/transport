<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\Bp;
use app\models\tables\DocTireRepair;
use app\models\tables\DocTireRepairAttach;
use app\models\tables\DocTireRepairDetail;
use app\models\tables\Series;
use app\models\tables\Tire;
use app\models\tables\TireBrand;
use app\models\tables\TireModel;
use app\models\tables\TireSize;
use app\models\tables\Employee;
use app\models\system\User;
use yii\db\Expression;
use yii\db\Transaction;

class DocTireRepairServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = DocTireRepair::find()->alias('r')
                ->select([
                    'r.*',
                    'bp.cardname AS provider_name',
                    'COUNT(rd.id) AS detail_count',
                ])
                ->leftJoin('bp', 'bp.cardcode = r.provider_code')
                ->leftJoin('doc_tire_repair_detail rd', 'rd.docentry = r.docentry')
                ->groupBy(['r.docentry']);

            if (!empty($filters['status'])) {
                $query->andWhere(['r.status' => $filters['status']]);
            }
            if (!empty($filters['doc_status'])) {
                $query->andWhere(['r.doc_status' => $filters['doc_status']]);
            }
            if (!empty($filters['canceled'])) {
                $query->andWhere(['r.canceled' => $filters['canceled']]);
            }
            if (!empty($filters['provider_code'])) {
                $query->andWhere(['r.provider_code' => $filters['provider_code']]);
            }
            if (!empty($filters['search'])) {
                $query->andWhere(['or',
                    ['like', 'r.docnum', $filters['search']],
                    ['like', 'r.comments', $filters['search']],
                    ['like', 'bp.cardname', $filters['search']],
                ]);
            }

            // Paginación
            $page = max(1, (int) ($filters['page'] ?? 1));
            $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 10)));
            $totalCount = (int) $query->count();

            $query->orderBy(['r.docentry' => SORT_DESC])
                ->limit($perPage)
                ->offset(($page - 1) * $perPage);

            return [
                'Success' => 'Ok',
                'Msg' => '',
                'Data' => [
                    'items' => $query->asArray()->all(),
                    'pagination' => [
                        'page' => $page,
                        'perPage' => $perPage,
                        'totalCount' => $totalCount,
                        'totalPages' => (int) ceil($totalCount / $perPage),
                    ],
                ],
            ];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al listar documentos de mantenimiento';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function get(int $docentry): array
    {
        $header = DocTireRepair::findOne(['docentry' => $docentry]);
        if ($header === null) {
            return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
        }

        $data = $header->toArray();
        $data['details'] = DocTireRepairDetail::find()
            ->alias('rd')
            ->select(['rd.*', 'tire.tire_name'])
            ->leftJoin('tire', 'tire.tire_code = rd.tire_code')
            ->where(['rd.docentry' => $docentry])
            ->orderBy(['rd.linenum' => SORT_ASC])
            ->asArray()
            ->all();
        $data['attachments'] = DocTireRepairAttach::find()
            ->where(['docentry' => $docentry])
            ->orderBy(['linenum' => SORT_ASC])
            ->asArray()
            ->all();

        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $data];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $data = $this->normalizePayload($data);

            $docentry = isset($data['docentry']) ? (int)$data['docentry'] : null;
            $header = ($docentry !== null) ? DocTireRepair::findOne(['docentry' => $docentry]) : null;

            if ($header !== null && $this->isImmutable($header)) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Documento cerrado/cancelado. No se permite editar.', 'Data' => []];
            }

            if ($header === null) {
                $header = new DocTireRepair();
                if (empty($data['docnum'])) {
                    if (!empty($data['series_id'])) {
                        $seriesServices = new SeriesServices();
                        $seriesResult = $seriesServices->getNextNumber('DocTireRepair', (int)$data['series_id']);
                        if (($seriesResult['Success'] ?? '') !== 'Ok') {
                            if ($ownTx) {
                                $tx->rollBack();
                            }
                            return $seriesResult;
                        }
                        $data['docnum'] = $seriesResult['Data']['docNum'];
                        $data['series_id'] = $seriesResult['Data']['seriesId'];
                    } else {
                        $data['docnum'] = $this->generateDocNum('MNT');
                    }
                }
            }

            $details = $data['details'] ?? null;
            $attachments = $data['attachments'] ?? null;
            unset($data['details'], $data['attachments']);

            $header->setAttributes($data);
            if (!$header->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            if (is_array($details)) {
                $syncDetails = $this->syncDetails((int)$header->docentry, $details);
                if (($syncDetails['Success'] ?? '') !== 'Ok') {
                    if ($ownTx) {
                        $tx->rollBack();
                    }
                    return $syncDetails;
                }
            }

            if (is_array($attachments)) {
                $syncAttach = $this->syncAttachments((int)$header->docentry, $attachments);
                if (($syncAttach['Success'] ?? '') !== 'Ok') {
                    if ($ownTx) {
                        $tx->rollBack();
                    }
                    return $syncAttach;
                }
            }

            if ($ownTx) {
                $tx->commit();
            }

            return $this->get((int)$header->docentry);
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al guardar documento de mantenimiento';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function close(int $docentry, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $header = DocTireRepair::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->status !== DocTireRepair::STATUS_VAL) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Solo documentos en estado Validado pueden cerrarse', 'Data' => []];
            }

            $header->status = DocTireRepair::STATUS_CLOSE;

            if (!$header->save()) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            if ($ownTx) { $tx->commit(); }
            return ['Success' => 'Ok', 'Msg' => 'Documento cerrado correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) { $tx->rollBack(); }
            $msg = 'Error al cerrar documento';
            if (YII_DEBUG) { $msg .= ': ' . $e->getMessage(); }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // Flujo de trazabilidad temporal (Taller)
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Libera una orden de mantenimiento: PLAN → LIB
     * Asigna el técnico responsable y registra released_at.
     */
    public function release(int $docentry, int $technicianUserId, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $header = DocTireRepair::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->status !== DocTireRepair::STATUS_PLAN) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Solo ordenes en estado Planeado pueden liberarse', 'Data' => []];
            }

            $header->status = DocTireRepair::STATUS_LIB;
            //$header->technician_user_id = $technicianUserId;
            $header->released_at = date('Y-m-d H:i:s');

            if (!$header->save()) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            if ($ownTx) { $tx->commit(); }
            return ['Success' => 'Ok', 'Msg' => 'Orden liberada correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) { $tx->rollBack(); }
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al liberar orden';
            if (YII_DEBUG) { $msg .= ': ' . $e->getMessage(); }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    /**
     * Inicia la ejecución de una orden: LIB → TALLER
     * Registra started_at.
     */
    public function start(int $docentry, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $header = DocTireRepair::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->status !== DocTireRepair::STATUS_LIB) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Solo ordenes en estado Liberado pueden iniciarse', 'Data' => []];
            }

            $header->status = DocTireRepair::STATUS_TALLER;
            $header->started_at = date('Y-m-d H:i:s');

            if (!$header->save()) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            if ($ownTx) { $tx->commit(); }
            return ['Success' => 'Ok', 'Msg' => 'Orden iniciada correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) { $tx->rollBack(); }
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al iniciar orden';
            if (YII_DEBUG) { $msg .= ': ' . $e->getMessage(); }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    /**
     * Finaliza la ejecución de una orden: TALLER → EXEC
     * Registra completed_at.
     */
    public function execute(int $docentry, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $header = DocTireRepair::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->status !== DocTireRepair::STATUS_TALLER) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Solo ordenes en estado Taller pueden ejecutarse', 'Data' => []];
            }

            $header->status = DocTireRepair::STATUS_EXEC;
            $header->completed_at = date('Y-m-d H:i:s');

            if (!$header->save()) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            if ($ownTx) { $tx->commit(); }
            return ['Success' => 'Ok', 'Msg' => 'Orden ejecutada correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) { $tx->rollBack(); }
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al ejecutar orden';
            if (YII_DEBUG) { $msg .= ': ' . $e->getMessage(); }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    /**
     * Valida una orden ejecutada: EXEC → VAL
     * Registra validated_at, validated_by_user_id, cierra documento y ejecuta integración.
     */
    public function validate(int $docentry, int $validatedByUserId, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $header = DocTireRepair::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->status !== DocTireRepair::STATUS_EXEC) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Solo ordenes en estado Ejecutado pueden validarse', 'Data' => []];
            }

            $header->status = DocTireRepair::STATUS_VAL;
            $header->doc_status = DocTireRepair::DOC_STATUS_C;
            $header->validated_by_user_id = $validatedByUserId;
            $header->validated_at = date('Y-m-d H:i:s');
            $header->rejection_notes = null; // Limpiar notas de rechazo previas

            if (!$header->save()) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            // ── Integración: aplicar cambios a llantas ──
            $integrationService = new TireIntegrationService();
            $result = $integrationService->onRepairClosed($docentry, $tx);
            if ($result['Success'] !== 'Ok') {
                if ($ownTx) { $tx->rollBack(); }
                return $result;
            }

            if ($ownTx) { $tx->commit(); }
            return ['Success' => 'Ok', 'Msg' => 'Orden validada correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) { $tx->rollBack(); }
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al validar orden';
            if (YII_DEBUG) { $msg .= ': ' . $e->getMessage(); }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    /**
     * Rechaza una orden validada, regresándola a Taller con notas de rechazo.
     */
    public function reject(int $docentry, int $rejectedByUserId, string $rejectionNotes, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $header = DocTireRepair::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->status !== DocTireRepair::STATUS_VAL) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Solo ordenes en estado Validado pueden rechazarse', 'Data' => []];
            }

            $header->status = DocTireRepair::STATUS_TALLER;
            $header->rejection_notes = $rejectionNotes;
            $header->validated_by_user_id = $rejectedByUserId; // Registramos quién rechazó
            $header->validated_at = date('Y-m-d H:i:s'); // Marcamos como "revisado"

            if (!$header->save()) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            if ($ownTx) { $tx->commit(); }
            return ['Success' => 'Ok', 'Msg' => 'Orden rechazada, regresada a Taller', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) { $tx->rollBack(); }
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al rechazar orden';
            if (YII_DEBUG) { $msg .= ': ' . $e->getMessage(); }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function cancel(int $docentry, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $header = DocTireRepair::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->canceled === DocTireRepair::CANCELED_Y) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'El documento ya esta cancelado', 'Data' => []];
            }

            $header->canceled = DocTireRepair::CANCELED_Y;
            $header->doc_status = DocTireRepair::DOC_STATUS_C;
            $header->status = DocTireRepair::STATUS_CANCELLED;
            $header->cancelled_at = date('Y-m-d H:i:s');

            if (!$header->save()) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            if ($ownTx) { $tx->commit(); }
            return ['Success' => 'Ok', 'Msg' => 'Documento cancelado correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) { $tx->rollBack(); }
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al cancelar documento';
            if (YII_DEBUG) { $msg .= ': ' . $e->getMessage(); }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function delete(int $docentry, ?Transaction $transaction = null): array
    {
        return [
            'Success' => 'Error',
            'Msg' => 'No se permite eliminar folios por auditoria. Use cancelacion logica.',
            'Data' => [],
        ];
    }

    public function getFormOptions(): array
    {
        return [
            'Success' => 'Ok',
            'Msg' => '',
            'Data' => [
                'series_options' => Series::find()
                    ->select(['id AS code', 'name AS name'])
                    ->where(['object_name' => 'DocTireRepair'])
                    ->orderBy(['name' => SORT_ASC])
                    ->asArray()
                    ->all(),
                'provider_options' => Bp::find()
                    ->select(['cardcode AS code', 'cardname AS name'])
                    ->orderBy(['cardname' => SORT_ASC])
                    ->asArray()
                    ->all(),
                'tire_options' => Tire::find()
                    ->select(['tire_code AS code', 'tire_name AS name'])
                    ->orderBy(['tire_code' => SORT_ASC])
                    ->asArray()
                    ->all(),
                'technician_options' => Employee::find()
                    ->select(['employee_code AS code', new \yii\db\Expression('concat(first_name, " ", last_name, " ",second_last_name) AS name')])
                    ->orderBy(['first_name' => SORT_ASC, 'last_name' => SORT_ASC, 'second_last_name' => SORT_ASC])
                    ->asArray()
                    ->all(),
                'repair_type_options' => [
                    DocTireRepairDetail::REPAIR_TYPE_PUNCTURE => 'Ponchadura',
                    DocTireRepairDetail::REPAIR_TYPE_PATCH => 'Parche',
                    DocTireRepairDetail::REPAIR_TYPE_RETREAD => 'Renovado',
                    DocTireRepairDetail::REPAIR_TYPE_BALANCE => 'Balanceo',
                    DocTireRepairDetail::REPAIR_TYPE_ALIGNMENT => 'Alineacion',
                    DocTireRepairDetail::REPAIR_TYPE_ROTATION => 'Rotacion',
                    DocTireRepairDetail::REPAIR_TYPE_OTHER => 'Otro',
                ],
            ],
        ];
    }

    /**
     * Obtiene eventos para el calendario FullCalendar de mantenimientos
     */
    public function getCalendarEvents(array $params = []): array
    {
        try {
            $query = DocTireRepair::find()->alias('r')
                ->select([
                    'r.docentry',
                    'r.docnum',
                    'r.doc_date',
                    'r.return_date',
                    'r.status',
                    'r.comments',
                    'r.provider_code',
                    'bp.cardname AS provider_name',
                ])
                ->leftJoin('bp', 'bp.cardcode = r.provider_code')
                ->andWhere(['r.canceled' => 'N']);

            // Filtro opcional por proveedor
            if (!empty($params['provider_code'])) {
                $query->andWhere(['r.provider_code' => $params['provider_code']]);
            }

            $rows = $query->asArray()->all();

            $statusColors = [
                DocTireRepair::STATUS_PLAN      => '#6c757d',   // gray
                DocTireRepair::STATUS_LIB       => '#17a2b8',   // cyan
                DocTireRepair::STATUS_TALLER    => '#fd7e14',   // orange
                DocTireRepair::STATUS_EXEC      => '#007bff',   // blue
                DocTireRepair::STATUS_VAL       => '#ffc107',   // yellow
                DocTireRepair::STATUS_CLOSE     => '#28a745',   // green
                DocTireRepair::STATUS_CANCELLED => '#dc3545',   // red
            ];

            $statusLabels = [
                DocTireRepair::STATUS_PLAN      => 'Planeado',
                DocTireRepair::STATUS_LIB       => 'Liberado',
                DocTireRepair::STATUS_TALLER    => 'Taller',
                DocTireRepair::STATUS_EXEC      => 'Ejecutado',
                DocTireRepair::STATUS_VAL       => 'Validado',
                DocTireRepair::STATUS_CLOSE     => 'Cerrado',
                DocTireRepair::STATUS_CANCELLED => 'Cancelado',
            ];

            $events = [];
            foreach ($rows as $row) {
                $status = $row['status'] ?? DocTireRepair::STATUS_PLAN;
                $color = $statusColors[$status] ?? '#6c757d';
                $label = $statusLabels[$status] ?? $status;

                $title = sprintf(
                    '[%s] %s - %s',
                    $row['docnum'],
                    $label,
                    $row['provider_name'] ?? 'Sin proveedor'
                );

                $events[] = [
                    'id' => (string) $row['docentry'],
                    'title' => $title,
                    'start' => $row['return_date'] ?: $row['doc_date'],
                    'end' => $row['return_date'] ?: $row['doc_date'],
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'docentry' => (int) $row['docentry'],
                        'docnum' => $row['docnum'],
                        'status' => $status,
                        'statusLabel' => $label,
                        'provider' => $row['provider_name'] ?? 'Sin proveedor',
                        'comments' => $row['comments'] ?? '',
                        'doc_date' => $row['doc_date'],
                        'return_date' => $row['return_date'],
                    ],
                ];
            }

            return [
                'Success' => 'Ok',
                'Msg' => '',
                'Data' => $events,
            ];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al obtener eventos del calendario';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    /**
     * Actualiza fecha de un documento (drag & drop en calendario)
     */
    public function updateDate(array $data): array
    {
        try {
            $docentry = (int) ($data['docentry'] ?? 0);
            $repairDate = $data['repair_date'] ?? '';
            $returnDate = $data['return_date'] ?? '';

            if ($docentry <= 0 || empty($repairDate)) {
                return ['Success' => 'Error', 'Msg' => 'Datos insuficientes para actualizar fecha', 'Data' => []];
            }

            $header = DocTireRepair::findOne(['docentry' => $docentry]);
            if ($header === null) {
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->canceled === DocTireRepair::CANCELED_Y) {
                return ['Success' => 'Error', 'Msg' => 'No se puede modificar un documento cancelado', 'Data' => []];
            }

            $header->repair_date = $repairDate;
            $header->return_date = $returnDate ?: null;
            if (!$header->save()) {
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            return ['Success' => 'Ok', 'Msg' => 'Fechas actualizadas correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al actualizar fechas';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    /**
     * Obtiene llantas disponibles para mantenimiento (no dadas de baja ni asignadas a otro taller activo).
     */
    public function getAvailableTires(array $params = []): array
    {
        try {
            $locMap = [
                Tire::LOC_WH => 'Almacén',
                Tire::LOC_VH => 'En Vehículo',
                Tire::LOC_WS => 'Taller',
                Tire::LOC_SC => 'Desecho',
                Tire::LOC_SP => 'En Reencauche',
            ];
            $locCase = 'CASE t.location_status ';
            foreach ($locMap as $k => $v) {
                $locCase .= "WHEN '{$k}' THEN '{$v}' ";
            }
            $locCase .= "ELSE t.location_status END";

            $query = Tire::find()->alias('t')
                ->select([
                    't.tire_code',
                    't.tire_name AS name',
                    't.current_km AS tire_km',
                    't.curr_tread_depth AS tread_depth',
                    new Expression("{$locCase} AS location"),
                    'sz.name AS tire_size',
                    'b.name AS tire_brand',
                    'm.name AS series_name',
                ])
                ->leftJoin(['sz' => TireSize::tableName()], 'sz.code = t.size_code')
                ->leftJoin(['b' => TireBrand::tableName()], 'b.code = t.brand_code')
                ->leftJoin(['m' => TireModel::tableName()], 'm.code = t.model_code')
                ->andWhere(['t.operational_status' => Tire::OP_STATUS_AV])
                ->orderBy(['t.tire_code' => SORT_ASC]);

            if (!empty($params['search'])) {
                $query->andWhere(['or',
                    ['like', 't.tire_code', $params['search']],
                    ['like', 't.tire_name', $params['search']],
                    ['like', 'sz.name', $params['search']],
                    ['like', 'b.name', $params['search']],
                    ['like', 'm.name', $params['search']],
                ]);
            }

            // Filtro por serie (modelo)
            if (!empty($params['series_id'])) {
                $query->andWhere(['m.code' => $params['series_id']]);
            }

            $items = $query->asArray()->all();

            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $items];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al obtener llantas disponibles';
            if (YII_DEBUG) { $msg .= ': ' . $e->getMessage(); }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    /**
     * Obtiene el siguiente número de documento para una serie.
     */
    public function getNextDocnum(int $seriesId): array
    {
        try {
            $seriesServices = new SeriesServices();
            $result = $seriesServices->peekNextNumber('DocTireRepair', $seriesId);
            if (($result['Success'] ?? '') !== 'Ok') {
                return $result;
            }
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => [
                'docnum' => $result['Data']['docNum'],
                'series_id' => $result['Data']['seriesId'],
            ]];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al obtener siguiente folio';
            if (YII_DEBUG) { $msg .= ': ' . $e->getMessage(); }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    private function syncDetails(int $docentry, array $details): array
    {
        DocTireRepairDetail::deleteAll(['docentry' => $docentry]);

        $lineNum = 1;
        foreach ($details as $row) {
            if (!is_array($row)) {
                continue;
            }

            $row = $this->normalizePayload($row);
            $model = new DocTireRepairDetail();
            $row['docentry'] = $docentry;
            $row['linenum'] = isset($row['linenum']) ? (int)$row['linenum'] : $lineNum;
            $model->setAttributes($row);

            if (!$model->save()) {
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($model), 'Data' => []];
            }

            $lineNum++;
        }

        return ['Success' => 'Ok', 'Msg' => '', 'Data' => []];
    }

    private function syncAttachments(int $docentry, array $attachments): array
    {
        DocTireRepairAttach::deleteAll(['docentry' => $docentry]);

        $detailLines = DocTireRepairDetail::find()
            ->select('linenum')
            ->where(['docentry' => $docentry])
            ->column();
        $validLines = array_map('intval', $detailLines);

        $basePath = \Yii::getAlias('@webroot') . '/public/docs-tire-repair';
        if (!is_dir($basePath)) {
            @mkdir($basePath, 0755, true);
        }

        $lineNum = 1;
        foreach ($attachments as $row) {
            if (!is_array($row)) {
                continue;
            }

            $row = $this->normalizePayload($row);
            $uploadedFile = $row['_uploaded_file'] ?? null;
            unset($row['_uploaded_file']);

            $model = new DocTireRepairAttach();
            $row['docentry'] = $docentry;
            $row['linenum'] = isset($row['linenum']) ? (int)$row['linenum'] : $lineNum;

            if (!in_array((int)$row['linenum'], $validLines, true)) {
                return [
                    'Success' => 'Error',
                    'Msg' => 'Cada adjunto debe apuntar a una linea de detalle valida',
                    'Data' => [],
                ];
            }

            // Guardar archivo físico si se subió
            if ($uploadedFile instanceof \yii\web\UploadedFile) {
                $timestamp = time();
                $ext = $uploadedFile->getExtension() ?: pathinfo($uploadedFile->name, PATHINFO_EXTENSION);
                $storedName = $docentry . '_' . $row['linenum'] . '_' . $timestamp . '.' . $ext;
                $storedPath = $basePath . '/' . $storedName;

                if (!$uploadedFile->saveAs($storedPath)) {
                    return [
                        'Success' => 'Error',
                        'Msg' => 'Error al guardar archivo en disco: ' . $storedName,
                        'Data' => [],
                    ];
                }

                $row['filename'] = $uploadedFile->name;
                $row['filepath'] = 'public/docs-tire-repair/' . $storedName;
            }

            $model->setAttributes($row);
            if (!$model->save()) {
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($model), 'Data' => []];
            }

            $lineNum++;
        }

        return ['Success' => 'Ok', 'Msg' => '', 'Data' => []];
    }

    private function isImmutable(DocTireRepair $header): bool
    {
        return ($header->doc_status === DocTireRepair::DOC_STATUS_C)
            || ($header->canceled === DocTireRepair::CANCELED_Y)
            || ($header->status === DocTireRepair::STATUS_CLOSE)
            || ($header->status === DocTireRepair::STATUS_CANCELLED);
    }

    private function normalizePayload(array $data): array
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = $this->normalizePayload($v);
                continue;
            }
            if (is_string($v) && trim($v) === '') {
                $data[$k] = null;
            }
        }
        return $data;
    }

    private function modelErrors($model): string
    {
        $errors = [];
        foreach ($model->getErrors() as $messages) {
            $errors = array_merge($errors, $messages);
        }
        return implode('; ', $errors);
    }

    private function generateDocNum(string $prefix): string
    {
        return $prefix . '-' . date('Ymd-His') . '-' . substr((string)microtime(true), -4);
    }
}
