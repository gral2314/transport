<?php

declare(strict_types=1);

namespace app\models\tables;

use yii\db\ActiveRecord;

/**
 * Employee — ActiveRecord para {{%employee}} (Empleados)
 *
 * @property string      $employee_code
 * @property string      $first_name
 * @property string      $last_name
 * @property string|null $second_last_name
 * @property string|null $curp
 * @property string|null $phone_number
 * @property string|null $email
 * @property string|null $address
 * @property string|null $birth_date
 * @property string|null $gender
 * @property string      $hire_date
 * @property string      $employee_status
 * @property string|null $shift_type
 * @property string      $position_code
 * @property string      $area_code
 * @property string|null $branch_code
 * @property string|null $employee_type_code
 * @property string|null $direct_manager_code
 * @property string      $documentation_complete
 * @property string      $active
 * @property int|null    $user_id
 * @property string|null $notes
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 *
 * @property PositionCatalog       $position
 * @property AreaCatalog           $area
 * @property BranchCatalog|null    $branch
 * @property EmployeeTypeCatalog|null $employeeType
 * @property Employee|null         $directManager
 * @property Employee[]            $subordinates
 * @property \app\models\system\Users|null $user
 * @property EmployeeDocument[]    $employeeDocuments
 * @property EmployeeRole[]        $employeeRoles
 */
class Employee extends ActiveRecord
{
    // gender: M=Masculino, F=Femenino, O=Otro
    public const GENDER_M = 'M';
    public const GENDER_F = 'F';
    public const GENDER_O = 'O';

    // employee_status: ACTIVE, INACTIVE, SUSPENDED, VACATION
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_INACTIVE = 'INACTIVE';
    public const STATUS_SUSPENDED = 'SUSPENDED';
    public const STATUS_VACATION = 'VACATION';

    // shift_type: MORNING, EVENING, NIGHT, MIXED
    public const SHIFT_MORNING = 'MORNING';
    public const SHIFT_EVENING = 'EVENING';
    public const SHIFT_NIGHT = 'NIGHT';
    public const SHIFT_MIXED = 'MIXED';

    public const DOCUMENTATION_COMPLETE_Y = 'Y';
    public const DOCUMENTATION_COMPLETE_N = 'N';

    public const ACTIVE_Y = 'Y';
    public const ACTIVE_N = 'N';

    public static function tableName(): string
    {
        return '{{%employee}}';
    }

    public static function primaryKey(): array
    {
        return ['employee_code'];
    }

