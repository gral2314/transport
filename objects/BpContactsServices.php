<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\BpContacts;
use yii\db\Transaction;

class BpContactsServices
{
    public function list(array $filters = []): array
    {
        try {
            $q = BpContacts::find();
            if (!empty($filters['cardcode'])) $q->andWhere(['cardcode' => $filters['cardcode']]);
            if (!empty($filters['active'])) $q->andWhere(['active' => $filters['active']]);
            if (!empty($filters['search'])) $q->andWhere(['or', ['like', 'name', $filters['search']], ['like', 'email', $filters['search']]]);
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $q->orderBy(['cardcode' => SORT_ASC, 'contact_code' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? ('Error al listar contactos: ' . $e->getMessage()) : 'Error al listar contactos', 'Data' => []];
        }
    }
    public function get(string $cardcode, string $contactCode): array
    {
        $m = BpContacts::findOne(['cardcode' => $cardcode, 'contact_code' => $contactCode]);
        if ($m === null) return ['Success' => 'Error', 'Msg' => 'Contacto no encontrado', 'Data' => []];
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $m->toArray()];
    }
    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $card = $data['cardcode'] ?? null;
            $contact = $data['contact_code'] ?? null;
            $m = ($card !== null && $contact !== null) ? BpContacts::findOne(['cardcode' => $card, 'contact_code' => $contact]) : null;
            if ($m === null) $m = new BpContacts();
            $m->setAttributes($data);
            if (!$m->save()) {
                if ($ownTx) $tx->rollBack();
                $errs = [];
                foreach ($m->getErrors() as $e) $errs = array_merge($errs, $e);
                return ['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []];
            }
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Contacto guardado', 'Data' => $m->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? ('Error al guardar contacto: ' . $e->getMessage()) : 'Error al guardar contacto', 'Data' => []];
        }
    }
    public function delete(string $cardcode, string $contactCode, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $m = BpContacts::findOne(['cardcode' => $cardcode, 'contact_code' => $contactCode]);
            if ($m === null) {
                if ($ownTx) $tx->rollBack();
                return ['Success' => 'Error', 'Msg' => 'Contacto no encontrado', 'Data' => []];
            }
            $m->delete();
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Contacto eliminado', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => YII_DEBUG ? ('Error al eliminar contacto: ' . $e->getMessage()) : 'Error al eliminar contacto', 'Data' => []];
        }
    }
    public function getFormOptions(): array { return ['Success' => 'Ok', 'Msg' => '', 'Data' => []]; }
}
