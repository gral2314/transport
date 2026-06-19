<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use app\models\system\User;
use yii\db\ActiveRecord;

class DocTireMovement extends ActiveRecord
{
    public const DOC_STATUS_O = 'O';
    public const DOC_STATUS_C = 'C';

    public const STATUS_PLANNED = 'PLANNED';
    public const STATUS_RELEASED = 'RELEASED';
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_PENDING_VALIDATION = 'PENDING_VALIDATION';
    public const STATUS_CLOSED = 'CLOSED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const CANCELED_Y = 'Y';
    public const CANCELED_N = 'N';

    public const PRIORITY_LOW = 'LOW';
    public const PRIORITY_MEDIUM = 'MEDIUM';
    public const PRIORITY_HIGH = 'HIGH';
    public const PRIORITY_URGENT = 'URGENT';

    public const ORIGIN_TYPE_MANUAL = 'MANUAL';
    public const ORIGIN_TYPE_MAINTENANCE = 'MAINTENANCE';
    public const ORIGIN_TYPE_INSPECTION = 'INSPECTION';
    public const ORIGIN_TYPE_REPAIR = 'REPAIR';
    public const ORIGIN_TYPE_WAREHOUSE = 'WAREHOUSE';

    public const INDEXES = [
        'ux_doc_tire_movement_docnum' => ['columns' => ['docnum'], 'unique' => true],
        'idx_doc_tire_movement_status' => ['columns' => ['status'], 'unique' => false],
        'idx_doc_tire_movement_doc_status' => ['columns' => ['doc_status'], 'unique' => false],
        'idx_doc_tire_movement_canceled' => ['columns' => ['canceled'], 'unique' => false],
        'idx_doc_tire_movement_technician_user_id' => ['columns' => ['technician_user_id'], 'unique' => false],
        'idx_doc_tire_movement_validated_by_user_id' => ['columns' => ['validated_by_user_id'], 'unique' => false],
    ];

    public static function tableName(): string
    {
        return '{{%doc_tire_movement}}';
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
            [['docnum', 'doc_date', 'doc_duedate'], 'required'],
            [['docentry', 'base_entry', 'series_id', 'createuser', 'updateuser', 'technician_user_id', 'validated_by_user_id'], 'integer'],
            [['odometer_initial', 'odometer_final'], 'integer'],
            [['doc_date', 'doc_duedate', 'createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
            [['released_at', 'started_at', 'completed_at', 'validated_at', 'cancelled_at'], 'safe'],
            [['docnum'], 'string', 'max' => 30],
            [['comments'], 'string', 'max' => 300],
            [['base_type'], 'string', 'max' => 50],

            [['docnum'], 'unique'],

            [['doc_status'], 'in', 'range' => [self::DOC_STATUS_O, self::DOC_STATUS_C]],
            [['doc_status'], 'default', 'value' => self::DOC_STATUS_O],
            [['status'], 'in', 'range' => [
                self::STATUS_PLANNED, self::STATUS_RELEASED, self::STATUS_IN_PROGRESS,
                self::STATUS_PENDING_VALIDATION, self::STATUS_CLOSED, self::STATUS_CANCELLED,
            ]],
            [['status'], 'default', 'value' => self::STATUS_PLANNED],
            [['canceled'], 'in', 'range' => [self::CANCELED_Y, self::CANCELED_N]],
            [['canceled'], 'default', 'value' => self::CANCELED_N],
            [['priority'], 'in', 'range' => [self::PRIORITY_LOW, self::PRIORITY_MEDIUM, self::PRIORITY_HIGH, self::PRIORITY_URGENT]],
            [['priority'], 'default', 'value' => self::PRIORITY_LOW],
            [['origin_type'], 'in', 'range' => [self::ORIGIN_TYPE_MANUAL, self::ORIGIN_TYPE_MAINTENANCE, self::ORIGIN_TYPE_INSPECTION, self::ORIGIN_TYPE_REPAIR, self::ORIGIN_TYPE_WAREHOUSE]],
            [['origin_type'], 'default', 'value' => self::ORIGIN_TYPE_MANUAL],

            [['series_id'], 'exist', 'skipOnError' => true, 'targetClass' => Series::class, 'targetAttribute' => ['series_id' => 'id']],
            [['technician_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['technician_user_id' => 'id']],
            [['validated_by_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['validated_by_user_id' => 'id']],
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
            'doc_duedate' => 'Fecha Vencimiento',
            'doc_status' => 'Estado Documento',
            'status' => 'Estado Operativo',
            'canceled' => 'Cancelado',
            'comments' => 'Comentarios',
            'technician_user_id' => 'Técnico Asignado',
            'validated_by_user_id' => 'Supervisor que Valida',
            'released_at' => 'Fecha/Hora Liberación',
            'started_at' => 'Fecha/Hora Inicio',
            'completed_at' => 'Fecha/Hora Finalización',
            'odometer_initial' => 'Odómetro Inicial',
            'odometer_final' => 'Odómetro Final',
            'validated_at' => 'Fecha/Hora Validación',
            'cancelled_at' => 'Fecha/Hora Cancelación',
            'priority' => 'Prioridad',
            'origin_type' => 'Tipo Origen',
            'series_id' => 'Serie',
            'base_type' => 'Tipo Base',
            'base_entry' => 'Entrada Base',
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

    public function getTechnicianUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'technician_user_id']);
    }

    public function getValidatedByUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'validated_by_user_id']);
    }

    public function getCreateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'createuser']);
    }

    public function getUpdateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'updateuser']);
    }

    public function getVehicles(): \yii\db\ActiveQuery
    {
        return $this->hasMany(DocTireMovementVehicle::class, ['docentry' => 'docentry'])->orderBy(['linenum' => SORT_ASC]);
    }

    public function getDetails(): \yii\db\ActiveQuery
    {
        return $this->hasMany(DocTireMovementDetail::class, ['docentry' => 'docentry'])->orderBy(['linenum' => SORT_ASC]);
    }

    public function getAttachments(): \yii\db\ActiveQuery
    {
        return $this->hasMany(DocTireMovementAttach::class, ['docentry' => 'docentry'])->orderBy(['linenum' => SORT_ASC]);
    }
    
}
