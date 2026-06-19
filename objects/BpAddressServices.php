<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\BpAddress;
use app\models\tables\States;
use app\models\tables\Country;
use yii\db\Transaction;

class BpAddressServices
{
    public function list(array $filters = []): array
    {
        try {
            $q = BpAddress::find()->alias('a')->select(['a.*', 's.name as state_name', 'c.name as country_name'])
                ->leftJoin('states s', 'a.state_code = s.code AND a.country_code = s.country')
                ->leftJoin('country c', 'a.country_code = c.code');
            if (!empty($filters['cardcode'])) $q->andWhere(['a.cardcode' => $filters['cardcode']]);
            if (!empty($filters['active'])) $q->andWhere(['a.active' => $filters['active']]);
            if (!empty($filters['search'])) $q->andWhere(['or', ['like', 'a.address_code', $filters['search']], ['like', 'a.street', $filters['search']], ['like', 'a.city', $filters['search']]]);
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $q->orderBy(['a.cardcode' => SORT_ASC, 'a.address_code' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? ('Error al listar direcciones: ' . $e->getMessage()) : 'Error al listar direcciones', 'Data' => []];
        }
    }
    public function get(string $cardcode, string $addressCode): array
    {
        $m = BpAddress::findOne(['cardcode' => $cardcode, 'address_code' => $addressCode]);
        if ($m === null) return ['Success' => 'Error', 'Msg' => 'Dirección no encontrada', 'Data' => []];
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $m->toArray()];
    }
    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $card = $data['cardcode'] ?? null;
            $addr = $data['address_code'] ?? null;
            $m = ($card !== null && $addr !== null) ? BpAddress::findOne(['cardcode' => $card, 'address_code' => $addr]) : null;
            if ($m === null) $m = new BpAddress();
            $m->setAttributes($data);
            if (!$m->save()) {
                if ($ownTx) $tx->rollBack();
                $errs = [];
                foreach ($m->getErrors() as $e) $errs = array_merge($errs, $e);
                return ['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []];
            }
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Dirección guardada', 'Data' => $m->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? ('Error al guardar dirección: ' . $e->getMessage()) : 'Error al guardar dirección', 'Data' => []];
        }
    }
    public function delete(string $cardcode, string $addressCode, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $m = BpAddress::findOne(['cardcode' => $cardcode, 'address_code' => $addressCode]);
            if ($m === null) {
                if ($ownTx) $tx->rollBack();
                return ['Success' => 'Error', 'Msg' => 'Dirección no encontrada', 'Data' => []];
            }
            $m->delete();
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Dirección eliminada', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? ('Error al eliminar dirección: ' . $e->getMessage()) : 'Error al eliminar dirección', 'Data' => []];
        }
    }
    public function getFormOptions(): array
    {
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => [
            'countries' => Country::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
            'states' => States::find()->select(['code', 'country', 'name'])->where(['active' => 'Y'])->asArray()->all(),
        ]];
    }
}
