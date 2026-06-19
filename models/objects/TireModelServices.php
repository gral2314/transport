<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\TireBrand;
use app\models\tables\TireModel;
use yii\db\Transaction;

class TireModelServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = TireModel::find();
            if (!empty($filters['active'])) {
                $query->andWhere(['active' => $filters['active']]);
            }
            if (!empty($filters['brand_code'])) {
                $query->andWhere(['brand_code' => $filters['brand_code']]);
            }
            if (!empty($filters['search'])) {
                $query->andWhere(['like', 'name', $filters['search']]);
            }
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['brand_code' => SORT_ASC, 'code' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            return ['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []];
        }
    }

    public function get(string $pk): array
    {
        $model = TireModel::findOne(['code' => $pk]);
        if ($model === null) {
            return ['Success' => 'Error', 'Msg' => 'Registro no encontrado', 'Data' => []];
        }
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $model->toArray()];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $pk = $data['code'] ?? null;
            $model = ($pk !== null) ? TireModel::findOne(['code' => $pk]) : null;
            if ($model === null) {
                $model = new TireModel();
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
            $model = TireModel::findOne(['code' => $pk]);
            if ($model === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Registro no encontrado', 'Data' => []];
            }
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

    public function getFormOptions(): array
    {
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => [
            'active_options' => TireModel::getActiveOptions(),
            'brand_list'     => TireBrand::getDropdownList(),
        ]];
    }
}
