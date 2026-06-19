<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\EmployeeRole;
use app\models\tables\RoleCatalog;
use yii\db\Transaction;

class EmployeeRoleServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = EmployeeRole::find()->alias('er')->select(['er.*', 'r.name as role_name'])->leftJoin('role_catalog r', 'er.role_code = r.code');
            if (!empty($filters['employee_code'])) {
                $query->andWhere(['er.employee_code' => $filters['employee_code']]);
            }
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['er.role_code' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al listar roles';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function saveBatch(string $employeeCode, array $roles, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            // Simple approach: remove existing and insert provided
            EmployeeRole::deleteAll(['employee_code' => $employeeCode]);
            foreach ($roles as $roleCode) {
                $model = new EmployeeRole();
                $model->employee_code = $employeeCode;
                $model->role_code = $roleCode;
                $model->active = 'Y';
                if (!$model->save()) {
                    if ($ownTx) $tx->rollBack();
                    $errs = [];
                    foreach ($model->getErrors() as $e) $errs = array_merge($errs, $e);
                    return ['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []];
                }
            }
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Roles guardados', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al guardar roles';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function getFormOptions(): array
    {
        try {
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => [
                'roles' => RoleCatalog::find()->select(['code','name'])->where(['active'=>'Y'])->asArray()->all(),
            ]];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => 'Error al obtener opciones', 'Data' => []];
        }
    }
}