    public function rules(): array
    {
        return [
            [['employee_code', 'first_name', 'last_name', 'hire_date', 'position_code', 'area_code'], 'required'],
            [['employee_code'], 'string', 'max' => 50],
            [['first_name', 'last_name', 'second_last_name'], 'string', 'max' => 100],
            [['curp'], 'string', 'max' => 18],
            [['phone_number'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 150],
            [['email'], 'email'],
            [['address'], 'string', 'max' => 300],
            [['notes'], 'string', 'max' => 500],
            [['birth_date', 'hire_date'], 'safe'],
            [['gender'], 'string'],
            [['gender'], 'in', 'range' => [self::GENDER_M, self::GENDER_F, self::GENDER_O]],
            [['employee_status'], 'string'],
            [['employee_status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_SUSPENDED, self::STATUS_VACATION]],
            [['shift_type'], 'string'],
            [['shift_type'], 'in', 'range' => [self::SHIFT_MORNING, self::SHIFT_EVENING, self::SHIFT_NIGHT, self::SHIFT_MIXED]],
            [['position_code', 'area_code', 'branch_code', 'employee_type_code', 'direct_manager_code'], 'string', 'max' => 50],
            [['documentation_complete', 'active'], 'string'],
            [['documentation_complete'], 'in', 'range' => [self::DOCUMENTATION_COMPLETE_Y, self::DOCUMENTATION_COMPLETE_N]],
            [['active'], 'in', 'range' => [self::ACTIVE_Y, self::ACTIVE_N]],
            [['createdate', 'updatedate'], 'safe'],
            [['createtime', 'updatetime'], 'safe'],
            [['createuser', 'updateuser', 'user_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\system\Users::class, 'targetAttribute' => ['user_id' => 'id']],
            [['employee_code'], 'unique'],
            [['position_code'], 'exist', 'skipOnError' => true, 'targetClass' => PositionCatalog::class, 'targetAttribute' => ['position_code' => 'code']],
            [['area_code'], 'exist', 'skipOnError' => true, 'targetClass' => AreaCatalog::class, 'targetAttribute' => ['area_code' => 'code']],
            [['branch_code'], 'exist', 'skipOnError' => true, 'targetClass' => BranchCatalog::class, 'targetAttribute' => ['branch_code' => 'code']],
            [['employee_type_code'], 'exist', 'skipOnError' => true, 'targetClass' => EmployeeTypeCatalog::class, 'targetAttribute' => ['employee_type_code' => 'code']],
            [['direct_manager_code'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::class, 'targetAttribute' => ['direct_manager_code' => 'employee_code']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'employee_code' => 'Código de Empleado',
            'first_name' => 'Nombre',
            'last_name' => 'Apellido Paterno',
            'second_last_name' => 'Apellido Materno',
            'curp' => 'CURP',
            'phone_number' => 'Teléfono',
            'email' => 'Correo Electrónico',
            'address' => 'Dirección',
            'birth_date' => 'Fecha de Nacimiento',
            'gender' => 'Género',
            'hire_date' => 'Fecha de Ingreso',
            'employee_status' => 'Estatus',
            'shift_type' => 'Turno',
            'position_code' => 'Puesto',
            'area_code' => 'Área',
            'branch_code' => 'Sucursal',
            'employee_type_code' => 'Tipo de Empleado',
            'direct_manager_code' => 'Jefe Directo',
            'documentation_complete' => 'Expediente Completo',
            'active' => 'Activo',
            'user_id' => 'Usuario de Sistema',
            'notes' => 'Observaciones',
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
    public function getPosition()
    {
        return $this->hasOne(PositionCatalog::class, ['code' => 'position_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArea()
    {
        return $this->hasOne(AreaCatalog::class, ['code' => 'area_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranch()
    {
        return $this->hasOne(BranchCatalog::class, ['code' => 'branch_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeType()
    {
        return $this->hasOne(EmployeeTypeCatalog::class, ['code' => 'employee_type_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDirectManager()
    {
        return $this->hasOne(Employee::class, ['employee_code' => 'direct_manager_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubordinates()
    {
        return $this->hasMany(Employee::class, ['direct_manager_code' => 'employee_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeDocuments()
    {
        return $this->hasMany(EmployeeDocument::class, ['employee_code' => 'employee_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeRoles()
    {
        return $this->hasMany(EmployeeRole::class, ['employee_code' => 'employee_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\app\models\system\Users::class, ['id' => 'user_id']);
    }

    /**
     * Helpers para opciones de formularios
     */
    public static function getGenderOptions(): array
    {
        return [
            self::GENDER_M => 'Masculino',
            self::GENDER_F => 'Femenino',
            self::GENDER_O => 'Otro',
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
            self::STATUS_SUSPENDED => 'Suspendido',
            self::STATUS_VACATION => 'Vacaciones',
        ];
    }

    public static function getShiftOptions(): array
    {
        return [
            self::SHIFT_MORNING => 'Matutino',
            self::SHIFT_EVENING => 'Vespertino',
            self::SHIFT_NIGHT => 'Nocturno',
            self::SHIFT_MIXED => 'Mixto',
        ];
    }

    public static function getDocumentationCompleteOptions(): array
    {
        return [
            self::DOCUMENTATION_COMPLETE_Y => 'Sí',
            self::DOCUMENTATION_COMPLETE_N => 'No',
        ];
    }

    public static function getActiveOptions(): array
    {
        return [
            self::ACTIVE_Y => 'Sí',
            self::ACTIVE_N => 'No',
        ];
    }
}
