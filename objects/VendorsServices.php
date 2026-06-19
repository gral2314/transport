<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\Vendors;

class VendorsServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = Vendors::find();
            if (!empty($filters['active'])) $query->andWhere(['active' => $filters['active']]);
            if (!empty($filters['search'])) {
                $query->andWhere(['or', ['like', 'code', $filters['search']], ['like', 'name', $filters['search']]]);
            }
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['code' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al listar vendedores';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function get(string $pk): array
    {
        $model = Vendors::findOne(['code' => $pk]);
        if ($model === null) return ['Success' => 'Error', 'Msg' => 'Vendedor no encontrado', 'Data' => []];
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $model->toArray()];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $pk = $data['code'] ?? null;
            $model = ($pk !== null) ? Vendors::findOne(['code' => $pk]) : null;
            if ($model === null) $model = new Vendors();
            $model->setAttributes($data);
            if (!$model->save()) {
                if ($ownTx) $tx->rollBack();
                $errs = [];
                foreach ($model->getErrors() as $e) $errs = array_merge($errs, $e);
                return ['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []];
            }
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Vendedor guardado', 'Data' => $model->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al guardar vendedor';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function delete(string $pk, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $model = Vendors::findOne(['code' => $pk]);
            if ($model === null) {
                if ($ownTx) $tx->rollBack();
                return ['Success' => 'Error', 'Msg' => 'Vendedor no encontrado', 'Data' => []];
            }
            $model->delete();
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Vendedor eliminado', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al eliminar vendedor';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function getFormOptions(): array
    {
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => []];
    }
}
