<?php

declare(strict_types=1);
namespace app\models\objects;

use app\models\tables\VehicleType;
use app\models\tables\VehicleTypeAxle;
use app\models\tables\Vehicle;
use app\models\tables\VehicleTire;
use yii\db\Transaction;

class VehicleTypeServices
{
    /**
     * Valida que el tipo de vehículo tenga al menos 1 eje
     */
    private function validateMinimumAxles(array $axles): ?string
    {
        if (empty($axles)) {
            return 'El tipo de vehículo debe tener al menos 1 eje configurado.';
        }
        return null;
    }

    /**
     * Valida si el tipo de vehículo está asignado a algún vehículo
     */
    private function isVehicleTypeUsed(string $code): bool
    {
        return Vehicle::find()
            ->where(['vehicle_type_code' => $code])
            ->exists();
    }

    /**
     * Valida si un eje específico tiene llantas asignadas
     * Valida contra vehicle_tire usando axle_line_num y axle_type_code
     */
    private function isAxleLineUsed(string $vehicleTypeCode, int $lineNum, string $axleTypeCode): bool
    {
        // Obtener todos los vehículos de este tipo
        $vehicleCodes = Vehicle::find()
            ->select('vehicle_code')
            ->where(['vehicle_type_code' => $vehicleTypeCode])
            ->column();

        if (empty($vehicleCodes)) {
            return false;
        }

        // Verificar si alguno tiene llantas montadas en este eje específico
        return VehicleTire::find()
            ->where(['vehicle_code' => $vehicleCodes])
            ->andWhere(['axle_line_num' => $lineNum])
            ->andWhere(['axle_type_code' => $axleTypeCode])
            ->andWhere(['IS NOT', 'tire_code', null])
            ->exists();
    }

    /**
     * Calcula el siguiente line_num disponible para un código
     */
    private function getNextLineNum(string $code): int
    {
        $maxLineNum = VehicleTypeAxle::find()
            ->where(['code' => $code])
            ->max('line_num');
        
        return $maxLineNum ? ((int)$maxLineNum + 1) : 1;
    }

    /**
     * Elimina ejes que ya no están en el array de ejes enviado
     */
    private function deleteRemovedAxles(string $code, array $newAxles, Transaction $tx): array
    {
        // Obtener ejes actuales
        $currentAxles = VehicleTypeAxle::find()
            ->where(['code' => $code])
            ->indexBy('line_num')
            ->all();

        // Identificar line_nums a conservar
        $lineNumsToKeep = [];
        foreach ($newAxles as $axle) {
            if (isset($axle['line_num']) && $axle['line_num'] > 0) {
                $lineNumsToKeep[] = (int)$axle['line_num'];
            }
        }

        // Eliminar los que no están en la lista
        $errors = [];
        foreach ($currentAxles as $lineNum => $axle) {
            if (!in_array($lineNum, $lineNumsToKeep, true)) {
                // Validar que no tenga llantas asignadas
                if ($this->isAxleLineUsed($code, $lineNum, $axle->axle_type_code)) {
                    $errors[] = "No se puede eliminar el eje línea {$lineNum} ({$axle->axle_type_code}): tiene llantas asignadas en vehículos.";
                } else {
                    $axle->delete();
                }
            }
        }

        return $errors;
    }

