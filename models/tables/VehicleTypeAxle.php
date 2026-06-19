<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use yii\db\ActiveRecord;

/**
 * VehicleTypeAxle — ActiveRecord para {{%vehicle_type_axle}} (Configuración de Ejes por Tipo de Unidad)
 *
 * @property string      $code
 * @property int         $line_num
 * @property string      $axle_type_code
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 *
 * @property VehicleType $vehicleType
 * @property AxleType    $axleType
 */
class VehicleTypeAxle extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%vehicle_type_axle}}';
    }

    public static function primaryKey(): array
    {
        return ['code', 'line_num'];
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
            [['code', 'line_num', 'axle_type_code'], 'required'],
            [['code', 'axle_type_code'], 'string', 'max' => 50],
            [['line_num'], 'integer', 'min' => 0],
            [['createuser', 'updateuser'], 'integer'],
            [['createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
            [['code'], 'exist', 'skipOnError' => true,
                'targetClass' => VehicleType::class, 'targetAttribute' => ['code' => 'code']],
            [['axle_type_code'], 'exist', 'skipOnError' => true,
                'targetClass' => AxleType::class, 'targetAttribute' => ['axle_type_code' => 'code']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'code'           => 'Código Tipo Unidad',
            'line_num'       => 'Número Eje',
            'axle_type_code' => 'Tipo de Eje',
            'createdate'     => 'Fecha Creación',
            'createtime'     => 'Hora Creación',
            'createuser'     => 'Creado por',
            'updatedate'     => 'Fecha Actualización',
            'updatetime'     => 'Hora Actualización',
            'updateuser'     => 'Actualizado por',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────

    public function getVehicleType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(VehicleType::class, ['code' => 'code']);
    }

    public function getAxleType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(AxleType::class, ['code' => 'axle_type_code']);
    }
}
