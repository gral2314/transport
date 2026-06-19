<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use yii\db\ActiveRecord;

/**
 * VehicleTire — ActiveRecord para {{%vehicle_tire}} (Llantas instaladas en Vehículo)
 *
 * @property string      $vehicle_code
 * @property int         $line_num
 * @property string      $object
 * @property string      $tire_code
 * @property int         $axle_line_num
 * @property string      $axle_type_code
 * @property string      $position_code
 * @property string|null $install_date
 * @property float|null  $install_km
 * @property float|null  $record_km
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 *
 * @property Vehicle  $vehicle
 * @property Tire     $tire
 * @property AxleType $axleType
 */
class VehicleTire extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%vehicle_tire}}';
    }

    public static function primaryKey(): array
    {
        return ['vehicle_code', 'line_num'];
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
            // ✅ CAMBIO: tire_code es opcional (NULL cuando se auto-llena la configuración)
            [['vehicle_code', 'line_num', 'object', 'axle_line_num', 'axle_type_code', 'position_code'], 'required'],
            [['vehicle_code', 'tire_code', 'axle_type_code', 'position_code'], 'string', 'max' => 50],
            [['object'], 'string', 'max' => 50],
            [['line_num', 'axle_line_num'], 'integer'],
            [['install_km', 'record_km'], 'number'],
            [['install_date'], 'date', 'format' => 'php:Y-m-d'],
            [['vehicle_code'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::class, 'targetAttribute' => ['vehicle_code' => 'vehicle_code']],
            // ✅ tire_code puede ser NULL (usuario asigna después)
            [['tire_code'], 'exist', 'skipOnError' => true, 'targetClass' => Tire::class, 'targetAttribute' => ['tire_code' => 'tire_code'], 'when' => function($model) {
                return !empty($model->tire_code);
            }],
            [['axle_type_code'], 'exist', 'skipOnError' => true, 'targetClass' => AxleType::class, 'targetAttribute' => ['axle_type_code' => 'code']],
            [['createuser', 'updateuser'], 'integer'],
            [['createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'vehicle_code'   => 'Unidad',
            'line_num'       => 'Línea',
            'object'         => 'Objeto',
            'tire_code'      => 'Llanta',
            'axle_line_num'  => 'Número de Eje',
            'axle_type_code' => 'Tipo de Eje',
            'position_code'  => 'Posición en Eje',
            'install_date'   => 'Fecha Instalación',
            'install_km'     => 'Km al Instalar',
            'record_km'      => 'Km Registrado',
            'createdate'     => 'Fecha Creación',
            'createtime'     => 'Hora Creación',
            'createuser'     => 'Creado por',
            'updatedate'     => 'Fecha Actualización',
            'updatetime'     => 'Hora Actualización',
            'updateuser'     => 'Actualizado por',
        ];
    }

    public function getVehicle(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Vehicle::class, ['vehicle_code' => 'vehicle_code']);
    }

    public function getTire(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Tire::class, ['tire_code' => 'tire_code']);
    }

    public function getAxleType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(AxleType::class, ['code' => 'axle_type_code']);
    }
}