    /**
     * Crea o actualiza los ejes del tipo de vehículo
     */
    private function saveAxles(string $code, array $axles, Transaction $tx): bool
    {
        foreach ($axles as $index => $axleData) {
            \Yii::info("saveAxles: Procesando eje index={$index}, data=" . json_encode($axleData), __METHOD__);
            
            $lineNum = isset($axleData['line_num']) && $axleData['line_num'] > 0 
                ? (int)$axleData['line_num'] 
                : $this->getNextLineNum($code);

            \Yii::info("saveAxles: Buscando eje code={$code}, line_num={$lineNum}", __METHOD__);
            $axle = VehicleTypeAxle::findOne(['code' => $code, 'line_num' => $lineNum]);
            
            if ($axle === null) {
                \Yii::info("saveAxles: Eje NO encontrado, creando nuevo", __METHOD__);
                $axle = new VehicleTypeAxle();
                $axle->code = $code;
                $axle->line_num = $lineNum;
            } else {
                \Yii::info("saveAxles: Eje encontrado, actualizando", __METHOD__);
            }

            $axle->axle_type_code = $axleData['axle_type_code'];
            \Yii::info("saveAxles: Antes de save(), isNewRecord=" . ($axle->isNewRecord ? 'true' : 'false'), __METHOD__);
            \Yii::info("saveAxles: Atributos: " . json_encode($axle->attributes), __METHOD__);
            
            if (!$axle->save()) {
                \Yii::error("saveAxles: save() retornó FALSE", __METHOD__);
                \Yii::error("saveAxles: Errores de validación: " . json_encode($axle->getErrors()), __METHOD__);
                return false;
            }
            
            \Yii::info("saveAxles: save() exitoso para line_num={$lineNum}", __METHOD__);
        }

        return true;
    }

    public function list(array $filters = []): array
    {
        try {
            $query = VehicleType::find()->with('axles');
            
            if (!empty($filters['active'])) {
                $query->andWhere(['active' => $filters['active']]);
            }
            
            if (!empty($filters['search'])) {
                $query->andWhere(['or',
                    ['like', 'code', $filters['search']],
                    ['like', 'name', $filters['search']]
                ]);
            }
            
            return [
                'Success' => 'Ok', 
                'Msg' => '', 
                'Data' => $query->orderBy(['code' => SORT_ASC])->asArray()->all()
            ];
        } catch (\Throwable $e) {
            return ['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []];
        }
    }

    public function get(string $pk): array
    {
        $model = VehicleType::findOne(['code' => $pk]);
        
        if ($model === null) {
            return ['Success' => 'Error', 'Msg' => 'Registro no encontrado', 'Data' => []];
        }
        
        $data = $model->toArray();
        
        // Incluir ejes configurados
        $axles = VehicleTypeAxle::find()
            ->where(['code' => $pk])
            ->orderBy(['line_num' => SORT_ASC])
            ->asArray()
            ->all();
        
        $data['axles'] = $axles;
        $data['is_used'] = $this->isVehicleTypeUsed($pk);
        
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $data];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        
        try {
            $pk = $data['code'] ?? null;
            $axles = $data['axles'] ?? [];
            
            // Validar mínimo de ejes
            $validationError = $this->validateMinimumAxles($axles);
            if ($validationError !== null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => $validationError, 'Data' => []];
            }
            
            $model = ($pk !== null) ? VehicleType::findOne(['code' => $pk]) : null;
            $isUpdate = ($model !== null);
            
            if ($model === null) {
                $model = new VehicleType();
            } else {
                // En actualización, el code no se puede cambiar
                // Solo permitir cambiar name y active
                if (isset($data['code']) && $data['code'] !== $pk) {
                    if ($ownTx) {
                        $tx->rollBack();
                    }
                    return ['Success' => 'Error', 'Msg' => 'No se puede modificar el código del tipo de vehículo', 'Data' => []];
                }
            }
            
            // Guardar vehicle_type (remover axles y _csrf antes de setAttributes)
            $modelData = $data;
            unset($modelData['axles'], $modelData['_csrf']);
            $model->setAttributes($modelData);
            if (!$model->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => implode('; ', array_merge(...array_values($model->getErrors()))), 'Data' => []];
            }
            
            // Si es actualización, eliminar ejes removidos
            if ($isUpdate) {
                $deleteErrors = $this->deleteRemovedAxles($model->code, $axles, $tx);
                if (!empty($deleteErrors)) {
                    if ($ownTx) {
                        $tx->rollBack();
                    }
                    return ['Success' => 'Error', 'Msg' => implode("\n", $deleteErrors), 'Data' => []];
                }
            }
            
