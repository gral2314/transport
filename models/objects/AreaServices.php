<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\AreaCatalog;
use yii\db\Transaction;

class AreaServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = AreaCatalog::find();
            
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
            $errorMsg = 'Error al listar áreas';
            if (YII_DEBUG) {
                $errorMsg .= ': ' . $e->getMessage();
            }
            return ['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []];
        }
    }

    public function get(string $pk): array
    {
        $model = AreaCatalog::findOne(['code' => $pk]);
        if ($model === null) {
            return ['Success' => 'Error', 'Msg' => 'Área no encontrada', 'Data' => []];
        }
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $model->toArray()];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        
        try {
            $pk = $data['code'] ?? null;
            $model = ($pk !== null) ? AreaCatalog::findOne(['code' => $pk]) : null;
            if ($model === null) {
                $model = new AreaCatalog();
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
            
            return ['Success' => 'Ok', 'Msg' => 'Área guardada correctamente', 'Data' => $model->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            
            \Yii::error($e->getMessage(), __METHOD__);
            $errorMsg = 'Error al guardar el área';
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
            $model = AreaCatalog::findOne(['code' => $pk]);
            if ($model === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Área no encontrada', 'Data' => []];
            }
            
            $model->delete();
            
            if ($ownTx) {
                $tx->commit();
            }
            
            return ['Success' => 'Ok', 'Msg' => 'Área eliminada correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            
            \Yii::error($e->getMessage(), __METHOD__);
            $errorMsg = 'Error al eliminar el área';
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
