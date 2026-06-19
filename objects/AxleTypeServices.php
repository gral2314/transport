<?php

declare(strict_types=1);

namespace app\models\objects;

use yii;
use app\models\tables\AxleType;
use app\models\tables\AxleTypeConfig;
use app\models\tables\VehicleTypeAxle;
use yii\db\Transaction;

class AxleTypeServices
{
    /**
     * Valida que tire_qty sea válido (2, 4, 8 o 12 llantas)
     */
    private function validateTireQty(int $tireQty): ?string
    {
        if (!in_array($tireQty, [2, 4, 8, 12], true)) {
            return 'Cantidad de llantas inválida. Solo se permiten 2, 4, 8 o 12 llantas por eje.';
        }
        return null;
    }

    /**
     * Valida si el eje está asignado a algún tipo de vehículo
     */
    private function isAxleAssigned(string $code): bool
    {
        return VehicleTypeAxle::find()
            ->where(['axle_type_code' => $code])
            ->exists();
    }

    /**
     * Genera configuraciones de llantas según cantidad
     */
    private function generateTireConfigurations(int $tireQty): array
    {
        if ($tireQty === 2) {
            return [
                ['line_num' => '1', 'name' => 'LLANTA IZQUIERDA', 'pos_code' => 'LS'],
                ['line_num' => '2', 'name' => 'LLANTA DERECHA', 'pos_code' => 'RS'],
            ];
        }
        
        if ($tireQty === 4) {
            return [
                ['line_num' => '1', 'name' => 'IZQUIERDA INTERIOR', 'pos_code' => 'LI'],
                ['line_num' => '2', 'name' => 'IZQUIERDA EXTERIOR', 'pos_code' => 'LO'],
                ['line_num' => '3', 'name' => 'DERECHA INTERIOR', 'pos_code' => 'RI'],
                ['line_num' => '4', 'name' => 'DERECHA EXTERIOR', 'pos_code' => 'RO'],
            ];
        }
        
        if ($tireQty === 8) {
            // Tandem dual (2 ejes de 4 llantas)
            return [
                ['line_num' => '1', 'name' => 'IZQUIERDA INTERIOR EJE 1', 'pos_code' => 'LI1'],
                ['line_num' => '2', 'name' => 'IZQUIERDA EXTERIOR EJE 1', 'pos_code' => 'LO1'],
                ['line_num' => '3', 'name' => 'DERECHA INTERIOR EJE 1', 'pos_code' => 'RI1'],
                ['line_num' => '4', 'name' => 'DERECHA EXTERIOR EJE 1', 'pos_code' => 'RO1'],
                ['line_num' => '5', 'name' => 'IZQUIERDA INTERIOR EJE 2', 'pos_code' => 'LI2'],
                ['line_num' => '6', 'name' => 'IZQUIERDA EXTERIOR EJE 2', 'pos_code' => 'LO2'],
                ['line_num' => '7', 'name' => 'DERECHA INTERIOR EJE 2', 'pos_code' => 'RI2'],
                ['line_num' => '8', 'name' => 'DERECHA EXTERIOR EJE 2', 'pos_code' => 'RO2'],
            ];
        }
        
        if ($tireQty === 12) {
            // Tandem triple (3 ejes de 4 llantas)
            return [
                ['line_num' => '1', 'name' => 'IZQUIERDA INTERIOR EJE 1', 'pos_code' => 'LI1'],
                ['line_num' => '2', 'name' => 'IZQUIERDA EXTERIOR EJE 1', 'pos_code' => 'LO1'],
                ['line_num' => '3', 'name' => 'DERECHA INTERIOR EJE 1', 'pos_code' => 'RI1'],
                ['line_num' => '4', 'name' => 'DERECHA EXTERIOR EJE 1', 'pos_code' => 'RO1'],
                ['line_num' => '5', 'name' => 'IZQUIERDA INTERIOR EJE 2', 'pos_code' => 'LI2'],
                ['line_num' => '6', 'name' => 'IZQUIERDA EXTERIOR EJE 2', 'pos_code' => 'LO2'],
                ['line_num' => '7', 'name' => 'DERECHA INTERIOR EJE 2', 'pos_code' => 'RI2'],
                ['line_num' => '8', 'name' => 'DERECHA EXTERIOR EJE 2', 'pos_code' => 'RO2'],
                ['line_num' => '9', 'name' => 'IZQUIERDA INTERIOR EJE 3', 'pos_code' => 'LI3'],
                ['line_num' => '10', 'name' => 'IZQUIERDA EXTERIOR EJE 3', 'pos_code' => 'LO3'],
                ['line_num' => '11', 'name' => 'DERECHA INTERIOR EJE 3', 'pos_code' => 'RI3'],
                ['line_num' => '12', 'name' => 'DERECHA EXTERIOR EJE 3', 'pos_code' => 'RO3'],
            ];
        }

        return [];
    }