            // Guardar ejes
            $axlesSaved = $this->saveAxles($model->code, $axles, $tx);
            if (!$axlesSaved) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                \Yii::error("saveAxles returned false for code={$model->code}", __METHOD__);
                return ['Success' => 'Error', 'Msg' => 'Error al guardar configuración de ejes. Revise el log para detalles.', 'Data' => []];
            }
            
            if ($ownTx) {
                $tx->commit();
            }
            
            return ['Success' => 'Ok', 'Msg' => 'Registro guardado', 'Data' => $model->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            return ['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []];
        }
    }

    public function delete(string $pk, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        
        try {
            $model = VehicleType::findOne(['code' => $pk]);
            
            if ($model === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Registro no encontrado', 'Data' => []];
            }
            
            // Validar que no esté en uso
            if ($this->isVehicleTypeUsed($pk)) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'No se puede eliminar: el tipo de vehículo está asignado a uno o más vehículos.', 'Data' => []];
            }
            
            // Eliminar ejes (CASCADE debe hacerlo automáticamente, pero lo hacemos explícito)
            VehicleTypeAxle::deleteAll(['code' => $pk]);
            
            // Eliminar tipo de vehículo
            if (!$model->delete()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Error al eliminar registro', 'Data' => []];
            }
            
            if ($ownTx) {
                $tx->commit();
            }
            
            return ['Success' => 'Ok', 'Msg' => 'Registro eliminado', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            return ['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []];
        }
    }
    
    public function getFormOptions(): array
    {
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => [
            'active_options' => VehicleType::getActiveOptions(),
        ]];
    }
    
    /**
     * Obtiene la configuración de ejes y posiciones de llantas para un tipo de vehículo
     * 
     * @param string $vehicleTypeCode Código del tipo de vehículo
     * @param string|null $vehicleCode Código de la unidad (para validar si puede cambiar tipo)
     * @return array ['Success' => 'Ok'|'Error', 'Msg' => string, 'Data' => array]
     */
    public function getAxleConfiguration(string $vehicleTypeCode, ?string $vehicleCode = null): array
    {
        try {
            // Validar que el tipo de vehículo existe
            $vehicleType = VehicleType::findOne($vehicleTypeCode);
            if (!$vehicleType) {
                return ['Success' => 'Error', 'Msg' => 'Tipo de vehículo no encontrado', 'Data' => []];
            }
            
            // Si es una unidad existente, validar que no tenga llantas asignadas
            if ($vehicleCode) {
                $hasTires = VehicleTire::find()
                    ->where(['vehicle_code' => $vehicleCode])
                    ->andWhere(['IS NOT', 'tire_code', null])
                    ->andWhere(['<>', 'tire_code', ''])
                    ->exists();
                
                if ($hasTires) {
                    return [
                        'Success' => 'Error', 
                        'Msg' => 'No se puede cambiar el tipo de unidad porque tiene llantas asignadas. Debe desasignar todas las llantas primero.', 
                        'Data' => ['has_tires' => true]
                    ];
                }
            }
            
            // Consultar configuración de ejes con posiciones
            // ⚠️ IMPORTANTE: No usar ActiveRecord aquí porque tiene problema con PRIMARY KEY duplicada
            // cuando se hace JOIN. Usar SQL directo para evitar que Yii2 agrupe por PK.
            // ✅ MEJORA: Incluir nombres de eje y posición para UI más amigable
            $sql = "SELECT vta.code, vta.line_num, vta.axle_type_code, 
                           axlc.line_num as config_line_num, axlc.pos_code,
                           at.name as axle_type_name,
                           CASE
                               WHEN axlc.pos_code = 'LI' THEN 'IZQUIERDA INTERNA'
                               WHEN axlc.pos_code = 'LO' THEN 'IZQUIERDA EXTERNA'
                               WHEN axlc.pos_code = 'RI' THEN 'DERECHA INTERNA'
                               WHEN axlc.pos_code = 'RO' THEN 'DERECHA EXTERNA'
                               WHEN axlc.pos_code = 'LS' THEN 'IZQUIERDA SOLA'
                               WHEN axlc.pos_code = 'RS' THEN 'DERECHA SOLA'
                               WHEN axlc.pos_code = 'LI1' THEN 'IZQUIERDA INTERNA EJE 1'
                               WHEN axlc.pos_code = 'LO1' THEN 'IZQUIERDA EXTERNA EJE 1'
                               WHEN axlc.pos_code = 'RI1' THEN 'DERECHA INTERNA EJE 1'
                               WHEN axlc.pos_code = 'RO1' THEN 'DERECHA EXTERNA EJE 1'
                               WHEN axlc.pos_code = 'LI2' THEN 'IZQUIERDA INTERNA EJE 2'
                               WHEN axlc.pos_code = 'LO2' THEN 'IZQUIERDA EXTERNA EJE 2'
                               WHEN axlc.pos_code = 'RI2' THEN 'DERECHA INTERNA EJE 2'
                               WHEN axlc.pos_code = 'RO2' THEN 'DERECHA EXTERNA EJE 2'
                               WHEN axlc.pos_code = 'LI3' THEN 'IZQUIERDA INTERNA EJE 3'
                               WHEN axlc.pos_code = 'LO3' THEN 'IZQUIERDA EXTERNA EJE 3'
                               WHEN axlc.pos_code = 'RI3' THEN 'DERECHA INTERNA EJE 3'
                               WHEN axlc.pos_code = 'RO3' THEN 'DERECHA EXTERNA EJE 3'
                               ELSE ''
                           END AS position_name
                    FROM vehicle_type_axle vta
                    INNER JOIN axle_type_config axlc ON vta.axle_type_code = axlc.code
                    LEFT JOIN axle_type at ON vta.axle_type_code = at.code
                    WHERE vta.code = :vehicleTypeCode
                    ORDER BY vta.line_num ASC, CAST(axlc.line_num AS INTEGER) ASC";
            
            $query = \Yii::$app->db->createCommand($sql, [
                ':vehicleTypeCode' => $vehicleTypeCode
            ])->queryAll();
            
            if (empty($query)) {
                return [
                    'Success' => 'Error', 
                    'Msg' => 'No se encontró configuración de ejes para este tipo de vehículo', 
                    'Data' => []
                ];
            }
            
            // Construir array de posiciones con line_num autoincremental
            $positions = [];
            $lineNum = 1;
            
            foreach ($query as $row) {
                $positions[] = [
                    'line_num' => $lineNum++,
                    'axle_line_num' => (int)$row['line_num'],
                    'axle_type_code' => $row['axle_type_code'],
                    'axle_type_name' => $row['axle_type_name'],  // ✅ NUEVO: Nombre del tipo de eje
                    'position_code' => $row['pos_code'],
                    'position_name' => $row['position_name'],    // ✅ NUEVO: Nombre de la posición
                    'tire_code' => null,
                    'install_date' => null,
                    'install_km' => null,
                    'record_km' => null,
                ];
            }
            
            return [
                'Success' => 'Ok',
                'Msg' => 'Configuración obtenida correctamente',
                'Data' => [
                    'positions' => $positions,
                    'total_positions' => count($positions),
                    'can_change' => true,
                ]
            ];
            
        } catch (\Throwable $e) {
            \Yii::error([
                'message' => 'Error en VehicleTypeService::getAxleConfiguration',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'vehicleTypeCode' => $vehicleTypeCode,
                'vehicleCode' => $vehicleCode,
            ], __METHOD__);
            
            return [
                'Success' => 'Error',
                'Msg' => 'Error al obtener configuración de ejes. Intente nuevamente.',
                'Data' => []
            ];
        }
    }
}
