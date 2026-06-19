<?php

declare(strict_types=1);

namespace app\models\tables;

use yii\db\ActiveRecord;

/**
 * EmployeeTypeCatalog — ActiveRecord para {{%employee_type_catalog}} (Catálogo de tipos de empleado)
 *
 * @property string      $code
 * @property string      $name
 * @property string      $active
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 *
 * @property Employee[] $employees
 */
class EmployeeTypeCatalog extends ActiveRecord
{
    public const ACTIVE_Y = 'Y';
    public const ACTIVE_N = 'N';

    public static function tableName(): string
    {
        return '{{%employee_type_catalog}}';
    }

    public static function primaryKey(): array
    {
        return ['code'];
    }

    public function rules(): array
    {
        return [
            [['code', 'name'], 'required'],
            [['code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 100],
            [['active'], 'string'],
            [['active'], 'in', 'range' => [self::ACTIVE_Y, self::ACTIVE_N]],
            [['createdate', 'updatedate'], 'safe'],
            [['createtime', 'updatetime'], 'safe'],
            [['createuser', 'updateuser'], 'integer'],
            [['code'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'code' => 'Código',
            'name' => 'Tipo de Empleado',
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
    public function getEmployees()
    {
        return $this->hasMany(Employee::class, ['employee_type_code' => 'code']);
    }
}
