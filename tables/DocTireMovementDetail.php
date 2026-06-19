<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use app\models\system\User;
use yii\db\ActiveRecord;

class DocTireMovementDetail extends ActiveRecord
{
    public const MOVEMENT_TYPE_ASSIGN = 'ASSIGN';
    public const MOVEMENT_TYPE_ROTATE = 'ROTATE';
    public const MOVEMENT_TYPE_REMOVE = 'REMOVE';
    public const MOVEMENT_TYPE_TRANSFER = 'TRANSFER';
    public const MOVEMENT_TYPE_REPAIR_SEND = 'REPAIR_SEND';
    public const MOVEMENT_TYPE_REPAIR_RETURN = 'REPAIR_RETURN';
    public const MOVEMENT_TYPE_SCRAP = 'SCRAP';

    public const LINE_STATUS_PENDING = 'PENDING';
    public const LINE_STATUS_EXECUTED = 'EXECUTED';
    public const LINE_STATUS_CANCELED = 'CANCELED';

    public const TIRE_CONDITION_GOOD = 'GOOD';
    public const TIRE_CONDITION_USED = 'USED';
    public const TIRE_CONDITION_DAMAGED = 'DAMAGED';
    public const TIRE_CONDITION_SCRAP = 'SCRAP';
    public const TIRE_CONDITION_REPAIR = 'REPAIR';

    public const PHYSICAL_CONDITION_NW = 'NW';
    public const PHYSICAL_CONDITION_RT = 'RT';
    public const PHYSICAL_CONDITION_GD = 'GD';
    public const PHYSICAL_CONDITION_LW = 'LW';
    public const PHYSICAL_CONDITION_IW = 'IW';
    public const PHYSICAL_CONDITION_SD = 'SD';
    public const PHYSICAL_CONDITION_PU = 'PU';
    public const PHYSICAL_CONDITION_UN = 'UN';

    public const INDEXES = [
        'ux_doc_tire_movement_detail_docentry_linenum' => ['columns' => ['docentry', 'linenum'], 'unique' => true],
        'idx_doc_tire_movement_detail_document' => ['columns' => ['docentry'], 'unique' => false],
        'idx_doc_tire_movement_detail_movement_type' => ['columns' => ['movement_type'], 'unique' => false],
        'idx_doc_tire_movement_detail_tire_code' => ['columns' => ['tire_code'], 'unique' => false],
        'idx_doc_tire_movement_detail_related_tire_code' => ['columns' => ['related_tire_code'], 'unique' => false],
        'idx_doc_tire_movement_detail_vehicle_code_from' => ['columns' => ['vehicle_code_from'], 'unique' => false],
        'idx_doc_tire_movement_detail_vehicle_code_to' => ['columns' => ['vehicle_code_to'], 'unique' => false],
        'idx_doc_tire_movement_detail_line_status' => ['columns' => ['line_status'], 'unique' => false],
        'idx_doc_tire_movement_detail_execution_date' => ['columns' => ['execution_date'], 'unique' => false],
    ];

    public static function tableName(): string
    {
        return '{{%doc_tire_movement_detail}}';
    }

