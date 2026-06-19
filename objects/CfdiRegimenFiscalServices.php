<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\CfdiRegimenFiscal;
use yii\db\Transaction;

class CfdiRegimenFiscalServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = CfdiRegimenFiscal::find();
            if (!empty($filters['active'])) $query->andWhere(['active' => $filters['active']]);
            if (!empty($filters['search'])) $query->andWhere(['or', ['like', 'code', $filters['search']], ['like', 'name', $filters['search']]]);
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['code' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al listar regímenes CFDI';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }
    public function get(string $pk): array
    {
        $m = CfdiRegimenFiscal::findOne(['code' => $pk]);
        if ($m === null) return ['Success' => 'Error', 'Msg' => 'Registro no encontrado', 'Data' => []];
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $m->toArray()];
    }
    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $pk = $data['code'] ?? null;
            $m = ($pk !== null) ? CfdiRegimenFiscal::findOne(['code' => $pk]) : null;
            if ($m === null) $m = new CfdiRegimenFiscal();
            $m->setAttributes($data);
            if (!$m->save()) {
                if ($ownTx) $tx->rollBack();
                $errs = [];
                foreach ($m->getErrors() as $e) $errs = array_merge($errs, $e);
                return ['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []];
            }
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Registro guardado', 'Data' => $m->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al guardar régimen CFDI';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }
    public function delete(string $pk, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $m = CfdiRegimenFiscal::findOne(['code' => $pk]);
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
            $msg = 'Error al eliminar régimen CFDI';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }
    public function getFormOptions(): array { return ['Success' => 'Ok', 'Msg' => '', 'Data' => []]; }
}
