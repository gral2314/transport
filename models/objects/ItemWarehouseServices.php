<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\ItemWarehouse;
use app\models\tables\Warehouse;
use yii\db\Transaction;

class ItemWarehouseServices
{
    public function list(array $filters = []): array
    {
        try {
            $q = ItemWarehouse::find()->alias('iw')->select(['iw.*', 'w.name as warehouse_name'])
                ->leftJoin('warehouse w', 'iw.warehouse_code = w.code');
            if (!empty($filters['itemcode'])) $q->andWhere(['iw.itemcode' => $filters['itemcode']]);
            if (!empty($filters['warehouse_code'])) $q->andWhere(['iw.warehouse_code' => $filters['warehouse_code']]);
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $q->orderBy(['iw.itemcode' => SORT_ASC, 'iw.warehouse_code' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? ('Error al listar inventario por almacén: ' . $e->getMessage()) : 'Error al listar inventario por almacén', 'Data' => []];
        }
    }
    public function get(string $itemcode, string $warehouseCode): array
    {
        $m = ItemWarehouse::findOne(['itemcode' => $itemcode, 'warehouse_code' => $warehouseCode]);
        if ($m === null) return ['Success' => 'Error', 'Msg' => 'Registro no encontrado', 'Data' => []];
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $m->toArray()];
    }
    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $item = $data['itemcode'] ?? null;
            $wh = $data['warehouse_code'] ?? null;
            $m = ($item !== null && $wh !== null) ? ItemWarehouse::findOne(['itemcode' => $item, 'warehouse_code' => $wh]) : null;
            if ($m === null) $m = new ItemWarehouse();
            $m->setAttributes($data);
            if (!$m->save()) {
                if ($ownTx) $tx->rollBack();
                $errs = [];
                foreach ($m->getErrors() as $e) $errs = array_merge($errs, $e);
                return ['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []];
            }
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Inventario guardado', 'Data' => $m->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? ('Error al guardar inventario: ' . $e->getMessage()) : 'Error al guardar inventario', 'Data' => []];
        }
    }
    public function delete(string $itemcode, string $warehouseCode, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $m = ItemWarehouse::findOne(['itemcode' => $itemcode, 'warehouse_code' => $warehouseCode]);
            if ($m === null) {
                if ($ownTx) $tx->rollBack();
                return ['Success' => 'Error', 'Msg' => 'Registro no encontrado', 'Data' => []];
            }
            $m->delete();
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Registro eliminado', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? ('Error al eliminar inventario: ' . $e->getMessage()) : 'Error al eliminar inventario', 'Data' => []];
        }
    }
    public function getFormOptions(): array
    {
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => [
            'warehouses' => Warehouse::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
        ]];
    }
}