    public static function primaryKey(): array
    {
        return ['id'];
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
            [['docentry', 'linenum', 'movement_type'], 'required'],
            [['id', 'docentry', 'linenum', 'target_entry', 'createuser', 'updateuser'], 'integer'],
            [['tire_km', 'related_tire_km', 'tread_depth'], 'number'],
            [['execution_date', 'createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['execution_time', 'createtime', 'updatetime'], 'safe'],
            [['tire_code', 'related_tire_code', 'vehicle_code_from', 'vehicle_code_to', 'position_from', 'position_to', 'whs_code_from', 'whs_code_to', 'target_type'], 'string', 'max' => 50],
            [['comments'], 'string', 'max' => 300],
            [['deviation_notes'], 'string'],

            [['docentry', 'linenum'], 'unique', 'targetAttribute' => ['docentry', 'linenum']],

            [['movement_type'], 'in', 'range' => [
                self::MOVEMENT_TYPE_ASSIGN,
                self::MOVEMENT_TYPE_ROTATE,
                self::MOVEMENT_TYPE_REMOVE,
                self::MOVEMENT_TYPE_TRANSFER,
                self::MOVEMENT_TYPE_REPAIR_SEND,
                self::MOVEMENT_TYPE_REPAIR_RETURN,
                self::MOVEMENT_TYPE_SCRAP,
            ]],
            [['line_status'], 'in', 'range' => [self::LINE_STATUS_PENDING, self::LINE_STATUS_EXECUTED, self::LINE_STATUS_CANCELED]],
            [['line_status'], 'default', 'value' => self::LINE_STATUS_PENDING],
            [['tire_condition'], 'in', 'range' => [self::TIRE_CONDITION_GOOD, self::TIRE_CONDITION_USED, self::TIRE_CONDITION_DAMAGED, self::TIRE_CONDITION_SCRAP, self::TIRE_CONDITION_REPAIR]],
            [['physical_condition'], 'in', 'range' => [self::PHYSICAL_CONDITION_NW, self::PHYSICAL_CONDITION_RT, self::PHYSICAL_CONDITION_GD, self::PHYSICAL_CONDITION_LW, self::PHYSICAL_CONDITION_IW, self::PHYSICAL_CONDITION_SD, self::PHYSICAL_CONDITION_PU, self::PHYSICAL_CONDITION_UN]],
            [['physical_condition'], 'default', 'value' => self::PHYSICAL_CONDITION_NW],

            [['docentry'], 'exist', 'skipOnError' => true, 'targetClass' => DocTireMovement::class, 'targetAttribute' => ['docentry' => 'docentry']],
            [['tire_code'], 'exist', 'skipOnError' => true, 'targetClass' => Tire::class, 'targetAttribute' => ['tire_code' => 'tire_code']],
            [['related_tire_code'], 'exist', 'skipOnError' => true, 'targetClass' => Tire::class, 'targetAttribute' => ['related_tire_code' => 'tire_code']],
            [['vehicle_code_from'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::class, 'targetAttribute' => ['vehicle_code_from' => 'vehicle_code']],
            [['vehicle_code_to'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::class, 'targetAttribute' => ['vehicle_code_to' => 'vehicle_code']],
            [['whs_code_from'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['whs_code_from' => 'code']],
            [['whs_code_to'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['whs_code_to' => 'code']],
            [['createuser'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['createuser' => 'id']],
            [['updateuser'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updateuser' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'Id',
            'docentry' => 'Docentry',
            'linenum' => 'Número Línea',
            'movement_type' => 'Tipo Movimiento',
            'tire_code' => 'Llanta',
            'tire_km' => 'Km Llanta',
            'related_tire_code' => 'Llanta Relacionada',
            'related_tire_km' => 'Km Llanta Relacionada',
            'vehicle_code_from' => 'Unidad Origen',
            'vehicle_code_to' => 'Unidad Destino',
            'position_from' => 'Posición Origen',
            'position_to' => 'Posición Destino',
            'whs_code_from' => 'Almacén Origen',
            'whs_code_to' => 'Almacén Destino',
            'line_status' => 'Estado Línea',
            'execution_date' => 'Fecha Ejecución',
            'execution_time' => 'Hora Ejecución',
            'comments' => 'Comentarios',
            'deviation_notes' => 'Notas de Desviación',
            'tire_condition' => 'Condición Operativa',
            'physical_condition' => 'Condición Física',
            'tread_depth' => 'Profundidad',
            'target_type' => 'Tipo Destino',
            'target_entry' => 'Entrada Destino',
            'createdate' => 'Fecha Creación',
            'createtime' => 'Hora Creación',
            'createuser' => 'Creado Por',
            'updatedate' => 'Fecha Actualización',
            'updatetime' => 'Hora Actualización',
            'updateuser' => 'Actualizado Por',
        ];
    }

    public function getDocument(): \yii\db\ActiveQuery
    {
        return $this->hasOne(DocTireMovement::class, ['docentry' => 'docentry']);
    }

    public function getTire(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Tire::class, ['tire_code' => 'tire_code']);
    }

    public function getRelatedTire(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Tire::class, ['tire_code' => 'related_tire_code']);
    }

    public function getVehicleFrom(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Vehicle::class, ['vehicle_code' => 'vehicle_code_from']);
    }

    public function getVehicleTo(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Vehicle::class, ['vehicle_code' => 'vehicle_code_to']);
    }

    public function getWarehouseFrom(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Warehouse::class, ['code' => 'whs_code_from']);
    }

    public function getWarehouseTo(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Warehouse::class, ['code' => 'whs_code_to']);
    }

    public function getCreateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'createuser']);
    }

    public function getUpdateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'updateuser']);
    }

    public function getAttachments(): \yii\db\ActiveQuery
    {
        return $this->hasMany(DocTireMovementAttach::class, ['docentry' => 'docentry', 'linenum' => 'linenum']);
    }
}
