<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use app\models\tables\Employee;
use app\models\system\User;
use yii\db\ActiveRecord;

class DocTireRepair extends ActiveRecord
{
    public const DOC_STATUS_O = 'O';
    public const DOC_STATUS_C = 'C';

    public const STATUS_PLAN = 'PLAN';
    public const STATUS_LIB = 'LIB';
    public const STATUS_TALLER = 'TALLER';
    public const STATUS_EXEC = 'EXEC';
    public const STATUS_VAL = 'VAL';
    public const STATUS_CLOSE = 'CLOSE';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const CANCELED_Y = 'Y';
    public const CANCELED_N = 'N';

    public const INDEXES = [
        'ux_doc_tire_repair_docnum' => ['columns' => ['docnum'], 'unique' => true],
        'idx_doc_tire_repair_provider_code' => ['columns' => ['provider_code'], 'unique' => false],
        'idx_doc_tire_repair_status' => ['columns' => ['status'], 'unique' => false],
        'idx_doc_tire_repair_doc_status' => ['columns' => ['doc_status'], 'unique' => false],
        'idx_doc_tire_repair_repair_date' => ['columns' => ['repair_date'], 'unique' => false],
        'idx_doc_tire_repair_technician_user_id' => ['columns' => ['technician_user_id'], 'unique' => false],
        'idx_doc_tire_repair_validated_by_user_id' => ['columns' => ['validated_by_user_id'], 'unique' => false],
    ];

    public static function tableName(): string
    {
        return '{{%doc_tire_repair}}';
    }

    public static function primaryKey(): array
    {
        return ['docentry'];
    }

    public function behaviors(): array
    {
        return [
            'audit' => ['class' => AuditBehavior::class],
        ];
    }

    public function rules(): array
    {
        return [
            [['docnum', 'doc_date', 'repair_date'], 'required'],
            [['docentry', 'series_id', 'technician_user_id', 'validated_by_user_id', 'createuser', 'updateuser'], 'integer'],
            [['doc_date', 'repair_date', 'return_date', 'createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['released_at', 'started_at', 'completed_at', 'validated_at', 'cancelled_at', 'createtime', 'updatetime'], 'safe'],
            [['docnum'], 'string', 'max' => 30],
            [['provider_code'], 'string', 'max' => 50],
            [['comments'], 'string', 'max' => 300],
            [['rejection_notes'], 'string'],

            [['docnum'], 'unique'],

            [['doc_status'], 'in', 'range' => [self::DOC_STATUS_O, self::DOC_STATUS_C]],
            [['doc_status'], 'default', 'value' => self::DOC_STATUS_O],
            [['status'], 'in', 'range' => [
                self::STATUS_PLAN,
                self::STATUS_LIB,
                self::STATUS_TALLER,
                self::STATUS_EXEC,
                self::STATUS_VAL,
                self::STATUS_CLOSE,
                self::STATUS_CANCELLED,
            ]],
            [['status'], 'default', 'value' => self::STATUS_PLAN],
            [['canceled'], 'in', 'range' => [self::CANCELED_Y, self::CANCELED_N]],
            [['canceled'], 'default', 'value' => self::CANCELED_N],

            [['series_id'], 'exist', 'skipOnError' => true, 'targetClass' => Series::class, 'targetAttribute' => ['series_id' => 'id']],
            [['provider_code'], 'exist', 'skipOnError' => true, 'targetClass' => Bp::class, 'targetAttribute' => ['provider_code' => 'cardcode']],
            [['technician_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::class, 'targetAttribute' => ['technician_user_id' => 'employee_code']],
            [['validated_by_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::class, 'targetAttribute' => ['validated_by_user_id' => 'employee_code']],
            [['createuser'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['createuser' => 'id']],
            [['updateuser'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updateuser' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'docentry' => 'Docentry',
            'docnum' => 'Número Documento',
            'doc_date' => 'Fecha Documento',
            'doc_status' => 'Estado Documento',
            'status' => 'Estado Operativo',
            'canceled' => 'Cancelado',
            'provider_code' => 'Proveedor/Taller',
            'repair_date' => 'Fecha Reparación',
            'return_date' => 'Fecha Retorno',
            'comments' => 'Comentarios',
            'series_id' => 'Serie',
            'rejection_notes' => 'Notas de Rechazo',
            'technician_user_id' => 'Técnico Asignado',
            'validated_by_user_id' => 'Validado Por',
            'released_at' => 'Liberado En',
            'started_at' => 'Iniciado En',
            'completed_at' => 'Completado En',
            'validated_at' => 'Validado En',
            'cancelled_at' => 'Cancelado En',
            'createdate' => 'Fecha Creación',
            'createtime' => 'Hora Creación',
            'createuser' => 'Creado Por',
            'updatedate' => 'Fecha Actualización',
            'updatetime' => 'Hora Actualización',
            'updateuser' => 'Actualizado Por',
        ];
    }

    public function getSeries(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Series::class, ['id' => 'series_id']);
    }

    public function getProvider(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Bp::class, ['cardcode' => 'provider_code']);
    }

    public function getCreateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'createuser']);
    }

    public function getUpdateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'updateuser']);
    }

    public function getDetails(): \yii\db\ActiveQuery
    {
        return $this->hasMany(DocTireRepairDetail::class, ['docentry' => 'docentry'])->orderBy(['linenum' => SORT_ASC]);
    }

    public function getAttachments(): \yii\db\ActiveQuery
    {
        return $this->hasMany(DocTireRepairAttach::class, ['docentry' => 'docentry'])->orderBy(['linenum' => SORT_ASC]);
    }

    public function getTechnicianUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Employee::class, ['employee_code' => 'technician_user_id']);
    }

    public function getValidatedByUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Employee::class, ['employee_code' => 'validated_by_user_id']);
    }
}
