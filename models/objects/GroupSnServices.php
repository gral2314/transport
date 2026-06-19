<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\GroupSn;

class GroupSnServices 
{
    public function list(array $filters = []): array
    {
        try {
            $query = GroupSn::find();
            if (!empty($filters['active'])) {
                $query->andWhere(['active' => $filters['active']]);
            }
            if (!empty($filters['search'])) {
                $query->andWhere(['or',
                    ['like', 'code', $filters['search']],
                    ['like', 'name', $filters['search']],
                    ['like', 'cardtype', $filters['search']],
                ]);
            }

            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['code' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al listar grupos de socios de negocio';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function get(string $pk): array
    {
        $model = GroupSn::findOne(['code' => $pk]);
        if ($model === null) {
            return ['Success' => 'Error', 'Msg' => 'Grupo no encontrado', 'Data' => []];
        }
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $model->toArray()];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $pk = $data['code'] ?? null;
            $model = ($pk !== null) ? GroupSn::findOne(['code' => $pk]) : null;
            if ($model === null) {
                $model = new GroupSn();
            }

            $model->setAttributes($data);

            if (!$model->save()) {
                if ($ownTx) $tx->rollBack();
                $errs = [];
                foreach ($model->getErrors() as $e) $errs = array_merge($errs, $e);
                return ['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []];
            }

            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Grupo guardado', 'Data' => $model->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al guardar el grupo';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function delete(string $pk, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $model = GroupSn::findOne(['code' => $pk]);
            if ($model === null) {
                if ($ownTx) $tx->rollBack();
                return ['Success' => 'Error', 'Msg' => 'Grupo no encontrado', 'Data' => []];
            }
            $model->delete();
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Grupo eliminado', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al eliminar el grupo';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function getFormOptions(): array
    {
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => []];
    }
}
