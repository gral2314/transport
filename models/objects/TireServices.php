<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\Tire;
use app\models\tables\TireBrand;
use app\models\tables\TireModel;
use app\models\tables\TireSize;
use app\models\tables\TireType;
use app\models\tables\TireTreadDesign;
use app\models\tables\TireUsageType;
use app\models\tables\Country;
use app\models\tables\Vehicle;
use app\models\tables\VehicleTire;
use app\models\tables\DocTireMovementDetail;
use yii\db\Transaction;

class TireServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = Tire::find()
                ->select([
                    'tire.*',
                    'tire_brand.name as brand_name',
                    'tire_model.name as model_name',
                    'tire_size.name as size_name',
                    'tire_type.name as type_name',
                ])
                ->leftJoin('tire_brand', 'tire.brand_code = tire_brand.code')
                ->leftJoin('tire_model', 'tire.model_code = tire_model.code')
                ->leftJoin('tire_size', 'tire.size_code = tire_size.code')
                ->leftJoin('tire_type', 'tire.type_code = tire_type.code');
                
            if (!empty($filters['operational_status'])) {
                $query->andWhere(['tire.operational_status' => $filters['operational_status']]);
            }
            if (!empty($filters['location_status'])) {
                $query->andWhere(['tire.location_status' => $filters['location_status']]);
            }
            if (!empty($filters['brand_code'])) {
                $query->andWhere(['tire.brand_code' => $filters['brand_code']]);
            }
            if (!empty($filters['search'])) {
                $query->andWhere(['or',
                    ['like', 'tire.tire_code', $filters['search']],
                    ['like', 'tire.tire_name', $filters['search']],
                    ['like', 'tire.serial_no', $filters['search']],
                ]);
            }
            
            $rows = $query->orderBy(['tire.tire_code' => SORT_ASC])->asArray()->all();
            
            // Agregar etiquetas de status
            foreach ($rows as &$row) {
                $row['operational_status_name'] = Tire::getOperationalStatusLabel($row['operational_status'] ?? '');
                $row['location_status_name'] = Tire::getLocationStatusLabel($row['location_status'] ?? '');
                $row['physical_condition_name'] = Tire::getPhysicalConditionLabel($row['physical_condition'] ?? '');
            }
            
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $rows];
        } catch (\Throwable $e) {
            $errorMsg = 'Error al listar llantas';
            if (YII_DEBUG) {
                $errorMsg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []];
        }
    }

    public function get(string $pk): array
    {
        $model = Tire::findOne(['tire_code' => $pk]);
        if ($model === null) {
            return ['Success' => 'Error', 'Msg' => 'Llanta no encontrada', 'Data' => []];
        }
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $model->toArray()];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            // Normalizar datos: convertir strings vacíos a NULL en campos FK
            $fkFields = [
                'brand_code', 'model_code', 'size_code', 'type_code',
                'tread_design_code', 'usage_type_code', 'country_code',
                'assigned_unit_code'
            ];
            foreach ($fkFields as $field) {
                if (isset($data[$field]) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
            
            // Normalizar campos numéricos: convertir strings vacíos a NULL
            $numericFields = [
                'current_km', 'purchase_price', 'max_km', 'retread_qty',
                'tire_width', 'aspect_ratio', 'rim_size', 'load_idx',
                'max_load', 'max_press', 'orig_tread_depth', 'init_tread_depth',
                'curr_tread_depth', 'tread_wear_factor', 'init_km', 'repair_qty'
            ];
            foreach ($numericFields as $field) {
                if (isset($data[$field]) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
            
            $pk = $data['tire_code'] ?? null;
            $model = ($pk !== null) ? Tire::findOne(['tire_code' => $pk]) : null;
            if ($model === null) {
                $model = new Tire();
            }
            $model->setAttributes($data);
            if (!$model->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => implode('; ', array_merge(...array_values($model->getErrors()))), 'Data' => []];
            }
            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Llanta guardada correctamente', 'Data' => $model->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            
            // SEGURIDAD: No exponer detalles de SQL en producción
            $errorMsg = 'Error al guardar la llanta';
            
            if (YII_DEBUG) {
                // Solo en desarrollo mostrar el error técnico
                \Yii::error([
                    'message' => 'Error en TireService::save',
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'data' => $data,
                ], __METHOD__);
                $errorMsg .= ': ' . $e->getMessage();
            } else {
                // En producción solo loguear, no exponer al usuario
                \Yii::error([
                    'message' => 'Error en TireService::save',
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ], __METHOD__);
            }
            
            return ['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []];
        }
    }

    public function delete(string $pk, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $model = Tire::findOne(['tire_code' => $pk]);
            if ($model === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Llanta no encontrada', 'Data' => []];
            }
            $model->delete();
            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Llanta eliminada correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            
            $errorMsg = 'Error al eliminar la llanta';
            if (YII_DEBUG) {
                $errorMsg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []];
        }
    }

    public function getFormOptions(): array
    {
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => [
            // Catálogos para selects
            'brands'        => TireBrand::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
            'models'        => TireModel::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
            'sizes'         => TireSize::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
            'types'         => TireType::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
            'tread_designs' => TireTreadDesign::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
            'usage_types'   => TireUsageType::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
            'countries'     => Country::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
            'vehicles'      => Vehicle::find()->select(['vehicle_code as code', 'vehicle_name as name'])->where(['active' => 'Y'])->asArray()->all(),
            
            // Opciones de enums
            'operational_status_options' => Tire::getOperationalStatusOptions(),
            'physical_condition_options' => Tire::getPhysicalConditionOptions(),
            'location_status_options'    => Tire::getLocationStatusOptions(),
            'is_final_options'           => Tire::getIsFinalOptions(),
            'structure_type_options'     => Tire::getStructureTypeOptions(),
            'traction_rate_options'      => Tire::getTractionRateOptions(),
            'temp_rate_options'          => Tire::getTempRateOptions(),
        ]];
    }
    // ═══════════════════════════════════════════════════════════════════════
    // TireStateManager — Gestión de estados de llanta por tipo de movimiento
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Aplica un cambio de estado a una llanta según el tipo de movimiento.
     *
     * @param string $tireCode Código de la llanta
     * @param string $movementType Tipo de movimiento (DocTireMovementDetail::MOVEMENT_TYPE_*)
     * @param array $detailData Datos del detalle (vehicle_code_from/to, position_from/to, whs_code_from/to, etc.)
     * @param Transaction|null $transaction Transacción externa opcional
     * @return array ['Success' => 'Ok'|'Error', 'Msg' => '...']
     */
    public function applyMovement(
        string $tireCode,
        string $movementType,
        array $detailData = [],
        ?Transaction $transaction = null
    ): array {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $tire = Tire::findOne(['tire_code' => $tireCode]);
            if ($tire === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => "Llanta {$tireCode} no encontrada"];
            }

            switch ($movementType) {
                case DocTireMovementDetail::MOVEMENT_TYPE_ASSIGN:
                    // ASIGN: WH → VH, AV → US
                    $tire->location_status = Tire::LOC_VH;
                    $tire->operational_status = Tire::OP_STATUS_US;
                    $tire->assigned_unit_code = $detailData['vehicle_code_to'] ?? $detailData['vehicle_code_from'] ?? null;
                    $tire->assigned_position_code = $detailData['position_to'] ?? null;
                    $tire->assigned_axle_code = $detailData['axle_code_to'] ?? null;
                    break;

                case DocTireMovementDetail::MOVEMENT_TYPE_ROTATE:
                    // ROTATE: VH → VH (cambia posición), US → US
                    $tire->assigned_position_code = $detailData['position_to'] ?? null;
                    $tire->assigned_axle_code = $detailData['axle_code_to'] ?? null;
                    break;

                case DocTireMovementDetail::MOVEMENT_TYPE_REMOVE:
                    // REMOVE: VH → WH, US → AV
                    $tire->location_status = Tire::LOC_WH;
                    $tire->operational_status = Tire::OP_STATUS_AV;
                    $tire->assigned_unit_code = null;
                    $tire->assigned_position_code = null;
                    $tire->assigned_axle_code = null;
                    break;

                case DocTireMovementDetail::MOVEMENT_TYPE_TRANSFER:
                    // TRANSFER: VH → VH (cambia de unidad)
                    $tire->location_status = Tire::LOC_VH;
                    $tire->operational_status = Tire::OP_STATUS_US;
                    $tire->assigned_unit_code = $detailData['vehicle_code_to'] ?? null;
                    $tire->assigned_position_code = $detailData['position_to'] ?? null;
                    $tire->assigned_axle_code = $detailData['axle_code_to'] ?? null;
                    break;

                case DocTireMovementDetail::MOVEMENT_TYPE_REPAIR_SEND:
                    // REPAIR_SEND: VH → WS, US → MT
                    $tire->location_status = Tire::LOC_WS;
                    $tire->operational_status = Tire::OP_STATUS_MT;
                    $tire->assigned_unit_code = null;
                    $tire->assigned_position_code = null;
                    $tire->assigned_axle_code = null;
                    break;

                case DocTireMovementDetail::MOVEMENT_TYPE_REPAIR_RETURN:
                    // REPAIR_RETURN: WS → VH, MT → US
                    $tire->location_status = Tire::LOC_VH;
                    $tire->operational_status = Tire::OP_STATUS_US;
                    $tire->assigned_unit_code = $detailData['vehicle_code_to'] ?? $detailData['vehicle_code_from'] ?? null;
                    $tire->assigned_position_code = $detailData['position_to'] ?? null;
                    $tire->assigned_axle_code = $detailData['axle_code_to'] ?? null;
                    break;

                case DocTireMovementDetail::MOVEMENT_TYPE_SCRAP:
                    // SCRAP: VH/WH → SC, US/AV → DS
                    $tire->location_status = Tire::LOC_SC;
                    $tire->operational_status = Tire::OP_STATUS_DS;
                    $tire->assigned_unit_code = null;
                    $tire->assigned_position_code = null;
                    $tire->assigned_axle_code = null;
                    $tire->is_final = Tire::IS_FINAL_Y;
                    break;

                default:
                    if ($ownTx) {
                        $tx->rollBack();
                    }
                    return ['Success' => 'Error', 'Msg' => "Tipo de movimiento no soportado: {$movementType}"];
            }

            if (!$tire->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Error al actualizar estado de llanta: ' . implode('; ', array_merge(...array_values($tire->getErrors())))];
            }

            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Estado de llanta actualizado correctamente'];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error al aplicar movimiento a llanta';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg];
        }
    }

    /**
     * Actualiza el registro vehicle_tire cuando una llanta cambia de vehículo/posición.
     * NUNCA elimina registros — solo actualiza tire_code o lo pone en NULL.
     *
     * @param string $tireCode Código de la llanta
     * @param string|null $vehicleCode Código del vehículo (null = desasignar)
     * @param string|null $positionCode Posición (LI, LO, RI, RO, etc.)
     * @param string|null $axleCode Eje
     * @param int|null $lineNum Número de línea específico (opcional)
     * @param Transaction|null $transaction Transacción externa
     * @return array
     */
    public function updateVehicleTire(
        string $tireCode,
        ?string $vehicleCode = null,
        ?string $positionCode = null,
        ?string $axleCode = null,
        ?int $lineNum = null,
        ?Transaction $transaction = null
    ): array {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            if ($vehicleCode === null) {
                // Desasignar: buscar el registro donde está esta llanta y poner tire_code = NULL
                $records = VehicleTire::findAll(['tire_code' => $tireCode]);
                foreach ($records as $rec) {
                    $rec->tire_code = null;
                    if (!$rec->save()) {
                        if ($ownTx) {
                            $tx->rollBack();
                        }
                        return ['Success' => 'Error', 'Msg' => 'Error al desasignar llanta del vehículo'];
                    }
                }
            } else {
                if ($lineNum !== null) {
                    // Actualizar registro específico
                    $vt = VehicleTire::findOne(['vehicle_code' => $vehicleCode, 'line_num' => $lineNum]);
                    if ($vt === null) {
                        if ($ownTx) {
                            $tx->rollBack();
                        }
                        return ['Success' => 'Error', 'Msg' => "Línea {$lineNum} no encontrada en vehículo {$vehicleCode}"];
                    }
                    // Si ya había una llanta en esta posición, desasignarla
                    if ($vt->tire_code !== null && $vt->tire_code !== $tireCode) {
                        $oldTire = Tire::findOne(['tire_code' => $vt->tire_code]);
                        if ($oldTire !== null) {
                            $oldTire->assigned_unit_code = null;
                            $oldTire->assigned_position_code = null;
                            $oldTire->assigned_axle_code = null;
                            $oldTire->location_status = Tire::LOC_WH;
                            $oldTire->operational_status = Tire::OP_STATUS_AV;
                            $oldTire->save();
                        }
                    }
                    $vt->tire_code = $tireCode;
                    $vt->position_code = $positionCode ?? $vt->position_code;
                    $vt->axle_type_code = $axleCode ?? $vt->axle_type_code;
                    if (!$vt->save()) {
                        if ($ownTx) {
                            $tx->rollBack();
                        }
                        return ['Success' => 'Error', 'Msg' => 'Error al asignar llanta en vehículo'];
                    }
                } else {
                    // Buscar línea por posición
                    $vt = VehicleTire::findOne([
                        'vehicle_code' => $vehicleCode,
                        'position_code' => $positionCode,
                    ]);
                    if ($vt === null) {
                        if ($ownTx) {
                            $tx->rollBack();
                        }
                        return ['Success' => 'Error', 'Msg' => "No se encontró posición {$positionCode} en vehículo {$vehicleCode}"];
                    }
                    // Desasignar llanta anterior si existe
                    if ($vt->tire_code !== null && $vt->tire_code !== $tireCode) {
                        $oldTire = Tire::findOne(['tire_code' => $vt->tire_code]);
                        if ($oldTire !== null) {
                            $oldTire->assigned_unit_code = null;
                            $oldTire->assigned_position_code = null;
                            $oldTire->assigned_axle_code = null;
                            $oldTire->location_status = Tire::LOC_WH;
                            $oldTire->operational_status = Tire::OP_STATUS_AV;
                            $oldTire->save();
                        }
                    }
                    $vt->tire_code = $tireCode;
                    if (!$vt->save()) {
                        if ($ownTx) {
                            $tx->rollBack();
                        }
                        return ['Success' => 'Error', 'Msg' => 'Error al asignar llanta en vehículo'];
                    }
                }
            }

            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Registro vehicle_tire actualizado'];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error al actualizar vehicle_tire';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // TireLiveManager — Gestión de km, desgaste, reparaciones y retreads
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Actualiza el kilometraje de una llanta.
     */
    public function updateKm(string $tireCode, float $km, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $tire = Tire::findOne(['tire_code' => $tireCode]);
            if ($tire === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => "Llanta {$tireCode} no encontrada"];
            }

            $tire->current_km = ($tire->current_km ?? 0) + $km;

            if (!$tire->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Error al actualizar km de llanta'];
            }

            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Km actualizados', 'Data' => ['current_km' => $tire->current_km]];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error al actualizar km';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg];
        }
    }

    /**
     * Actualiza el desgaste (profundidad y factor de desgaste) de una llanta.
     */
    public function updateDesgaste(
        string $tireCode,
        ?float $treadDepth = null,
        ?float $wearFactor = null,
        ?Transaction $transaction = null
    ): array {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $tire = Tire::findOne(['tire_code' => $tireCode]);
            if ($tire === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => "Llanta {$tireCode} no encontrada"];
            }

            if ($treadDepth !== null) {
                $tire->curr_tread_depth = $treadDepth;
            }
            if ($wearFactor !== null) {
                $tire->tread_wear_factor = $wearFactor;
            }

            if (!$tire->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Error al actualizar desgaste de llanta'];
            }

            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Desgaste actualizado'];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error al actualizar desgaste';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg];
        }
    }

    /**
     * Incrementa el contador de reparaciones de una llanta.
     */
    public function registerRepair(string $tireCode, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $tire = Tire::findOne(['tire_code' => $tireCode]);
            if ($tire === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => "Llanta {$tireCode} no encontrada"];
            }

            $tire->repair_qty = ($tire->repair_qty ?? 0) + 1;

            if (!$tire->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Error al registrar reparación'];
            }

            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Reparación registrada', 'Data' => ['repair_qty' => $tire->repair_qty]];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error al registrar reparación';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg];
        }
    }

    /**
     * Incrementa el contador de reencauches de una llanta.
     */
    public function registerRetread(string $tireCode, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $tire = Tire::findOne(['tire_code' => $tireCode]);
            if ($tire === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => "Llanta {$tireCode} no encontrada"];
            }

            $tire->retread_qty = ($tire->retread_qty ?? 0) + 1;
            $tire->physical_condition = Tire::COND_RT;

            if (!$tire->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Error al registrar reencauche'];
            }

            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Reencauche registrado', 'Data' => ['retread_qty' => $tire->retread_qty]];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error al registrar reencauche';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg];
        }
    }

    /**
     * Marca una llanta como desechada (disposal).
     */
    public function markAsDisposed(string $tireCode, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $tire = Tire::findOne(['tire_code' => $tireCode]);
            if ($tire === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => "Llanta {$tireCode} no encontrada"];
            }

            $tire->location_status = Tire::LOC_SC;
            $tire->operational_status = Tire::OP_STATUS_DS;
            $tire->is_final = Tire::IS_FINAL_Y;
            $tire->assigned_unit_code = null;
            $tire->assigned_position_code = null;
            $tire->assigned_axle_code = null;

            if (!$tire->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Error al marcar llanta como desechada'];
            }

            // Desasignar de vehicle_tire
            $this->updateVehicleTire($tireCode, null, null, null, null, $tx);

            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Llanta marcada como desechada'];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error al desechar llanta';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg];
        }
    }

    /**
     * Evalúa si una llanta debe marcarse como final (is_final = Y)
     * basado en sus condiciones actuales.
     */
    public function evaluateFinalStatus(string $tireCode, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $tire = Tire::findOne(['tire_code' => $tireCode]);
            if ($tire === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => "Llanta {$tireCode} no encontrada"];
            }

            $isFinal = false;

            // Criterios para considerar llanta como final:
            // 1. Ya está en estado DS (desechada)
            if ($tire->operational_status === Tire::OP_STATUS_DS) {
                $isFinal = true;
            }
            // 2. Límite de reencauches (3)
            if (($tire->retread_qty ?? 0) >= 3) {
                $isFinal = true;
            }
            // 3. Profundidad por debajo del límite (2mm)
            if (($tire->curr_tread_depth ?? 99) < 2.0) {
                $isFinal = true;
            }
            // 4. Km máximo excedido
            if ($tire->max_km !== null && ($tire->current_km ?? 0) >= $tire->max_km) {
                $isFinal = true;
            }

            if ($isFinal && $tire->is_final === Tire::IS_FINAL_N) {
                $tire->is_final = Tire::IS_FINAL_Y;
                if (!$tire->save()) {
                    if ($ownTx) {
                        $tx->rollBack();
                    }
                    return ['Success' => 'Error', 'Msg' => 'Error al actualizar estado final de llanta'];
                }
            }

            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => ['is_final' => $tire->is_final]];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error al evaluar estado final';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // TireQueryService — Consultas especializadas de llantas
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Obtiene llantas disponibles para asignación (AV + WH, no finales).
     */
    public function getAvailableForAssignment(): array
    {
        try {
            $rows = Tire::find()
                ->where([
                    'operational_status' => Tire::OP_STATUS_AV,
                    'location_status' => Tire::LOC_WH,
                    'is_final' => Tire::IS_FINAL_N,
                ])
                ->orderBy(['tire_code' => SORT_ASC])
                ->asArray()
                ->all();

            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $rows];
        } catch (\Throwable $e) {
            $msg = 'Error al consultar llantas disponibles';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    /**
     * Obtiene llantas montadas en un vehículo específico.
     */
    public function getMountedOnVehicle(string $vehicleCode): array
    {
        try {
            $rows = Tire::find()
                ->where([
                    'assigned_unit_code' => $vehicleCode,
                    'location_status' => Tire::LOC_VH,
                ])
                ->orderBy(['tire_code' => SORT_ASC])
                ->asArray()
                ->all();

            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $rows];
        } catch (\Throwable $e) {
            $msg = 'Error al consultar llantas del vehículo';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    /**
     * Obtiene llantas en almacén.
     */
    public function getInWarehouse(): array
    {
        try {
            $rows = Tire::find()
                ->where([
                    'location_status' => Tire::LOC_WH,
                    'is_final' => Tire::IS_FINAL_N,
                ])
                ->orderBy(['tire_code' => SORT_ASC])
                ->asArray()
                ->all();

            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $rows];
        } catch (\Throwable $e) {
            $msg = 'Error al consultar llantas en almacén';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    /**
     * Obtiene llantas en taller/workshop.
     */
    public function getInWorkshop(): array
    {
        try {
            $rows = Tire::find()
                ->where(['location_status' => Tire::LOC_WS])
                ->orderBy(['tire_code' => SORT_ASC])
                ->asArray()
                ->all();

            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $rows];
        } catch (\Throwable $e) {
            $msg = 'Error al consultar llantas en taller';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    /**
     * Obtiene el estado completo de una llanta (incluye información del vehículo si está asignada).
     */
    public function getTireFullStatus(string $tireCode): array
    {
        try {
            $tire = Tire::find()
                ->with(['assignedUnit', 'brand', 'model', 'size', 'type'])
                ->where(['tire_code' => $tireCode])
                ->one();

            if ($tire === null) {
                return ['Success' => 'Error', 'Msg' => "Llanta {$tireCode} no encontrada", 'Data' => []];
            }

            $data = $tire->toArray();
            $data['operational_status_name'] = Tire::getOperationalStatusLabel($tire->operational_status);
            $data['location_status_name'] = Tire::getLocationStatusLabel($tire->location_status);
            $data['physical_condition_name'] = Tire::getPhysicalConditionLabel($tire->physical_condition);

            if ($tire->assignedUnit !== null) {
                $data['assigned_unit_name'] = $tire->assignedUnit->vehicle_name;
            }

            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $data];
        } catch (\Throwable $e) {
            $msg = 'Error al consultar estado de llanta';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }}
