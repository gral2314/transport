<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\Items;
use app\models\tables\GroupItems;
use app\models\tables\Tire;
use yii\db\Transaction;

class ItemsServices
{
    public function list(array $filters = []): array
    {
        try {
            $q = Items::find()->alias('i')->select(['i.*', 'g.name as group_name', 't.tire_name as tire_name'])
                ->leftJoin('group_items g', 'i.item_group = g.code')
                ->leftJoin('tire t', 'i.tire_code = t.tire_code');
            if (!empty($filters['active'])) $q->andWhere(['i.active' => $filters['active']]);
            if (!empty($filters['search'])) {
                $q->andWhere(['or', ['like', 'i.itemcode', $filters['search']], ['like', 'i.itemname', $filters['search']]]);
            }
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $q->orderBy(['i.itemcode' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? ('Error al listar artículos: ' . $e->getMessage()) : 'Error al listar artículos', 'Data' => []];
        }
    }
    public function get(string $pk): array
    {
        $m = Items::findOne(['itemcode' => $pk]);
        if ($m === null) return ['Success' => 'Error', 'Msg' => 'Artículo no encontrado', 'Data' => []];
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $m->toArray()];
    }
    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $pk = $data['itemcode'] ?? null;
            $m = ($pk !== null) ? Items::findOne(['itemcode' => $pk]) : null;
            if ($m === null) $m = new Items();
            $m->setAttributes($data);
            if (!$m->save()) {
                if ($ownTx) $tx->rollBack();
                $errs = [];
                foreach ($m->getErrors() as $e) $errs = array_merge($errs, $e);
                return ['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []];
            }
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Artículo guardado', 'Data' => $m->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? ('Error al guardar artículo: ' . $e->getMessage()) : 'Error al guardar artículo', 'Data' => []];
        }
    }
    public function delete(string $pk, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $m = Items::findOne(['itemcode' => $pk]);
            if ($m === null) {
                if ($ownTx) $tx->rollBack();
                return ['Success' => 'Error', 'Msg' => 'Artículo no encontrado', 'Data' => []];
            }
            $m->delete();
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Artículo eliminado', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? ('Error al eliminar artículo: ' . $e->getMessage()) : 'Error al eliminar artículo', 'Data' => []];
        }
    }
    public function getFormOptions(): array
    {
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => [
            'groups' => GroupItems::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
            'tires' => Tire::find()->select(['tire_code as code', 'tire_name as name'])->where(['active' => 'Y'])->asArray()->all(),
        ]];
    }
}
