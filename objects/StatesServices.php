<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\States;
use app\models\tables\Country;
use yii\db\Transaction;

class StatesServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = States::find()->alias('s')->select(['s.*', 'c.name as country_name'])->leftJoin('country c', 's.country = c.code');
            if (!empty($filters['active'])) $query->andWhere(['s.active' => $filters['active']]);
            if (!empty($filters['country'])) $query->andWhere(['s.country' => $filters['country']]);
            if (!empty($filters['search'])) {
                $query->andWhere(['or', ['like', 's.code', $filters['search']], ['like', 's.name', $filters['search']]]);
            }
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['s.country' => SORT_ASC, 's.name' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al listar estados';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function get(string $code, string $country): array
    {
        $model = States::findOne(['code' => $code, 'country' => $country]);
        if ($model === null) return ['Success' => 'Error', 'Msg' => 'Estado no encontrado', 'Data' => []];
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $model->toArray()];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $code = $data['code'] ?? null;
            $country = $data['country'] ?? null;
            $model = ($code !== null && $country !== null) ? States::findOne(['code' => $code, 'country' => $country]) : null;
            if ($model === null) $model = new States();
            $model->setAttributes($data);
            if (!$model->save()) {
                if ($ownTx) $tx->rollBack();
                $errs = [];
                foreach ($model->getErrors() as $e) $errs = array_merge($errs, $e);
                return ['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []];
            }
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Estado guardado', 'Data' => $model->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al guardar estado';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function delete(string $code, string $country, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $model = States::findOne(['code' => $code, 'country' => $country]);
            if ($model === null) {
                if ($ownTx) $tx->rollBack();
                return ['Success' => 'Error', 'Msg' => 'Estado no encontrado', 'Data' => []];
            }
            $model->delete();
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Estado eliminado', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al eliminar estado';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function getFormOptions(): array
    {
        return [
            'Success' => 'Ok',
            'Msg' => '',
            'Data' => [
                'countries' => Country::find()->select(['code', 'name'])->where(['active' => 'Y'])->orderBy(['name' => SORT_ASC])->asArray()->all(),
            ],
        ];
    }
}
