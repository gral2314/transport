<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\Country;
use yii\db\Transaction;

class CountryServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = Country::find();
            if (!empty($filters['active'])) {
                $query->andWhere(['active' => $filters['active']]);
            }
            if (!empty($filters['search'])) {
                $query->andWhere(['or',
                    ['like', 'code', $filters['search']],
                    ['like', 'name', $filters['search']],
                ]);
            }
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['name' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            return ['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []];
        }
    }

    public function get(string $pk): array
    {
        $model = Country::findOne(['code' => $pk]);
        if ($model === null) {
            return ['Success' => 'Error', 'Msg' => 'País no encontrado', 'Data' => []];
        }
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $model->toArray()];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $pk = $data['code'] ?? null;
            $model = ($pk !== null) ? Country::findOne(['code' => $pk]) : null;
            if ($model === null) {
                $model = new Country();
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
            return ['Success' => 'Ok', 'Msg' => 'País guardado', 'Data' => $model->toArray()];
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
            $model = Country::findOne(['code' => $pk]);
            if ($model === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'País no encontrado', 'Data' => []];
            }
            $model->delete();
            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => 'País eliminado', 'Data' => []];
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
            'active_options' => Country::getActiveOptions(),
        ]];
    }
}
