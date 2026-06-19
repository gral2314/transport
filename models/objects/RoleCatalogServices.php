<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\RoleCatalog;
use yii\db\Transaction;

class RoleCatalogServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = RoleCatalog::find();
            
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
            $errorMsg = 'Error al listar roles';
            if (YII_DEBUG) {
                $errorMsg .= ': ' . $e->getMessage();
            }
            return ['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []];
        }
    }

    public function get(string $pk): array
    {
        $model = RoleCatalog::findOne(['code' => $pk]);
        if ($model === null) {
            return ['Success' => 'Error', 'Msg' => 'Rol no encontrado', 'Data' => []];
        }
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $model->toArray()];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        
        try {
            $pk = $data['code'] ?? null;
            $model = ($pk !== null) ? RoleCatalog::findOne(['code' => $pk]) : null;
            if ($model === null) {
                $model = new RoleCatalog();
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
            
            return ['Success' => 'Ok', 'Msg' => 'Rol guardado correctamente', 'Data' => $model->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            
            \Yii::error($e->getMessage(), __METHOD__);
            $errorMsg = 'Error al guardar el rol';
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
            $model = RoleCatalog::findOne(['code' => $pk]);
            if ($model === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Rol no encontrado', 'Data' => []];
            }
            if($model->is_system == 'Y'){
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'No se puede eliminar un rol del sistema', 'Data' => []];
            }
            $model->delete();
            
            if ($ownTx) {
                $tx->commit();
            }
            
            return ['Success' => 'Ok', 'Msg' => 'Rol eliminado correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            
            \Yii::error($e->getMessage(), __METHOD__);
            $errorMsg = 'Error al eliminar el rol';
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
