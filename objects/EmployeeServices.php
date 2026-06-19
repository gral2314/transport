<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\Employee;
use app\models\tables\EmployeeDocument;
use app\models\tables\EmployeeRole;
use app\models\tables\PositionCatalog;
use app\models\tables\AreaCatalog;
use app\models\tables\BranchCatalog;
use app\models\tables\EmployeeTypeCatalog;
use app\models\tables\DocumentTypeCatalog;
use app\models\tables\RoleCatalog;
use yii\db\Transaction;

class EmployeeServices
{
    /**
     * Limpia los datos: convierte strings vacías a NULL para evitar violaciones de FK
     */
    private function cleanData(array $data): array
    {
        $fkFields = [
            'position_code', 'area_code', 'branch_code', 'employee_type_code',
            'direct_manager_code', 'user_id'
        ];
        
        foreach ($fkFields as $field) {
            if (isset($data[$field]) && trim((string)$data[$field]) === '') {
                $data[$field] = null;
            }
        }
        
        return $data;
    }

    public function list(array $filters = []): array
    {
        try {
            $query = Employee::find()
                ->alias('e')
                ->select([
                    'e.*',
                    'p.name AS position_name',
                    'a.name AS area_name',
                    'b.name AS branch_name',
                    'et.name AS employee_type_name',
                    'mgr.first_name AS manager_first_name',
                    'mgr.last_name AS manager_last_name',
                    new \yii\db\Expression("
                        CASE
                            WHEN e.employee_status = 'ACTIVE' THEN 'Activo'
                            WHEN e.employee_status = 'INACTIVE' THEN 'Inactivo'
                            WHEN e.employee_status = 'SUSPENDED' THEN 'Suspendido'
                            WHEN e.employee_status = 'VACATION' THEN 'Vacaciones'
                            ELSE e.employee_status
                        END AS employee_status_name
                    "),
                ])
                ->leftJoin('position_catalog p', 'e.position_code = p.code')
                ->leftJoin('area_catalog a', 'e.area_code = a.code')
                ->leftJoin('branch_catalog b', 'e.branch_code = b.code')
                ->leftJoin('employee_type_catalog et', 'e.employee_type_code = et.code')
                ->leftJoin('employee mgr', 'e.direct_manager_code = mgr.employee_code');
            
            if (!empty($filters['employee_status'])) {
                $query->andWhere(['e.employee_status' => $filters['employee_status']]);
            }
            if (!empty($filters['active'])) {
                $query->andWhere(['e.active' => $filters['active']]);
            }
            if (!empty($filters['position_code'])) {
                $query->andWhere(['e.position_code' => $filters['position_code']]);
            }
            if (!empty($filters['has_user_id'])) {
                $query->andWhere(['is not', 'e.user_id', null]);
            }
            if (!empty($filters['area_code'])) {
                $query->andWhere(['e.area_code' => $filters['area_code']]);
            }
            if (!empty($filters['search'])) {
                $query->andWhere(['or',
                    ['like', 'e.employee_code', $filters['search']],
                    ['like', 'e.first_name', $filters['search']],
                    ['like', 'e.last_name', $filters['search']],
                    ['like', 'e.curp', $filters['search']],
                    ['like', 'e.email', $filters['search']],
                ]);
            }
            
            return [
                'Success' => 'Ok',
                'Msg' => '',
                'Data' => $query->orderBy(['e.employee_code' => SORT_ASC])->asArray()->all()
            ];
        } catch (\Throwable $e) {
            \Yii::error([
                'message' => 'Error al listar empleados',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $filters
            ], __METHOD__);
            
            $errorMsg = 'Error al obtener la lista de empleados';
            if (YII_DEBUG) {
                $errorMsg .= ': ' . $e->getMessage();
            }
            
            return ['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []];
        }
    }

    /**
     * Obtiene empleados con puesto de mecánico (position_code = 'MECHANIC'), estatus activo,
     * campo active = 'Y' y que tengan user_id asignado.
     * Método reutilizable en todo el sistema para poblar selects de técnicos asignados.
     */
    public function getMechanics(): array
    {
        return $this->list([
            'position_code' => 'MECHANIC',
            'employee_status' => 'ACTIVE',
            'active' => 'Y',
            'has_user_id' => true,
        ]);
    }

    public function get(string $pk): array
    {
        try {
            $model = Employee::findOne(['employee_code' => $pk]);
            if ($model === null) {
                return ['Success' => 'Error', 'Msg' => 'Empleado no encontrado', 'Data' => []];
            }
            
            $data = $model->toArray();
            
            // Obtener documentos del empleado
            $data['documents'] = EmployeeDocument::find()
                ->alias('ed')
                ->select([
                    'ed.*',
                    'dt.name AS document_type_name',
                ])
                ->leftJoin('document_type_catalog dt', 'ed.document_type_code = dt.code')
                ->where(['ed.employee_code' => $pk])
                ->orderBy(['ed.document_type_code' => SORT_ASC])
                ->asArray()
                ->all();
            
            // Obtener roles del empleado
            $data['roles'] = EmployeeRole::find()
                ->alias('er')
                ->select([
                    'er.*',
                    'rc.name AS role_name',
                ])
                ->leftJoin('role_catalog rc', 'er.role_code = rc.code')
                ->where(['er.employee_code' => $pk])
                ->orderBy(['rc.name' => SORT_ASC])
                ->asArray()
                ->all();
            
            return ['Success' => 'Ok', 'Msg' => '', 'Data' => $data];
        } catch (\Throwable $e) {
            \Yii::error([
                'message' => 'Error al obtener empleado',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pk' => $pk
            ], __METHOD__);
            
            $errorMsg = 'Error al obtener los datos del empleado';
            if (YII_DEBUG) {
                $errorMsg .= ': ' . $e->getMessage();
            }
            
            return ['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []];
        }
    }

    public function save(array $data, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        
        try {
            $data = $this->cleanData($data);
            
            $pk = $data['employee_code'] ?? null;
            $model = ($pk !== null) ? Employee::findOne(['employee_code' => $pk]) : null;
            if ($model === null) {
                $model = new Employee();
            }
            
            $model->setAttributes($data);
            
            // Asignar campos de auditoría
            $userId = \Yii::$app->user->identity->id ?? null;
            if ($model->isNewRecord) {
                $model->createuser = $userId;
            }
            $model->updateuser = $userId;
            
            if (!$model->save()) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                $errs = [];
                foreach ($model->getErrors() as $e) {
                    $errs = array_merge($errs, $e);
                }
                return ['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []];
            }

            // Manejo de documentos (si vienen en payload)
            if (!empty($data['documents']) && is_array($data['documents'])) {
                // Borrar y reinsertar (en el mismo tx)
                EmployeeDocument::deleteAll(['employee_code' => $model->employee_code]);
                $docService = new EmployeeDocumentService();
                foreach ($data['documents'] as $doc) {
                    $doc['employee_code'] = $model->employee_code;
                    $res = $docService->save($doc, $tx);
                    if (($res['Success'] ?? '') !== 'Ok') {
                        if ($ownTx) $tx->rollBack();
                        return $res;
                    }
                }
            }

            // Manejo de roles (si vienen en payload)
            if (isset($data['roles']) && is_array($data['roles'])) {
                $roleService = new EmployeeRoleService();
                $rolesCodes = [];
                // Si roles vienen como array de objetos [{role_code: 'DRIVER'}, ...] o como array de códigos
                foreach ($data['roles'] as $r) {
                    if (is_array($r) && isset($r['role_code'])) $rolesCodes[] = $r['role_code'];
                    elseif (is_string($r)) $rolesCodes[] = $r;
                }
                $res = $roleService->saveBatch($model->employee_code, $rolesCodes, $tx);
                if (($res['Success'] ?? '') !== 'Ok') {
                    if ($ownTx) $tx->rollBack();
                    return $res;
                }
            }

            if ($ownTx) {
                $tx->commit();
            }

            // Recargar datos compuestos
            $dataOut = $this->get($model->employee_code);
            return ['Success' => 'Ok', 'Msg' => 'Empleado guardado correctamente', 'Data' => $dataOut['Data'] ?? $model->toArray()];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            
            \Yii::error([
                'message' => 'Error en EmployeeService::save',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ], __METHOD__);
            
            $errorMsg = 'Error al guardar el empleado';
            if (YII_DEBUG) {
                $errorMsg .= ': ' . $e->getMessage();
            }
            
            return ['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []];
        }
    }

    public function delete(string $pk, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;
        
        try {
            $model = Employee::findOne(['employee_code' => $pk]);
            if ($model === null) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'Empleado no encontrado', 'Data' => []];
            }
            
            $model->delete();
            
            if ($ownTx) {
                $tx->commit();
            }
            
            return ['Success' => 'Ok', 'Msg' => 'Empleado eliminado correctamente', 'Data' => []];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            
            \Yii::error([
                'message' => 'Error al eliminar empleado',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pk' => $pk
            ], __METHOD__);
            
            $errorMsg = 'Error al eliminar el empleado';
            if (YII_DEBUG) {
                $errorMsg .= ': ' . $e->getMessage();
            }
            
            return ['Success' => 'Error', 'Msg' => $errorMsg, 'Data' => []];
        }
    }

    public function getFormOptions(): array
    {
        try {
            return [
                'Success' => 'Ok',
                'Msg' => '',
                'Data' => [
                    'positions' => PositionCatalog::find()
                        ->select(['code', 'name'])
                        ->where(['active' => 'Y'])
                        ->orderBy(['name' => SORT_ASC])
                        ->asArray()
                        ->all(),
                    'areas' => AreaCatalog::find()
                        ->select(['code', 'name'])
                        ->where(['active' => 'Y'])
                        ->orderBy(['name' => SORT_ASC])
                        ->asArray()
                        ->all(),
                    'branches' => BranchCatalog::find()
                        ->select(['code', 'name'])
                        ->where(['active' => 'Y'])
                        ->orderBy(['name' => SORT_ASC])
                        ->asArray()
                        ->all(),
                    'employee_types' => EmployeeTypeCatalog::find()
                        ->select(['code', 'name'])
                        ->where(['active' => 'Y'])
                        ->orderBy(['name' => SORT_ASC])
                        ->asArray()
                        ->all(),
                    'document_types' => DocumentTypeCatalog::find()
                        ->select(['code', 'name'])
                        ->where(['active' => 'Y'])
                        ->orderBy(['name' => SORT_ASC])
                        ->asArray()
                        ->all(),
                    'roles' => RoleCatalog::find()
                        ->select(['code', 'name'])
                        ->where(['active' => 'Y'])
                        ->orderBy(['name' => SORT_ASC])
                        ->asArray()
                        ->all(),
                    'managers' => Employee::find()
                        ->select(['employee_code as code', new \yii\db\Expression("CONCAT(first_name, ' ', last_name) as name")])
                        ->where(['active' => 'Y', 'employee_status' => 'ACTIVE'])
                        ->orderBy(['first_name' => SORT_ASC])
                        ->asArray()
                        ->all(),
                    'users' => \app\models\system\Users::find()
                        ->select(['id as code', new \yii\db\Expression("CONCAT(usercode, ' - ', username) as name")])
                        ->where(['active' => 'Y'])
                        ->orderBy(['username' => SORT_ASC])
                        ->asArray()
                        ->all(),
                    'gender_options' => Employee::getGenderOptions(),
                    'status_options' => Employee::getStatusOptions(),
                    'shift_options' => Employee::getShiftOptions(),
                ]
            ];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => 'Error al obtener opciones del formulario', 'Data' => []];
        }
    }
}
