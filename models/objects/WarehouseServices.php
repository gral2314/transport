<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\Warehouse;

class WarehouseServices 
{
    public function list(array $filters = []): array
    {
        try {
            $query = Warehouse::find();
            if (!empty($filters['active'])) $query->andWhere(['active' => $filters['active']]);
            if (!empty($filters['search'])) $query->andWhere(['or', ['like', 'code', $filters['search']], ['like', 'name', $filters['search']]]);
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['code' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al listar almacenes';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }
    public function get(string $pk): array
    {
        $m = Warehouse::findOne(['code' => $pk]);
        if ($m === null) return ['Success' => 'Error', 'Msg' => 'Almacén no encontrado', 'Data' => []];
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $m->toArray()];
    }
    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $pk = $data['code'] ?? null;
            $m = ($pk !== null) ? Warehouse::findOne(['code' => $pk]) : null;
            if ($m === null) $m = new Warehouse();
            $m->setAttributes($data);
            if (!$m->save()) {
                if ($ownTx) $tx->rollBack();
                $errs = [];
                foreach ($m->getErrors() as $e) $errs = array_merge($errs, $e);
                return ['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []];
            }
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Almacén guardado', 'Data' => $m->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al guardar almacén';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }
    public function delete(string $pk, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $m = Warehouse::findOne(['code' => $pk]);
            if ($m === null) {
                if ($ownTx) $tx->rollBack();
                return ['Success' => 'Error', 'Msg' => 'Almacén no encontrado', 'Data' => []];
            }
            $m->delete();
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Almacén eliminado', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al eliminar almacén';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }
    public function getFormOptions(): array { return ['Success' => 'Ok', 'Msg' => '', 'Data' => []]; }
}
