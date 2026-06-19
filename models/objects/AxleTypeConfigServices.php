<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\AxleTypeConfig;
use yii\db\Transaction;

class AxleTypeConfigService
{
    public function list(array $filters = []): array
    {
        try {
            $query = AxleTypeConfig::find();
            if (!empty($filters['code'])) {
                $query->andWhere(['code' => $filters['code']]);
            }
            if (!empty($filters['search'])) {
                $query->andWhere(['or',
                    ['like', 'name', $filters['search']],
                    ['like', 'line_num', $filters['search']]
                ]);
            }
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['code' => SORT_ASC, 'line_num' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            return ['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []];
        }
    }

    public function get(string $code, string $lineNum): array
    {
        $model = AxleTypeConfig::findOne(['code' => $code, 'line_num' => $lineNum]);
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
            $code = $data['code'] ?? null;
            $lineNum = $data['line_num'] ?? null;
            $model = ($code !== null && $lineNum !== null) 
                ? AxleTypeConfig::findOne(['code' => $code, 'line_num' => $lineNum]) 
                : null;
            if ($model === null) {
                $model = new AxleTypeConfig();
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

    public function delete(string $code, string $lineNum, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $model = AxleTypeConfig::findOne(['code' => $code, 'line_num' => $lineNum]);
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
}
