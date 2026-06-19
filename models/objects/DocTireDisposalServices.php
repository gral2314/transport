<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\DocTireDisposal;
use app\models\tables\DocTireDisposalAttach;
use app\models\tables\DocTireDisposalDetail;
use app\models\tables\Series;
use app\models\tables\Tire;
use yii\db\Transaction;

class DocTireDisposalServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = DocTireDisposal::find()->alias('d')
                ->select([
                    'd.*',
                    'COUNT(dd.id) AS detail_count',
                ])
                ->leftJoin('doc_tire_disposal_detail dd', 'dd.docentry = d.docentry')
                ->groupBy(['d.docentry']);

            if (!empty($filters['status'])) {
                $query->andWhere(['d.status' => $filters['status']]);
            }
            if (!empty($filters['doc_status'])) {
                $query->andWhere(['d.doc_status' => $filters['doc_status']]);
            }
            if (!empty($filters['canceled'])) {
                $query->andWhere(['d.canceled' => $filters['canceled']]);
            }
            if (!empty($filters['date_from'])) {
                $query->andWhere(['>=', 'd.doc_date', $filters['date_from']]);
            }
            if (!empty($filters['date_to'])) {
                $query->andWhere(['<=', 'd.doc_date', $filters['date_to']]);
            }
            if (!empty($filters['search'])) {
                $query->andWhere(['or',
                    ['like', 'd.docnum', $filters['search']],
                    ['like', 'd.comments', $filters['search']],
                ]);
            }

            return [
                'Success' => 'Ok',
                'Msg' => '',
                'Data' => $query->orderBy(['d.docentry' => SORT_DESC])->asArray()->all(),
            ];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al listar documentos de baja';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function get(int $docentry): array
    {
        $header = DocTireDisposal::findOne(['docentry' => $docentry]);
        if ($header === null) {
            return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
        }

        $data = $header->toArray();
        $data['details'] = DocTireDisposalDetail::find()
            ->alias('dd')
            ->select(['dd.*', 'tire.tire_name'])
            ->leftJoin('tire', 'tire.tire_code = dd.tire_code')
            ->where(['dd.docentry' => $docentry])
            ->orderBy(['dd.linenum' => SORT_ASC])
            ->asArray()
            ->all();
        $data['attachments'] = DocTireDisposalAttach::find()
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
            $header = ($docentry !== null) ? DocTireDisposal::findOne(['docentry' => $docentry]) : null;

            if ($header !== null && $this->isImmutable($header)) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Documento cerrado/cancelado. No se permite editar.', 'Data' => []];
            }

            if ($header === null) {
                $header = new DocTireDisposal();
                if (empty($data['docnum'])) {
                    if (!empty($data['series_id'])) {
                        $seriesServices = new SeriesServices();
                        $seriesResult = $seriesServices->getNextNumber('DocTireDisposal', (int)$data['series_id']);
                        if (($seriesResult['Success'] ?? '') !== 'Ok') {
                            if ($ownTx) {
                                $tx->rollBack();
                            }
                            return $seriesResult;
                        }
                        $data['docnum'] = $seriesResult['Data']['docNum'];
                        $data['series_id'] = $seriesResult['Data']['seriesId'];
                    } else {
                        $data['docnum'] = $this->generateDocNum('DSP');
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
                // Validar que cada llanta esté disponible antes de guardar
                $validation = $this->validateTireAvailability($details, (int)$header->docentry);
                if (($validation['Success'] ?? '') !== 'Ok') {
                    if ($ownTx) {
                        $tx->rollBack();
                    }
                    return $validation;
                }

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
            $msg = 'Error al guardar documento de baja';
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
            $header = DocTireDisposal::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->canceled === DocTireDisposal::CANCELED_Y) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'No se puede cerrar un documento cancelado', 'Data' => []];
            }

            $detailCount = (int)DocTireDisposalDetail::find()->where(['docentry' => $docentry])->count();
            if ($detailCount <= 0) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Debe capturar al menos una linea de detalle para cerrar', 'Data' => []];
            }

            $header->doc_status = DocTireDisposal::DOC_STATUS_C;
            $header->status = DocTireDisposal::STATUS_CLOSE;

            if (!$header->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }
            // ── Integración: aplicar cambios a llantas ──
            $integrationService = new TireIntegrationService();
            $result = $integrationService->onDisposalClosed($docentry, $tx);
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
            $header = DocTireDisposal::findOne(['docentry' => $docentry]);
            if ($header === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }

            if ($header->canceled === DocTireDisposal::CANCELED_Y) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'El documento ya esta cancelado', 'Data' => []];
            }

            $header->canceled = DocTireDisposal::CANCELED_Y;
            $header->doc_status = DocTireDisposal::DOC_STATUS_C;
            $header->status = DocTireDisposal::STATUS_CLOSE;

            if (!$header->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($header), 'Data' => []];
            }

            if ($ownTx) {
                $tx->commit();
            }

            return ['Success' => 'Ok', 'Msg' => 'Documento cancelado correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error al cancelar documento';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
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

    public function getNextDocnum(int $seriesId): array
    {
        try {
            $seriesServices = new SeriesServices();
            return $seriesServices->peekNextNumber('DocTireDisposal', $seriesId);
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al obtener siguiente folio';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function getFormOptions(): array
    {
        return [
            'Success' => 'Ok',
            'Msg' => '',
            'Data' => [
                'tire_options' => $this->getAvailableTires(),
                'status_options' => [
                    DocTireDisposal::STATUS_PLAN => 'Planeado',
                    DocTireDisposal::STATUS_VAL => 'Validado',
                    DocTireDisposal::STATUS_CLOSE => 'Cerrado',
                ],
                'reason_options' => [
                    DocTireDisposalDetail::DISPOSAL_REASON_WEAR => 'Desgaste',
                    DocTireDisposalDetail::DISPOSAL_REASON_DAMAGE => 'Danio',
                    DocTireDisposalDetail::DISPOSAL_REASON_ACCIDENT => 'Accidente',
                    DocTireDisposalDetail::DISPOSAL_REASON_THEFT => 'Robo',
                    DocTireDisposalDetail::DISPOSAL_REASON_RETREAD_LIMIT => 'Limite renovados',
                    DocTireDisposalDetail::DISPOSAL_REASON_SIDEWALL_DAMAGE => 'Danio lateral',
                    DocTireDisposalDetail::DISPOSAL_REASON_OTHER => 'Otro',
                ],
                'series_options' => Series::find()
                    ->select(['id AS code', 'name AS name', 'prefix'])
                    ->where([
                        'object_name' => 'DocTireDisposal',
                        'is_active' => Series::ACTIVE_Y,
                    ])
                    ->orderBy(['name' => SORT_ASC])
                    ->asArray()
                    ->all(),
            ],
        ];
    }

    /**
     * Obtiene llantas disponibles: no desechadas, no asignadas a vehículo,
     * y que no estén ya en un documento de baja activo.
     */
    /**
     * Obtiene llantas disponibles: no desechadas, no asignadas a vehículo,
     * y que no estén ya en un documento de baja activo.
     */
    public function getAvailableTires(): array
    {
        $disposedCodes = DocTireDisposalDetail::find()
            ->alias('dd')
            ->innerJoin('doc_tire_disposal d', 'd.docentry = dd.docentry')
            ->where(['d.canceled' => DocTireDisposal::CANCELED_N])
            ->andWhere(['not', ['d.doc_status' => DocTireDisposal::DOC_STATUS_C]])
            ->select(['dd.tire_code'])
            ->column();

        return Tire::find()
            ->select([
                'tire_code AS code',
                'tire_name AS name',
                'operational_status',
                'location_status',
                'physical_condition',
                'current_km',
                'curr_tread_depth',
                'retread_qty',
                'purchase_date',
            ])
            ->where(['not', ['operational_status' => Tire::OP_STATUS_DS]])
            ->andWhere(['not', ['location_status' => Tire::LOC_SC]])
            ->andWhere(['assigned_unit_code' => null])
            ->andWhere(['not in', 'tire_code', $disposedCodes])
            ->orderBy(['tire_code' => SORT_ASC])
            ->asArray()
            ->all();
    }

    /**
     * Valida que todas las llantas en los detalles estén disponibles.
     * Retorna ['Success' => 'Ok'] si todas pasan, o ['Success' => 'Error', 'Msg' => ...] si alguna falla.
     */
    private function validateTireAvailability(array $details, int $currentDocentry): array
    {
        $availableCodes = array_column($this->getAvailableTires(), 'code');

        // Si es actualización, también se permiten las llantas ya asignadas a este documento
        $existingCodes = DocTireDisposalDetail::find()
            ->select(['tire_code'])
            ->where(['docentry' => $currentDocentry])
            ->column();

        $allowedCodes = array_unique(array_merge($availableCodes, $existingCodes));

        foreach ($details as $row) {
            if (!is_array($row)) {
                continue;
            }
            $tireCode = $row['tire_code'] ?? '';
            if ($tireCode === '') {
                continue;
            }
            if (!in_array($tireCode, $allowedCodes, true)) {
                $tire = Tire::findOne(['tire_code' => $tireCode]);
                $reason = 'no disponible';
                if ($tire !== null) {
                    if ($tire->assigned_unit_code !== null) {
                        $reason = 'está asignada a un vehículo';
                    } elseif ($tire->operational_status === Tire::OP_STATUS_DS || $tire->location_status === Tire::LOC_SC) {
                        $reason = 'ya está desechada';
                    } else {
                        $reason = 'ya está en otro documento de baja';
                    }
                }
                return [
                    'Success' => 'Error',
                    'Msg' => "La llanta {$tireCode} {$reason}",
                    'Data' => [],
                ];
            }
        }

        return ['Success' => 'Ok', 'Msg' => '', 'Data' => []];
    }

    private function syncDetails(int $docentry, array $details): array
    {
        DocTireDisposalDetail::deleteAll(['docentry' => $docentry]);

        $lineNum = 1;
        foreach ($details as $row) {
            if (!is_array($row)) {
                continue;
            }

            $row = $this->normalizePayload($row);
            $model = new DocTireDisposalDetail();
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
        DocTireDisposalAttach::deleteAll(['docentry' => $docentry]);

        $detailLines = DocTireDisposalDetail::find()
            ->select('linenum')
            ->where(['docentry' => $docentry])
            ->column();
        $validLines = array_map('intval', $detailLines);

        $lineNum = 1;
        foreach ($attachments as $row) {
            if (!is_array($row)) {
                continue;
            }

            $row = $this->normalizePayload($row);
            $model = new DocTireDisposalAttach();
            $row['docentry'] = $docentry;
            $row['linenum'] = isset($row['linenum']) ? (int)$row['linenum'] : $lineNum;

            if (!in_array((int)$row['linenum'], $validLines, true)) {
                return [
                    'Success' => 'Error',
                    'Msg' => 'Cada adjunto debe apuntar a una linea de detalle valida',
                    'Data' => [],
                ];
            }

            $model->setAttributes($row);
            if (!$model->save()) {
                return ['Success' => 'Error', 'Msg' => $this->modelErrors($model), 'Data' => []];
            }

            $lineNum++;
        }

        return ['Success' => 'Ok', 'Msg' => '', 'Data' => []];
    }

    private function isImmutable(DocTireDisposal $header): bool
    {
        return ($header->doc_status === DocTireDisposal::DOC_STATUS_C)
            || ($header->canceled === DocTireDisposal::CANCELED_Y)
            || ($header->status === DocTireDisposal::STATUS_CLOSE);
    }

    private function normalizePayload(array $data): array
    {
        // Mapeo de nombres de campo frontend → BD
        $fieldMap = [
            'estimated_loss' => 'scrap_value',
            'doc_duedate' => 'disposal_date',
        ];

        $normalized = [];
        foreach ($data as $k => $v) {
            $targetKey = $fieldMap[$k] ?? $k;
            if (is_array($v)) {
                $normalized[$targetKey] = $this->normalizePayload($v);
                continue;
            }
            if (is_string($v) && trim($v) === '') {
                $normalized[$targetKey] = null;
            } else {
                $normalized[$targetKey] = $v;
            }
        }
        return $normalized;
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