    /**
     * Elimina configuraciones existentes de un eje
     */
    private function deleteExistingConfigurations(string $code, Transaction $tx): void
    {
        AxleTypeConfig::deleteAll(['code' => $code]);
    }

    /**
     * Crea configuraciones de llantas para un eje
     */
    private function createConfigurations(string $code, int $tireQty, Transaction $tx): bool
    {
        $configs = $this->generateTireConfigurations($tireQty);
        yii::debug("Generando configuraciones para código {$code} con tire_qty {$tireQty}: " . json_encode($configs));
        foreach ($configs as $config) {
            $model = new AxleTypeConfig();
            $model->code = $code;
            $model->line_num = $config['line_num'];
            $model->name = $config['name'];
            $model->pos_code = $config['pos_code'];
            
            if (!$model->save()) {
                return false;
            }
        }
        
        return true;
    }

    public function list(array $filters = []): array
    {
        try {
            $query = AxleType::find();
            if (!empty($filters['active'])) {
                $query->andWhere(['active' => $filters['active']]);
            }
            if (!empty($filters['search'])) {
                $query->andWhere(['or',
                    ['like', 'code', $filters['search']],
                    ['like', 'name', $filters['search']]
                ]);
            }
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['code' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            return ['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []];
        }
    }

    public function get(string $pk): array
    {
        $model = AxleType::findOne(['code' => $pk]);
        if ($model === null) {
            return ['Success' => 'Error', 'Msg' => 'Registro no encontrado', 'Data' => []];
        }
        
        $data = $model->toArray();
        
        // Incluir configuraciones de llantas
        $configs = AxleTypeConfig::find()
            ->where(['code' => $pk])
            ->orderBy(['line_num' => SORT_ASC])
            ->asArray()
            ->all();
        
        $data['configurations'] = $configs;
        $data['is_assigned'] = $this->isAxleAssigned($pk);
        
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $data];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $pk = $data['code'] ?? null;
            $tireQty = (int)($data['tire_qty'] ?? 0);
            
            // Validar tire_qty
            $validationError = $this->validateTireQty($tireQty);
            if ($validationError !== null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => $validationError, 'Data' => []];
            }
            
            $model = ($pk !== null) ? AxleType::findOne(['code' => $pk]) : null;
            $isUpdate = ($model !== null);
            $oldTireQty = null;
            
            if ($model === null) {
                $model = new AxleType();
            } else {
                // Si es actualización, verificar si cambió tire_qty
                $oldTireQty = $model->tire_qty;
                if ($oldTireQty !== $tireQty) {
                    // Validar que no esté asignado
                    if ($this->isAxleAssigned($pk)) {
                        if ($ownTx) {
                            $tx->rollBack();
                        }
                        return ['Success' => 'Error', 'Msg' => 'No se puede cambiar la cantidad de llantas. Este eje está asignado a uno o más tipos de vehículo.', 'Data' => []];
                    }
                }
            }
            
            $model->setAttributes($data);
            if (!$model->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => implode('; ', array_merge(...array_values($model->getErrors()))), 'Data' => []];
            }
            
            // Si es actualización y cambió tire_qty, eliminar configuraciones antiguas
            if ($isUpdate && $oldTireQty !== null && $oldTireQty !== $tireQty) {
                $this->deleteExistingConfigurations($model->code, $tx);
            }
            
            // Si es nuevo o cambió tire_qty, crear configuraciones
            if (!$isUpdate || ($oldTireQty !== null && $oldTireQty !== $tireQty)) {
                if (!$this->createConfigurations($model->code, $tireQty, $tx)) {
                    if ($ownTx) {
                        $tx->rollBack();
                    }
                    return ['Success' => 'Error', 'Msg' => 'Error al crear configuraciones de llantas', 'Data' => []];
                }
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
            $model = AxleType::findOne(['code' => $pk]);
            if ($model === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Registro no encontrado', 'Data' => []];
            }
            
            // Validar que no esté asignado
            if ($this->isAxleAssigned($pk)) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'No se puede eliminar. Este eje está asignado a uno o más tipos de vehículo.', 'Data' => []];
            }
            
            // Eliminar configuraciones primero (CASCADE debería hacerlo automáticamente)
            $this->deleteExistingConfigurations($pk, $tx);
            
            $model->delete();
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
}
