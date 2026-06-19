<?php

declare(strict_types=1);

namespace app\models\tables;

use yii\db\ActiveRecord;

/**
 * EmployeeRole — ActiveRecord para {{%employee_role}} (Relación N roles por empleado)
 *
 * @property string      $employee_code
 * @property string      $role_code
 * @property string      $active
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 *
 * @property Employee     $employee
 * @property RoleCatalog  $role
 */
class EmployeeRole extends ActiveRecord
{
    public const ACTIVE_Y = 'Y';
    public const ACTIVE_N = 'N';

    public static function tableName(): string
    {
        return '{{%employee_role}}';
    }

    public static function primaryKey(): array
    {
        return ['employee_code', 'role_code'];
    }

    public function rules(): array
    {
        return [
            [['employee_code', 'role_code'], 'required'],
            [['employee_code', 'role_code'], 'string', 'max' => 50],
            [['active'], 'string'],
            [['active'], 'in', 'range' => [self::ACTIVE_Y, self::ACTIVE_N]],
            [['createdate', 'updatedate'], 'safe'],
            [['createtime', 'updatetime'], 'safe'],
            [['createuser', 'updateuser'], 'integer'],
            [['employee_code'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::class, 'targetAttribute' => ['employee_code' => 'employee_code']],
            [['role_code'], 'exist', 'skipOnError' => true, 'targetClass' => RoleCatalog::class, 'targetAttribute' => ['role_code' => 'code']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'employee_code' => 'Código de Empleado',
            'role_code' => 'Código de Rol',
            'active' => 'Activo',
            'createdate' => 'Fecha de Creación',
            'createtime' => 'Hora de Creación',
            'createuser' => 'Creado Por',
            'updatedate' => 'Fecha de Actualización',
            'updatetime' => 'Hora de Actualización',
            'updateuser' => 'Actualizado Por',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::class, ['employee_code' => 'employee_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(RoleCatalog::class, ['code' => 'role_code']);
    }
}
