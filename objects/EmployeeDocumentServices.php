<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\EmployeeDocument;
use app\models\tables\DocumentTypeCatalog;
use yii\db\Transaction;

class EmployeeDocumentServices
{
    public function list(array $filters = []): array
    {
        try {
            $query = EmployeeDocument::find()->alias('ed')->select(['ed.*', 'dt.name as document_type_name'])->leftJoin('document_type_catalog dt', 'ed.document_type_code = dt.code');
            if (!empty($filters['employee_code'])) {
                $query->andWhere(['ed.employee_code' => $filters['employee_code']]);
            }
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $query->orderBy(['ed.document_type_code' => SORT_ASC])->asArray()->all()];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al listar documentos';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function get(string $employeeCode, string $docTypeCode): array
    {
        $model = EmployeeDocument::findOne(['employee_code' => $employeeCode, 'document_type_code' => $docTypeCode]);
        if ($model === null) {
            return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
        }
        return ['Success' => 'Ok', 'Msg' => '', 'Data' => $model->toArray()];
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $employeeCode = $data['employee_code'] ?? null;
            $docType = $data['document_type_code'] ?? null;
            if (empty($employeeCode) || empty($docType)) {
                return ['Success' => 'Error', 'Msg' => 'Faltan claves del documento', 'Data' => []];
            }
            $model = EmployeeDocument::findOne(['employee_code' => $employeeCode, 'document_type_code' => $docType]);
            if ($model === null) {
                $model = new EmployeeDocument();
                $model->employee_code = $employeeCode;
                $model->document_type_code = $docType;
            }
            $model->delivered = $data['delivered'] ?? $model->delivered ?? 'N';
            $model->expiration_date = $data['expiration_date'] ?? $model->expiration_date;
            $model->active = $data['active'] ?? $model->active ?? 'Y';
            $model->notes = $data['notes'] ?? $model->notes;

            if (!$model->save()) {
                if ($ownTx) $tx->rollBack();
                $errs = [];
                foreach ($model->getErrors() as $e) {
                    $errs = array_merge($errs, $e);
                }
                return ['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []];
            }
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Documento guardado', 'Data' => $model->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al guardar documento';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function delete(string $employeeCode, string $docTypeCode, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        try {
            $model = EmployeeDocument::findOne(['employee_code' => $employeeCode, 'document_type_code' => $docTypeCode]);
            if ($model === null) {
                if ($ownTx) $tx->rollBack();
                return ['Success' => 'Error', 'Msg' => 'Documento no encontrado', 'Data' => []];
            }
            $model->delete();
            if ($ownTx) $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Documento eliminado', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) $tx->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            $msg = 'Error al eliminar documento';
            if (YII_DEBUG) $msg .= ': ' . $e->getMessage();
            return ['Success' => 'Error', 'Msg' => $msg, 'Data' => []];
        }
    }

    public function getFormOptions(): array
    {
        try {
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => [
                'document_types' => DocumentTypeCatalog::find()->select(['code','name'])->where(['active'=>'Y'])->asArray()->all(),
            ]];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => 'Error al obtener opciones', 'Data' => []];
        }
    }
}
