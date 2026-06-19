<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\DocTireMovement;
use app\models\tables\DocTireMovementAttach;
use app\models\tables\DocTireMovementDetail;
use app\models\tables\DocTireMovementVehicle;
use app\models\tables\Series;
use app\models\tables\Tire;
use app\models\tables\Vehicle;
use app\models\tables\Warehouse;
use yii\db\Transaction;

class DocTireMovementServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = DocTireMovement::find()->alias('m')
                ->select([
                    'm.*',
                    'COUNT(md.id) AS detail_count',
                    'COUNT(mv.id) AS vehicle_count',
                    'u.username AS technician_username',
                ])
                ->leftJoin('doc_tire_movement_detail md', 'md.docentry = m.docentry')
                ->leftJoin('doc_tire_movement_vehicle mv', 'mv.docentry = m.docentry')
                ->leftJoin('{{%users}} u', 'u.id = m.technician_user_id')
                ->groupBy(['m.docentry']);

            if (!empty($filters['status'])) {
                $query->andWhere(['m.status' => $filters['status']]);
            }
            // Limitar por días hacia atrás (para órdenes cerradas/canceladas en taller)
            if (!empty($filters['days_back'])) {
                $days = (int) $filters['days_back'];
                $query->andWhere(['>=', 'm.doc_date', date('Y-m-d', strtotime("-{$days} days"))]);
            }
            if (!empty($filters['doc_status'])) {
                $query->andWhere(['m.doc_status' => $filters['doc_status']]);
            }
            if (!empty($filters['canceled'])) {
                $query->andWhere(['m.canceled' => $filters['canceled']]);
            }
            if (!empty($filters['origin_type'])) {
                $query->andWhere(['m.origin_type' => $filters['origin_type']]);
            }
            if (!empty($filters['priority'])) {
                $query->andWhere(['m.priority' => $filters['priority']]);
            }
            if (!empty($filters['technician_user_id'])) {
                $query->andWhere(['m.technician_user_id' => (int) $filters['technician_user_id']]);
            }
            if (!empty($filters['search'])) {
                $query->andWhere(['or',
                    ['like', 'm.docnum', $filters['search']],
                    ['like', 'm.comments', $filters['search']],
                ]);
            }

            // Paginación
            $page = max(1, (int) ($filters['page'] ?? 1));
            $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 10)));
            $totalCount = (int) $query->count();

            $query->orderBy(['m.docentry' => SORT_DESC])
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
            $msg = 'Error al listar documentos de asignacion';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function get(int $docentry): array
    {
        $header = DocTireMovement::findOne(['docentry' => $docentry]);
        if ($header === null) {
            return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
        }

        $data = $header->toArray([], ['technicianUser']);
        $data['technicianUser'] = $header->technicianUser ? $header->technicianUser->toArray() : null;
        $data['vehicles'] = DocTireMovementVehicle::find()
            ->alias('mv')
            ->select(['mv.*', 'v.vehicle_name'])
            ->leftJoin('vehicle v', 'v.vehicle_code = mv.vehicle_code')
            ->where(['mv.docentry' => $docentry])
            ->orderBy(['mv.linenum' => SORT_ASC])
            ->asArray()
            ->all();

        $data['details'] = DocTireMovementDetail::find()
            ->alias('md')
            ->select([
                'md.*',
                't1.tire_name AS tire_name',
                't2.tire_name AS related_tire_name',
                'vf.vehicle_name AS vehicle_from_name',
                'vt.vehicle_name AS vehicle_to_name',
            ])
            ->leftJoin('tire t1', 't1.tire_code = md.tire_code')
            ->leftJoin('tire t2', 't2.tire_code = md.related_tire_code')
            ->leftJoin('vehicle vf', 'vf.vehicle_code = md.vehicle_code_from')
            ->leftJoin('vehicle vt', 'vt.vehicle_code = md.vehicle_code_to')
            ->where(['md.docentry' => $docentry])
            ->orderBy(['md.linenum' => SORT_ASC])
            ->asArray()
            ->all();

        $data['attachments'] = DocTireMovementAttach::find()
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
            $header = ($docentry !== null) ? DocTireMovement::findOne(['docentry' => $docentry]) : null;

            if ($header !== null && $this->isImmutable($header)) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Documento cerrado/cancelado. No se permite editar.', 'Data' => []];
            }

            if ($header === null) {
                $header = new DocTireMovement();

                // Determinar la serie a usar: la enviada desde el frontend, o la default
                $seriesId = !empty($data['series_id']) ? (int)$data['series_id'] : Series::getDefaultId('DocTireMovement');

                if ($seriesId === null) {
                    if ($ownTx) {
                        $tx->rollBack();
                    }
                    return ['Success' => 'Error', 'Msg' => 'No hay una serie configurada para documentos de asignacion. Configure una serie en el catalogo.', 'Data' => []];
                }

                $seriesServices = new SeriesServices();
                $seriesResult = $seriesServices->getNextNumber('DocTireMovement', $seriesId);
                if (($seriesResult['Success'] ?? '') !== 'Ok') {
                    if ($ownTx) {
                        $tx->rollBack();
                    }
                    return $seriesResult;
                }
                $data['docnum'] = $seriesResult['Data']['docNum'];
                $data['series_id'] = $seriesResult['Data']['seriesId'];
            }

            $vehicles = $data['vehicles'] ?? null;
            $details = $data['details'] ?? null;
            $attachments = $data['attachments'] ?? null;
            unset($data['vehicles'], $data['details'], $data['attachments']);

            $header->setAttributes($data);
            if (!$header->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            if (is_array($vehicles)) {
                $syncVehicles = $this->syncVehicles((int)$header->docentry, $vehicles);
                if (($syncVehicles['Success'] ?? '') !== 'Ok') {
                    if ($ownTx) {
                        $tx->rollBack();
                    }
                    return $syncVehicles;
                }
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
            $msg = 'Error al guardar documento de asignacion';
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
            $header = DocTireMovement::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->canceled === DocTireMovement::CANCELED_Y) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'No se puede cerrar un documento cancelado', 'Data' => []];
            }

            $detailCount = (int)DocTireMovementDetail::find()->where(['docentry' => $docentry])->count();
            if ($detailCount <= 0) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Debe capturar al menos una linea de detalle para cerrar', 'Data' => []];
            }

            $vehicleCount = (int)DocTireMovementVehicle::find()->where(['docentry' => $docentry])->count();
            if ($vehicleCount <= 0) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Debe capturar al menos una unidad involucrada para cerrar', 'Data' => []];
            }

            $header->doc_status = DocTireMovement::DOC_STATUS_C;
            $header->status = DocTireMovement::STATUS_CLOSED;
            $header->completed_at = date('Y-m-d H:i:s');

            if (!$header->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            // ── Integración: aplicar cambios a llantas y vehículos ──
            $integrationService = new TireIntegrationService();
            $result = $integrationService->onMovementClosed($docentry, $tx);
            if ($result['Success'] !== 'Ok') {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return $result;
            }

            if ($ownTx) {
                $tx->commit();
            }

            return ['Success' => 'Ok', 'Msg' => 'Documento cerrado correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error al cerrar documento';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function cancel(int $docentry, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $header = DocTireMovement::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->canceled === DocTireMovement::CANCELED_Y) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'El documento ya esta cancelado', 'Data' => []];
            }

            $header->canceled = DocTireMovement::CANCELED_Y;
            $header->doc_status = DocTireMovement::DOC_STATUS_C;
            $header->status = DocTireMovement::STATUS_CANCELLED;
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

    // ═══════════════════════════════════════════════════════════════════════
    // Flujo de trazabilidad temporal (Taller)
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Libera una orden de taller: PLANNNED → RELEASED
     * Asigna el técnico responsable y registra released_at.
     */
    public function release(int $docentry, int $technicianUserId, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $header = DocTireMovement::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->status !== DocTireMovement::STATUS_PLANNED) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Solo ordenes en estado planeado pueden liberarse', 'Data' => []];
            }

            $header->status = DocTireMovement::STATUS_RELEASED;
            $header->technician_user_id = $technicianUserId;
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
     * Inicia la ejecución de una orden: RELEASED → IN_PROGRESS
     * Registra started_at.
     */
    public function start(int $docentry, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $header = DocTireMovement::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->status !== DocTireMovement::STATUS_RELEASED) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Solo ordenes en estado liberado pueden iniciarse', 'Data' => []];
            }

            $header->status = DocTireMovement::STATUS_IN_PROGRESS;
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
     * Finaliza la ejecución de una orden: IN_PROGRESS → PENDING_VALIDATION
     * Registra completed_at y odometer_final.
     *
     * @param int $docentry ID del documento
     * @param int|null $odometer Odómetro final (km) ingresado por el técnico
     * @param Transaction|null $transaction Transacción externa opcional
     */
    public function complete(int $docentry, ?int $odometer = null, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $header = DocTireMovement::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->status !== DocTireMovement::STATUS_IN_PROGRESS) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Solo ordenes en estado en progreso pueden finalizarse', 'Data' => []];
            }

            $header->status = DocTireMovement::STATUS_PENDING_VALIDATION;
            $header->completed_at = date('Y-m-d H:i:s');

            if ($odometer !== null) {
                $header->odometer_final = $odometer;
            }

            if (!$header->save()) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            if ($ownTx) { $tx->commit(); }
            return ['Success' => 'Ok', 'Msg' => 'Orden finalizada, pendiente de validación', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) { $tx->rollBack(); }
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al finalizar orden';
            if (YII_DEBUG) { $msg .= ': ' . $e->getMessage(); }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    /**
     * Valida una orden completada: PENDING_VALIDATION → CLOSED
     * Registra validated_at, validated_by_user_id, limpia rejection_notes y ejecuta integración.
     */
    public function validate(int $docentry, int $validatedByUserId, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $header = DocTireMovement::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->status !== DocTireMovement::STATUS_PENDING_VALIDATION) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Solo ordenes en estado pendiente de validación pueden validarse', 'Data' => []];
            }

            $header->status = DocTireMovement::STATUS_CLOSED;
            $header->doc_status = DocTireMovement::DOC_STATUS_C;
            $header->validated_by_user_id = $validatedByUserId;
            $header->validated_at = date('Y-m-d H:i:s');
            $header->rejection_notes = null; // Limpiar notas de rechazo previas

            if (!$header->save()) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            // ── Integración: aplicar cambios a llantas y vehículos ──
            $integrationService = new TireIntegrationService();
            $result = $integrationService->onMovementClosed($docentry, $tx);
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
     * Rechaza una orden en estado pendiente de validación, regresándola a en progreso
     * con notas de rechazo del supervisor.
     */
    public function reject(int $docentry, int $rejectedByUserId, string $rejectionNotes, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $header = DocTireMovement::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->status !== DocTireMovement::STATUS_PENDING_VALIDATION) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => 'Solo ordenes en estado pendiente de validación pueden rechazarse', 'Data' => []];
            }

            $header->status = DocTireMovement::STATUS_IN_PROGRESS;
            $header->rejection_notes = $rejectionNotes;
            $header->validated_by_user_id = $rejectedByUserId; // Registramos quién rechazó
            $header->validated_at = date('Y-m-d H:i:s'); // Marcamos como "revisado"

            if (!$header->save()) {
                if ($ownTx) { $tx->rollBack(); }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            // No ejecutar integración (TireIntegrationService) al rechazar

            if ($ownTx) { $tx->commit(); }
            return ['Success' => 'Ok', 'Msg' => 'Orden rechazada, regresada a En Proceso', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) { $tx->rollBack(); }
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al rechazar orden';
            if (YII_DEBUG) { $msg .= ': ' . $e->getMessage(); }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    /**
     * Obtiene datos específicos para el modal de validación,
     * incluyendo comparativa Plan vs Ejecutado y evidencia.
     */
    public function getValidationData(int $docentry): array
    {
        try {
            $header = DocTireMovement::find()
                ->alias('m')
                ->select([
                    'm.*',
                    'assigned.username AS assigned_username',
                    'validated.username AS validated_username',
                ])
                ->leftJoin('users assigned', 'assigned.id = m.technician_user_id')
                ->leftJoin('users validated', 'validated.id = m.validated_by_user_id')
                ->where(['m.docentry' => $docentry])
                ->one();

            if ($header === null) {
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            $data = $header->toArray();
            $data['assigned_username'] = $header->getAttribute('assigned_username');
            $data['validated_username'] = $header->getAttribute('validated_username');

            // Timeline
            $data['timeline'] = [
                'released_at' => $header->released_at,
                'started_at' => $header->started_at,
                'completed_at' => $header->completed_at,
                'validated_at' => $header->validated_at,
            ];

            // Detalle comparativo: Plan vs Ejecutado
            $details = DocTireMovementDetail::find()
                ->alias('md')
                ->select([
                    'md.*',
                    't1.tire_name AS tire_name',
                    't2.tire_name AS related_tire_name',
                    'vf.vehicle_name AS vehicle_from_name',
                    'vt.vehicle_name AS vehicle_to_name',
                ])
                ->leftJoin('tire t1', 't1.tire_code = md.tire_code')
                ->leftJoin('tire t2', 't2.tire_code = md.related_tire_code')
                ->leftJoin('vehicle vf', 'vf.vehicle_code = md.vehicle_code_from')
                ->leftJoin('vehicle vt', 'vt.vehicle_code = md.vehicle_code_to')
                ->where(['md.docentry' => $docentry])
                ->orderBy(['md.linenum' => SORT_ASC])
                ->asArray()
                ->all();

            // Calcular desviaciones: líneas donde line_status = EXECUTED y hay deviation_notes
            $hasDeviations = false;
            foreach ($details as &$det) {
                $det['is_deviation'] = (
                    $det['line_status'] === DocTireMovementDetail::LINE_STATUS_EXECUTED
                    && !empty($det['deviation_notes'])
                );
                if ($det['is_deviation']) {
                    $hasDeviations = true;
                }
            }
            unset($det);

            $data['details'] = $details;
            $data['has_deviations'] = $hasDeviations;

            // Evidencia (attachments)
            $data['attachments'] = DocTireMovementAttach::find()
                ->where(['docentry' => $docentry])
                ->orderBy(['linenum' => SORT_ASC])
                ->asArray()
                ->all();

            // Vehículos
            $data['vehicles'] = DocTireMovementVehicle::find()
                ->alias('mv')
                ->select(['mv.*', 'v.vehicle_name'])
                ->leftJoin('vehicle v', 'v.vehicle_code = mv.vehicle_code')
                ->where(['mv.docentry' => $docentry])
                ->orderBy(['mv.linenum' => SORT_ASC])
                ->asArray()
                ->all();

            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $data];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al obtener datos de validacion';
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

    /**
     * Obtiene eventos para FullCalendar.
     * Filtra por technician_user_id si se proporciona.
     */
    public function getCalendarEvents(array $params = []): array
    {
        try {
            $query = DocTireMovement::find()->alias('m')
                ->select([
                    'm.docentry',
                    'm.docnum',
                    'm.doc_date',
                    'm.doc_duedate',
                    'm.status',
                    'm.priority',
                    'm.comments',
                    'm.technician_user_id',
                    "CONCAT(e.first_name, ' ', e.last_name) AS technician_name",
                ])
                ->leftJoin('employee e', 'e.employee_code = m.technician_user_id')
                ->andWhere(['m.canceled' => 'N']);

            // Filtro opcional por técnico
            if (!empty($params['technician_user_id'])) {
                $query->andWhere(['m.technician_user_id' => $params['technician_user_id']]);
            }

            $rows = $query->asArray()->all();

            $statusColors = [
                DocTireMovement::STATUS_PLANNED => '#6c757d',        // gray
                DocTireMovement::STATUS_RELEASED => '#007bff',       // blue
                DocTireMovement::STATUS_IN_PROGRESS => '#ffc107',    // yellow
                DocTireMovement::STATUS_PENDING_VALIDATION => '#17a2b8', // cyan
                DocTireMovement::STATUS_CLOSED => '#28a745',         // green
                DocTireMovement::STATUS_CANCELLED => '#dc3545',      // red
            ];

            $statusLabels = [
                DocTireMovement::STATUS_PLANNED => 'Planeado',
                DocTireMovement::STATUS_RELEASED => 'Liberado',
                DocTireMovement::STATUS_IN_PROGRESS => 'En Proceso',
                DocTireMovement::STATUS_PENDING_VALIDATION => 'Pendiente Validar',
                DocTireMovement::STATUS_CLOSED => 'Cerrado',
                DocTireMovement::STATUS_CANCELLED => 'Cancelado',
            ];

            $events = [];
            foreach ($rows as $row) {
                $status = $row['status'] ?? DocTireMovement::STATUS_PLANNED;
                $color = $statusColors[$status] ?? '#6c757d';
                $label = $statusLabels[$status] ?? $status;

                $title = sprintf(
                    '[%s] %s - %s',
                    $row['docnum'],
                    $label,
                    $row['technician_name'] ?? 'Sin técnico'
                );

                $events[] = [
                    'id' => (string) $row['docentry'],
                    'title' => $title,
                    'start' => $row['doc_date'],
                    'end' => $row['doc_duedate'],
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'docentry' => (int) $row['docentry'],
                        'docnum' => $row['docnum'],
                        'status' => $status,
                        'statusLabel' => $label,
                        'priority' => $row['priority'],
                        'technician' => $row['technician_name'] ?? 'Sin técnico',
                        'comments' => $row['comments'] ?? '',
                        'doc_date' => $row['doc_date'],
                        'doc_duedate' => $row['doc_duedate'],
                    ],
                ];
            }

            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $events];
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al obtener eventos', 'Data' => []];
        }
    }

    /**
     * Actualiza fechas de un documento (drag & drop).
     * Solo permite documentos en estado PLANNED.
     */
    public function updateDate(array $data): array
    {
        try {
            $docentry = (int) ($data['docentry'] ?? 0);
            if ($docentry <= 0) {
                return ['Success' => 'Error', 'Msg' => 'docentry inválido', 'Data' => []];
            }

            $model = DocTireMovement::findOne(['docentry' => $docentry]);
            if ($model === null) {
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            // Solo permitir drag en PLANNED
            if ($model->status !== DocTireMovement::STATUS_PLANNED) {
                return ['Success' => 'Error', 'Msg' => 'Solo documentos Planeados pueden ser re-agendados', 'Data' => []];
            }

            $startDate = $data['start_date'] ?? null;
            $endDate = $data['end_date'] ?? null;

            if ($startDate !== null) {
                $model->doc_date = $startDate;
            }
            if ($endDate !== null) {
                $model->doc_duedate = $endDate;
            }

            if (!$model->save()) {
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($model), 'Data' => []];
            }

            return ['Success' => 'Ok', 'Msg' => 'Fechas actualizadas correctamente', 'Data' => [
                'docentry' => $docentry,
                'doc_date' => $model->doc_date,
                'doc_duedate' => $model->doc_duedate,
            ]];
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? $e->getMessage() : 'Error al actualizar fecha', 'Data' => []];
        }
    }

    public function getFormOptions(): array
    {
        $employeeServices = new EmployeeServices();
        $mechanicsResult = $employeeServices->getMechanics();
        $mechanicOptions = [];
        if (($mechanicsResult['Success'] ?? '') === 'Ok') {
            $mechanicOptions = $mechanicsResult['Data'];
        }

        return [
            'Success' => 'Ok',
            'Msg' => '',
            'Data' => [
                'vehicle_options' => Vehicle::find()
                    ->select(['vehicle_code AS code', 'vehicle_name AS name'])
                    ->orderBy(['vehicle_code' => SORT_ASC])
                    ->asArray()
                    ->all(),
                'tire_options' => Tire::find()
                    ->select(['tire_code AS code', 'tire_name AS name'])
                    ->orderBy(['tire_code' => SORT_ASC])
                    ->asArray()
                    ->all(),
                'warehouse_options' => Warehouse::find()
                    ->select(['code', 'name'])
                    ->orderBy(['code' => SORT_ASC])
                    ->asArray()
                    ->all(),
                'movement_type_options' => [
                    DocTireMovementDetail::MOVEMENT_TYPE_ASSIGN => 'Asignacion',
                    DocTireMovementDetail::MOVEMENT_TYPE_ROTATE => 'Rotacion',
                    DocTireMovementDetail::MOVEMENT_TYPE_REMOVE => 'Retiro',
                    DocTireMovementDetail::MOVEMENT_TYPE_TRANSFER => 'Traslado',
                    DocTireMovementDetail::MOVEMENT_TYPE_REPAIR_SEND => 'Envio reparacion',
                    DocTireMovementDetail::MOVEMENT_TYPE_REPAIR_RETURN => 'Retorno reparacion',
                    DocTireMovementDetail::MOVEMENT_TYPE_SCRAP => 'Baja',
                ],
                'series_options' => Series::getActiveOptions('DocTireMovement'),
                'mechanic_options' => $mechanicOptions,
            ],
        ];
    }

    private function syncVehicles(int $docentry, array $vehicles): array
    {
        DocTireMovementVehicle::deleteAll(['docentry' => $docentry]);

        $lineNum = 1;
        foreach ($vehicles as $row) {
            if (!is_array($row)) {
                continue;
            }

            $row = $this->normalizePayload($row);
            $model = new DocTireMovementVehicle();
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

    private function syncDetails(int $docentry, array $details): array
    {
        DocTireMovementDetail::deleteAll(['docentry' => $docentry]);

        $lineNum = 1;
        foreach ($details as $row) {
            if (!is_array($row)) {
                continue;
            }

            $row = $this->normalizePayload($row);

            $businessError = $this->validateMovementDetailBusiness($row);
            if ($businessError !== null) {
                return ['Success' => 'Error', 'Msg' => $businessError, 'Data' => []];
            }

            $model = new DocTireMovementDetail();
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
        DocTireMovementAttach::deleteAll(['docentry' => $docentry]);

        $lineNum = 1;
        foreach ($attachments as $row) {
            if (!is_array($row)) {
                continue;
            }

            $row = $this->normalizePayload($row);
            $model = new DocTireMovementAttach();
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

    private function validateMovementDetailBusiness(array $row): ?string
    {
        $type = $row['movement_type'] ?? null;
        if ($type === null) {
            return 'movement_type es requerido en cada linea de detalle';
        }

        if (empty($row['tire_code'])) {
            return 'tire_code es requerido en cada linea de detalle';
        }

        if ($type === DocTireMovementDetail::MOVEMENT_TYPE_ROTATE && empty($row['related_tire_code'])) {
            return 'related_tire_code es requerido para movimientos ROTATE';
        }

        if ($type === DocTireMovementDetail::MOVEMENT_TYPE_ASSIGN) {
            if (empty($row['vehicle_code_to']) || empty($row['position_to'])) {
                return 'vehicle_code_to y position_to son requeridos para ASSIGN';
            }
        }

        if ($type === DocTireMovementDetail::MOVEMENT_TYPE_REMOVE) {
            if (empty($row['vehicle_code_from']) || empty($row['position_from'])) {
                return 'vehicle_code_from y position_from son requeridos para REMOVE';
            }
        }

        if ($type === DocTireMovementDetail::MOVEMENT_TYPE_TRANSFER) {
            if (empty($row['vehicle_code_from']) && empty($row['whs_code_from'])) {
                return 'TRANSFER requiere origen (vehicle_code_from o whs_code_from)';
            }
            if (empty($row['vehicle_code_to']) && empty($row['whs_code_to'])) {
                return 'TRANSFER requiere destino (vehicle_code_to o whs_code_to)';
            }
        }

        return null;
    }

    private function isImmutable(DocTireMovement $header): bool
    {
        return ($header->doc_status === DocTireMovement::DOC_STATUS_C)
            || ($header->canceled === DocTireMovement::CANCELED_Y)
            || ($header->status === DocTireMovement::STATUS_CLOSED)
            || ($header->status === DocTireMovement::STATUS_CANCELLED);
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

}
