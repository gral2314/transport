<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\PositionCatalog;
use yii\db\Transaction;

class PositionServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = PositionCatalog::find();
            
            if (!empty($filters['active'])) {
                $query->andWhere(['active' => $filters['active']]);
            }
            if (!empty($filters['search'])) {
                $query->andWhere(['or',
                    ['like', 'code', $filters['search']],
                    ['like', 'name', $filters['search']],
                ]);
            }
            
            return [
                'Success' => 'Ok',
                'Msg' => '',
                'Data' => $query->orderBy(['code' => SORT_ASC])->asArray()->all()
            ];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $errorMsg = 'Error al listar puestos';
            if (YII_DEBUG) {
                $errorMsg .= ': ' . $e->getMessage();
            }
            return ['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []];
        }
    }

    public function get(string $pk): array
    {
        $model = PositionCatalog::findOne(['code' => $pk]);
        if ($model === null) {
            return ['Success' => 'Error', 'Msg' => 'Puesto no encontrado', 'Data' => []];
        }
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $model->toArray()];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        
        try {
            $pk = $data['code'] ?? null;
            $model = ($pk !== null) ? PositionCatalog::findOne(['code' => $pk]) : null;
            if ($model === null) {
                $model = new PositionCatalog();
                $model->is_system = 'N'; // Por defecto, los puestos no son de sistema
            }
            
            $model->setAttributes($data);
            
            if (!$model->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return [
                    'Success' => 'Error',
                    'Msg' => implode('; ', array_merge(...array_values($model->getErrors()))),
                    'Data' => []
                ];
            }
            
            if ($ownTx) {
                $tx->commit();
            }
            
            return ['Success' => 'Ok', 'Msg' => 'Puesto guardado correctamente', 'Data' => $model->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            
            \Yii::error($e->getMessage(), __METHOD__);
            $errorMsg = 'Error al guardar el puesto';
            if (YII_DEBUG) {
                $errorMsg .= ': ' . $e->getMessage();
            }
            
            return ['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []];
        }
    }

    public function delete(string $pk, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        
        try {
            $model = PositionCatalog::findOne(['code' => $pk]);
            if ($model === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Puesto no encontrado', 'Data' => []];
            }
            if($model->is_system === 'Y') {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'No se puede eliminar un puesto de sistema', 'Data' => []];
            }
            $model->delete();
            
            if ($ownTx) {
                $tx->commit();
            }
            
            return ['Success' => 'Ok', 'Msg' => 'Puesto eliminado correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            
            \Yii::error($e->getMessage(), __METHOD__);
            $errorMsg = 'Error al eliminar el puesto';
            if (YII_DEBUG) {
                $errorMsg .= ': ' . $e->getMessage();
            }
            
            return ['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []];
        }
    }

    public function getFormOptions(): array
    {
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => []];
    }
}
