<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\CargoType;
use app\models\tables\CenterCost;
use app\models\tables\DocTypeVehicule;
use app\models\tables\FuelType;
use app\models\tables\Nom012;
use app\models\tables\SatVehicleConfig;
use app\models\tables\ServiceType;
use app\models\tables\Tire;
use app\models\tables\Vehicle;
use app\models\tables\VehicleBrand;
use app\models\tables\VehicleDocument;
use app\models\tables\VehicleTire;
use app\models\tables\VehicleType;
use yii\db\Transaction;

class VehicleService
{
    /**
     * Limpia los datos: convierte strings vacías a NULL para evitar violaciones de FK
     * @param array $data
     * @return array
     */
    private function cleanData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }
        return $data;
    }

    public function list(array $filters = []): array
    {
        try {
            // ✅ MEJORA: Incluir nombres descriptivos mediante JOINs
            $query = Vehicle::find()
                ->alias('v')
                ->select([
                    'v.*',
                    'vt.name AS vehicle_type_name',
                    'st.name AS service_type_name',
                    'ct.name AS cargo_type_name',
                    new \yii\db\Expression("
                        CASE
                            WHEN v.status = 'A' THEN 'Activo'
                            WHEN v.status = 'I' THEN 'Inactivo'
                            WHEN v.status = 'M' THEN 'Mantenimiento'
                            WHEN v.status = 'O' THEN 'Baja'
                            ELSE v.status
                        END AS status_name
                    "),
                ])
                ->leftJoin('vehicle_type vt', 'v.vehicle_type_code = vt.code')
                ->leftJoin('service_type st', 'v.service_type_code = st.code')
                ->leftJoin('cargo_type ct', 'v.cargo_type_code = ct.code');
            
            if (!empty($filters['status'])) {
                $query->andWhere(['v.status' => $filters['status']]);
            }
            if (!empty($filters['active'])) {
                $query->andWhere(['v.active' => $filters['active']]);
            }
            if (!empty($filters['vehicle_type_code'])) {
                $query->andWhere(['v.vehicle_type_code' => $filters['vehicle_type_code']]);
            }
            if (!empty($filters['cost_center_code'])) {
                $query->andWhere(['v.cost_center_code' => $filters['cost_center_code']]);
            }
            if (!empty($filters['search'])) {
                $query->andWhere(['or',
                    ['like', 'v.vehicle_code', $filters['search']],
                    ['like', 'v.vehicle_name', $filters['search']],
                    ['like', 'v.plate_no', $filters['search']],
                    ['like', 'v.economic_no', $filters['search']],
                ]);
            }
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['v.vehicle_code' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            \Yii::error([
                'message' => 'Error al listar vehículos',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $filters
            ], __METHOD__);
            return ['Success' => 'Error', 'Msg' => 'Error al obtener la lista de vehículos. Intente nuevamente.', 'Data' => []];
        }
    }

    public function get(string $pk): array
    {
        try {
            $model = Vehicle::findOne(['vehicle_code' => $pk]);
            if ($model === null) {
                return ['Success' => 'Error', 'Msg' => 'Unidad no encontrada', 'Data' => []];
            }
            $data = $model->toArray();
            $data['documents'] = VehicleDocument::find()->where(['vehicle_code' => $pk])->orderBy(['line_num' => SORT_ASC])->asArray()->all();
            
           $data['tires'] = VehicleTire::find()->alias('t0')
                            ->select([
                                't0.*',
                                't1.name AS axle_type_name',
                                't0.position_code',
                                new \yii\db\Expression("
                                    CASE
                                        WHEN t0.position_code = 'LI' THEN 'IZQUIERDA INTERNA'
                                        WHEN t0.position_code = 'LO' THEN 'IZQUIERDA EXTERNA'
                                        WHEN t0.position_code = 'RI' THEN 'DERECHA INTERNA'
                                        WHEN t0.position_code = 'RO' THEN 'DERECHA EXTERNA'
                                        WHEN t0.position_code = 'LS' THEN 'IZQUIERDA SOLA'
                                        WHEN t0.position_code = 'RS' THEN 'DERECHA SOLA'
                                        WHEN t0.position_code = 'LI1' THEN 'IZQUIERDA INTERNA EJE 1'
                                        WHEN t0.position_code = 'LO1' THEN 'IZQUIERDA EXTERNA EJE 1'
                                        WHEN t0.position_code = 'RI1' THEN 'DERECHA INTERNA EJE 1'
                                        WHEN t0.position_code = 'RO1' THEN 'DERECHA EXTERNA EJE 1'
                                        WHEN t0.position_code = 'LI2' THEN 'IZQUIERDA INTERNA EJE 2'
                                        WHEN t0.position_code = 'LO2' THEN 'IZQUIERDA EXTERNA EJE 2'
                                        WHEN t0.position_code = 'RI2' THEN 'DERECHA INTERNA EJE 2'
                                        WHEN t0.position_code = 'RO2' THEN 'DERECHA EXTERNA EJE 2'
                                        WHEN t0.position_code = 'LI3' THEN 'IZQUIERDA INTERNA EJE 3'
                                        WHEN t0.position_code = 'LO3' THEN 'IZQUIERDA EXTERNA EJE 3'
                                        WHEN t0.position_code = 'RI3' THEN 'DERECHA INTERNA EJE 3'
                                        WHEN t0.position_code = 'RO3' THEN 'DERECHA EXTERNA EJE 3'
                                        ELSE ''
                                    END AS position_name
                                ")
                            ])
                            ->leftJoin('axle_type t1', 't0.axle_type_code = t1.code')
                            ->where(['vehicle_code' => $pk])
                            ->orderBy(['CAST(t0.line_num AS INTEGER)' => SORT_ASC])
                            ->asArray()
                            ->all();

            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $data];
        } catch (\Throwable $e) {
            \Yii::error([
                'message' => 'Error al obtener vehículo',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pk' => $pk
            ], __METHOD__);
            return ['Success' => 'Error', 'Msg' => 'Error al obtener los datos de la unidad.', 'Data' => []];
        }
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $pk = $data['vehicle_code'] ?? null;
            $model = ($pk !== null) ? Vehicle::findOne(['vehicle_code' => $pk]) : null;
            if ($model === null) {
                $model = new Vehicle();
            }
            
            // Asignar valor por defecto al campo 'object' si no viene en el payload
            if (!isset($data['object']) || trim($data['object']) === '') {
                $data['object'] = 'VEHICLE';
            }
            
            // Limpiar datos: convertir strings vacías a NULL
            $data = $this->cleanData($data);
            
            $model->setAttributes($data);
            if (!$model->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                // Retornar errores estructurados: campos y mensajes EN ESPAÑOL
                $errors = $model->getErrors();
                $labels = $model->attributeLabels(); // Obtener labels en español
                $errorFields = [];
                $errorMessages = [];
                foreach ($errors as $field => $messages) {
                    $fieldLabel = $labels[$field] ?? ucfirst($field); // Usar label español o capitalizar field
                    $errorMsg = is_array($messages) ? implode(', ', $messages) : $messages;
                    $errorFields[$field] = $errorMsg;
                    $errorMessages[] = $fieldLabel . ': ' . $errorMsg;
                }
                return [
                    'Success' => 'Error', 
                    'Msg' => implode(' | ', $errorMessages),
                    'Data' => [],
                    'Errors' => $errorFields  // Errores por campo técnico
                ];
            }
            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Unidad guardada', 'Data' => $model->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            // Loguear error completo para debugging (NO exponer al usuario)
            \Yii::error([
                'message' => 'Error al guardar vehículo',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ], __METHOD__);
            
            // Retornar mensaje genérico al usuario (seguridad: NO exponer estructura DB)
            return [
                'Success' => 'Error', 
                'Msg' => 'Error al guardar la unidad. Verifique que todos los datos relacionados existan (marca, tipo, etc.).', 
                'Data' => []
            ];
        }
    }

    public function delete(string $pk, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $model = Vehicle::findOne(['vehicle_code' => $pk]);
            if ($model === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Unidad no encontrada', 'Data' => []];
            }
            // Children cascade via DB FK ON DELETE CASCADE
            $model->delete();
            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Unidad eliminada', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            return ['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []];
        }
    }

    // -------------------------------------------------------------------------
    // Vehicle Documents
    // -------------------------------------------------------------------------

    public function saveDocument(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $vehicleCode = $data['vehicle_code'] ?? null;
            $lineNum     = isset($data['line_num']) ? (int)$data['line_num'] : null;

            if ($vehicleCode === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'vehicle_code requerido', 'Data' => []];
            }

            $model = ($lineNum !== null)
                ? VehicleDocument::findOne(['vehicle_code' => $vehicleCode, 'line_num' => $lineNum])
                : null;

            if ($model === null) {
                $model = new VehicleDocument();
                if ($lineNum === null) {
                    $maxLine = VehicleDocument::find()->where(['vehicle_code' => $vehicleCode])->max('line_num');
                    $data['line_num'] = ($maxLine !== null) ? ((int)$maxLine + 1) : 1;
                }
            }

            // Asignar valor por defecto al campo 'object' si no viene en el payload
            if (!isset($data['object']) || trim($data['object']) === '') {
                $data['object'] = 'VEHICLE_DOC';
            }
            
            // Limpiar datos: convertir strings vacías a NULL
            $data = $this->cleanData($data);

            $model->setAttributes($data);
            if (!$model->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                // Retornar errores en español
                $errors = $model->getErrors();
                $labels = $model->attributeLabels();
                $errorFields = [];
                $errorMessages = [];
                foreach ($errors as $field => $messages) {
                    $fieldLabel = $labels[$field] ?? ucfirst($field);
                    $errorMsg = is_array($messages) ? implode(', ', $messages) : $messages;
                    $errorFields[$field] = $errorMsg;
                    $errorMessages[] = $fieldLabel . ': ' . $errorMsg;
                }
                return [
                    'Success' => 'Error',
                    'Msg' => implode(' | ', $errorMessages),
                    'Data' => [],
                    'Errors' => $errorFields
                ];
            }
            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Documento guardado', 'Data' => $model->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            \Yii::error([
                'message' => 'Error al guardar documento',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ], __METHOD__);
            return ['Success' => 'Error', 'Msg' => 'Error al guardar el documento. Verifique los datos e intente nuevamente.', 'Data' => []];
        }
    }

    public function deleteDocument(string $vehicleCode, int $lineNum, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $model = VehicleDocument::findOne(['vehicle_code' => $vehicleCode, 'line_num' => $lineNum]);
            if ($model === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }
            $model->delete();
            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Documento eliminado', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            return ['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []];
        }
    }

    // -------------------------------------------------------------------------
    // Vehicle Tires
    // -------------------------------------------------------------------------

    public function saveTireLine(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $vehicleCode = $data['vehicle_code'] ?? null;
            $lineNum     = isset($data['line_num']) ? (int)$data['line_num'] : null;

            if ($vehicleCode === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'vehicle_code requerido', 'Data' => []];
            }

            $model = ($lineNum !== null)
                ? VehicleTire::findOne(['vehicle_code' => $vehicleCode, 'line_num' => $lineNum])
                : null;

            if ($model === null) {
                $model = new VehicleTire();
                if ($lineNum === null) {
                    $maxLine = VehicleTire::find()->where(['vehicle_code' => $vehicleCode])->max('line_num');
                    $data['line_num'] = ($maxLine !== null) ? ((int)$maxLine + 1) : 1;
                }
            }

            // Asignar valor por defecto al campo 'object' si no viene en el payload
            if (!isset($data['object']) || trim($data['object']) === '') {
                $data['object'] = 'VEHICLE_TIRE';
            }
            
            // Limpiar datos: convertir strings vacías a NULL
            $data = $this->cleanData($data);

            $model->setAttributes($data);
            if (!$model->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                // Retornar errores en español
                $errors = $model->getErrors();
                $labels = $model->attributeLabels();
                $errorFields = [];
                $errorMessages = [];
                foreach ($errors as $field => $messages) {
                    $fieldLabel = $labels[$field] ?? ucfirst($field);
                    $errorMsg = is_array($messages) ? implode(', ', $messages) : $messages;
                    $errorFields[$field] = $errorMsg;
                    $errorMessages[] = $fieldLabel . ': ' . $errorMsg;
                }
                return [
                    'Success' => 'Error',
                    'Msg' => implode(' | ', $errorMessages),
                    'Data' => [],
                    'Errors' => $errorFields
                ];
            }
            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Llanta de vehículo guardada', 'Data' => $model->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            \Yii::error([
                'message' => 'Error al guardar llanta',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ], __METHOD__);
            return ['Success' => 'Error', 'Msg' => 'Error al guardar la llanta. Verifique los datos e intente nuevamente.', 'Data' => []];
        }
    }

    public function deleteTireLine(string $vehicleCode, int $lineNum, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $model = VehicleTire::findOne(['vehicle_code' => $vehicleCode, 'line_num' => $lineNum]);
            if ($model === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Línea de llanta no encontrada', 'Data' => []];
            }
            $model->delete();
            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'Llanta de vehículo eliminada', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            return ['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []];
        }
    }

    // -------------------------------------------------------------------------
    // Form Options
    // -------------------------------------------------------------------------

    public function getFormOptions(): array
    {
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => [
            'acquisition_options'  => Vehicle::getAcquisitionOptions(),
            'status_options'       => Vehicle::getStatusOptions(),
            'active_options'       => Vehicle::getActiveOptions(),
            'available_options'    => Vehicle::getAvailableOptions(),
            'vehicle_type_list'    => VehicleType::getDropdownListExtended(), // ✅ CAMBIO: Método extendido
            'brand_list'           => VehicleBrand::getDropdownList(),
            'fuel_type_list'       => FuelType::getDropdownList(),
            'center_cost_list'     => CenterCost::getDropdownList(),
            'sat_config_list'      => SatVehicleConfig::getDropdownList(),
            'nom012_list'          => Nom012::getDropdownList(),
            'service_type_list'    => ServiceType::getDropdownList(),
            'cargo_type_list'      => CargoType::getDropdownList(),
            'doc_type_list'        => DocTypeVehicule::getDropdownList(),
            'tire_list'            => Tire::find()->where(['location_status' => Tire::LOC_WH])->select(['tire_name', 'tire_code'])->indexBy('tire_code')->column(),
        ]];
    }

    // ═══════════════════════════════════════════════════════════════════════
    // VehicleKmManager — Gestión de kilometraje de vehículos
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Actualiza el kilometraje actual de un vehículo.
     * Solo actualiza si el km nuevo es mayor al actual.
     *
     * @param string $vehicleCode Código del vehículo
     * @param float $km Kilometraje a registrar
     * @param Transaction|null $transaction Transacción externa opcional
     * @return array ['Success' => 'Ok'|'Error', 'Msg' => '...', 'Data' => [...]]
     */
    public function updateKm(string $vehicleCode, float $km, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $vehicle = Vehicle::findOne(['vehicle_code' => $vehicleCode]);
            if ($vehicle === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => "Vehículo {$vehicleCode} no encontrado"];
            }

            // Solo actualizar si el km nuevo es mayor
            if ($km > ($vehicle->current_km ?? 0)) {
                $vehicle->current_km = $km;
                if (!$vehicle->save()) {
                    if ($ownTx) {
                        $tx->rollBack();
                    }
                    return ['Success' => 'Error', 'Msg' => 'Error al actualizar km del vehículo'];
                }
            }

            if ($ownTx) {
                $tx->commit();
            }
            return [
                'Success' => 'Ok',
                'Msg' => '',
                'Data' => [
                    'vehicle_code' => $vehicleCode,
                    'current_km' => $vehicle->current_km,
                ],
            ];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error al actualizar km del vehículo';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg];
        }
    }
}
