<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\VehicleTypeAxle;
use yii\db\Transaction;

class VehicleTypeAxleServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = VehicleTypeAxle::find();
            if (!empty($filters['code'])) {
                $query->andWhere(['code' => $filters['code']]);
            }
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['code' => SORT_ASC, 'line_num' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            return ['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []];
        }
    }

    public function get(string $code, int $lineNum): array
    {
        $model = VehicleTypeAxle::findOne(['code' => $code, 'line_num' => $lineNum]);
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
                ? VehicleTypeAxle::findOne(['code' => $code, 'line_num' => $lineNum]) 
                : null;
            if ($model === null) {
                $model = new VehicleTypeAxle();
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

    public function delete(string $code, int $lineNum, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $model = VehicleTypeAxle::findOne(['code' => $code, 'line_num' => $lineNum]);
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
