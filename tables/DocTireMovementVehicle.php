<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use app\models\system\User;
use yii\db\ActiveRecord;

class DocTireMovementVehicle extends ActiveRecord
{
    public const INDEXES = [
        'ux_doc_tire_movement_vehicle_docentry_linenum' => ['columns' => ['docentry', 'linenum'], 'unique' => true],
        'idx_doc_tire_movement_vehicle_vehicle_code' => ['columns' => ['vehicle_code'], 'unique' => false],
        'idx_doc_tire_movement_vehicle_vehicle_km' => ['columns' => ['vehicle_km'], 'unique' => false],
    ];

    public static function tableName(): string
    {
        return '{{%doc_tire_movement_vehicle}}';
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
            [['docentry', 'linenum'], 'required'],
            [['id', 'docentry', 'linenum', 'createuser', 'updateuser'], 'integer'],
            [['vehicle_km'], 'number'],
            [['createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
            [['vehicle_code'], 'string', 'max' => 50],
            [['comments'], 'string', 'max' => 300],

            [['docentry', 'linenum'], 'unique', 'targetAttribute' => ['docentry', 'linenum']],

            [['docentry'], 'exist', 'skipOnError' => true, 'targetClass' => DocTireMovement::class, 'targetAttribute' => ['docentry' => 'docentry']],
            [['vehicle_code'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::class, 'targetAttribute' => ['vehicle_code' => 'vehicle_code']],
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
            'vehicle_code' => 'Unidad',
            'vehicle_km' => 'Odómetro',
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
        return $this->hasOne(DocTireMovement::class, ['docentry' => 'docentry']);
    }

    public function getVehicle(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Vehicle::class, ['vehicle_code' => 'vehicle_code']);
    }

    public function getCreateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'createuser']);
    }

    public function getUpdateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'updateuser']);
    }
}
