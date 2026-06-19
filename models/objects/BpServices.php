<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\Bp;
use app\models\tables\GroupSn;
use app\models\tables\Currency;
use app\models\tables\PaymentConditions;
use app\models\tables\PaymentMethods;
use app\models\tables\CfdiUseSn;
use app\models\tables\CfdiRegimenFiscal;
use app\models\tables\Vendors;
use yii\db\Transaction;

class BpServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = Bp::find()->alias('bp')->select(['bp.*', 'gs.name as card_group_name', 'c.name as currency_name'])
                ->leftJoin('group_sn gs', 'bp.card_group = gs.code')
                ->leftJoin('currency c', 'bp.currency = c.code');

            if (!empty($filters['active'])) $query->andWhere(['bp.active' => $filters['active']]);
            if (!empty($filters['cardtype'])) $query->andWhere(['bp.cardtype' => $filters['cardtype']]);
            if (!empty($filters['search'])) {
                $query->andWhere(['or',
                    ['like', 'bp.cardcode', $filters['search']],
                    ['like', 'bp.cardname', $filters['search']],
                    ['like', 'bp.email', $filters['search']],
                ]);
            }

            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['bp.cardcode' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al listar socios de negocio';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function get(string $pk): array
    {
        $m = Bp::findOne(['cardcode' => $pk]);
        if ($m === null) return ['Success' => 'Error', 'Msg' => 'Socio no encontrado', 'Data' => []];
        $data = $m->toArray();
        $data['contacts'] = $m->bpContacts;
        $data['addresses'] = $m->bpAddresses;
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $data];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $pk = $data['cardcode'] ?? null;
            $m = ($pk !== null) ? Bp::findOne(['cardcode' => $pk]) : null;
            if ($m === null) $m = new Bp();
            $m->setAttributes($data);
            if (!$m->save()) {
                if ($ownTx) $tx->rollBack();
                $errs = [];
                foreach ($m->getErrors() as $e) $errs = array_merge($errs, $e);
                return ['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []];
            }
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Socio guardado', 'Data' => $m->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al guardar socio';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function delete(string $pk, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $m = Bp::findOne(['cardcode' => $pk]);
            if ($m === null) {
                if ($ownTx) $tx->rollBack();
                return ['Success' => 'Error', 'Msg' => 'Socio no encontrado', 'Data' => []];
            }
            $m->delete();
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Socio eliminado', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al eliminar socio';
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
                'group_sn' => GroupSn::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
                'currencies' => Currency::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
                'payment_conditions' => PaymentConditions::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
                'payment_methods' => PaymentMethods::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
                'cfdi_use' => CfdiUseSn::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
                'cfdi_regimen' => CfdiRegimenFiscal::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
                'vendors' => Vendors::find()->select(['code', 'name'])->where(['active' => 'Y'])->asArray()->all(),
            ],
        ];
    }
}
