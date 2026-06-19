<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use app\models\system\User;
use yii\db\ActiveRecord;

class DocTireRepairDetail extends ActiveRecord
{
    public const REPAIR_TYPE_PUNCTURE = 'PUNCTURE';
    public const REPAIR_TYPE_PATCH = 'PATCH';
    public const REPAIR_TYPE_RETREAD = 'RETREAD';
    public const REPAIR_TYPE_BALANCE = 'BALANCE';
    public const REPAIR_TYPE_ALIGNMENT = 'ALIGNMENT';
    public const REPAIR_TYPE_ROTATION = 'ROTATION';
    public const REPAIR_TYPE_OTHER = 'OTHER';

    public const INDEXES = [
        'ux_doc_tire_repair_detail_docentry_linenum' => ['columns' => ['docentry', 'linenum'], 'unique' => true],
        'idx_doc_tire_repair_detail_tire_code' => ['columns' => ['tire_code'], 'unique' => false],
        'idx_doc_tire_repair_detail_repair_type' => ['columns' => ['repair_type'], 'unique' => false],
    ];

    public static function tableName(): string
    {
        return '{{%doc_tire_repair_detail}}';
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
            [['docentry', 'linenum', 'tire_code'], 'required'],
            [['id', 'docentry', 'linenum', 'tire_km', 'createuser', 'updateuser'], 'integer'],
            [['cost', 'tread_depth'], 'number'],
            [['createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
            [['tire_code'], 'string', 'max' => 50],
            [['comments'], 'string', 'max' => 300],
            [['deviation_notes'], 'string'],

            [['docentry', 'linenum'], 'unique', 'targetAttribute' => ['docentry', 'linenum']],

            [['repair_type'], 'in', 'range' => [
                self::REPAIR_TYPE_PUNCTURE,
                self::REPAIR_TYPE_PATCH,
                self::REPAIR_TYPE_RETREAD,
                self::REPAIR_TYPE_BALANCE,
                self::REPAIR_TYPE_ALIGNMENT,
                self::REPAIR_TYPE_ROTATION,
                self::REPAIR_TYPE_OTHER,
            ]],
            [['repair_type'], 'default', 'value' => self::REPAIR_TYPE_OTHER],

            [['docentry'], 'exist', 'skipOnError' => true, 'targetClass' => DocTireRepair::class, 'targetAttribute' => ['docentry' => 'docentry']],
            [['tire_code'], 'exist', 'skipOnError' => true, 'targetClass' => Tire::class, 'targetAttribute' => ['tire_code' => 'tire_code']],
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
            'tire_code' => 'Llanta',
            'repair_type' => 'Tipo Reparación',
            'cost' => 'Costo',
            'tire_km' => 'Kilometraje',
            'tread_depth' => 'Profundidad Banda (mm)',
            'deviation_notes' => 'Notas de Desviación',
            'comments' => 'Comentarios',
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
        return $this->hasOne(DocTireRepair::class, ['docentry' => 'docentry']);
    }

    public function getTire(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Tire::class, ['tire_code' => 'tire_code']);
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
        return $this->hasMany(DocTireRepairAttach::class, ['docentry' => 'docentry', 'linenum' => 'linenum']);
    }
}
