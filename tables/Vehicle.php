<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use yii\db\ActiveRecord;

/**
 * Vehicle — ActiveRecord para {{%vehicle}} (Unidades / Vehículos)
 *
 * @property string      $vehicle_code
 * @property string      $vehicle_name
 * @property string      $object
 * @property string      $vehicle_type_code
 * @property string|null $brand_code
 * @property string|null $model
 * @property int|null    $unit_year
 * @property string|null $plate_no
 * @property string|null $economic_no
 * @property string|null $vin
 * @property string|null $engine_no
 * @property string|null $serial_no
 * @property string|null $fuel_type_code
 * @property float|null  $fuel_capacity
 * @property float|null  $current_fuel
 * @property float|null  $fuel_performance
 * @property float|null  $current_km
 * @property float|null  $initial_km
 * @property float|null  $weight_capacity
 * @property float|null  $volume_capacity
 * @property float|null  $cargo_length
 * @property float|null  $cargo_width
 * @property float|null  $cargo_height
 * @property float|null  $unit_length
 * @property float|null  $unit_width
 * @property float|null  $unit_height
 * @property string|null $gps_id
 * @property string|null $gps_model
 * @property string|null $gps_provider
 * @property string|null $iave
 * @property string|null $fixed_asset_code
 * @property string|null $gl_account
 * @property string|null $cost_center_code
 * @property string      $acquisition
 * @property string|null $purchase_date
 * @property float|null  $purchase_price
 * @property string|null $sat_vehicle_config_code
 * @property string|null $nom012_code
 * @property string|null $service_type_code
 * @property string|null $cargo_type_code
 * @property string      $active
 * @property string      $status
 * @property string      $available
 * @property string|null $default_driver_code
 * @property string|null $default_driver2_code
 * @property string|null $default_trailer1_code
 * @property string|null $default_trailer2_code
 * @property string|null $default_dolly_code
 * @property string|null $notes
 * @property string|null $last_service_date
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 *
 * @property VehicleType       $vehicleType
 * @property VehicleBrand|null $vehicleBrand
 * @property FuelType|null     $fuelType
 * @property CenterCost|null   $costCenter
 * @property SatVehicleConfig|null $satVehicleConfig
 * @property Nom012|null       $nom012
 * @property ServiceType|null  $serviceType
 * @property CargoType|null    $cargoType
 * @property VehicleDocument[] $vehicleDocuments
 * @property VehicleTire[]     $vehicleTires
 */
class Vehicle extends ActiveRecord
{
    // acquisition: P=Propio, R=Renta
    public const ACQ_P = 'P';
    public const ACQ_R = 'R';

    // status: A=Activo, I=Inactivo, M=Mantenimiento, O=Baja
    public const STATUS_A = 'A';
    public const STATUS_I = 'I';
    public const STATUS_M = 'M';
    public const STATUS_O = 'O';

    public const ACTIVE_Y = 'Y';
    public const ACTIVE_N = 'N';

    public const AVAILABLE_Y = 'Y';
    public const AVAILABLE_N = 'N';

    public static function tableName(): string
    {
        return '{{%vehicle}}';
    }

    public static function primaryKey(): array
    {
        return ['vehicle_code'];
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
            [['vehicle_code', 'vehicle_name', 'object', 'vehicle_type_code'], 'required'],
            [['vehicle_code', 'brand_code', 'fuel_type_code', 'cost_center_code', 'sat_vehicle_config_code', 'nom012_code',
              'service_type_code', 'cargo_type_code', 'default_driver_code', 'default_driver2_code',
              'default_trailer1_code', 'default_trailer2_code', 'default_dolly_code'], 'string', 'max' => 50],
            [['vehicle_type_code'], 'string', 'max' => 50],
            [['vehicle_name'], 'string', 'max' => 250],
            [['object', 'model', 'plate_no', 'economic_no', 'vin', 'engine_no', 'serial_no',
              'gps_id', 'gps_model', 'gps_provider', 'iave', 'fixed_asset_code', 'gl_account'], 'string', 'max' => 50],
            [['notes'], 'string'],
            [['unit_year'], 'integer'],
            [['fuel_capacity', 'current_fuel', 'fuel_performance', 'current_km', 'initial_km',
              'weight_capacity', 'volume_capacity', 'cargo_length', 'cargo_width', 'cargo_height',
              'unit_length', 'unit_width', 'unit_height', 'purchase_price'], 'number'],
            [['purchase_date', 'last_service_date'], 'date', 'format' => 'php:Y-m-d'],
            [['vehicle_type_code'], 'exist', 'skipOnError' => true, 'targetClass' => VehicleType::class, 'targetAttribute' => ['vehicle_type_code' => 'code']],
            [['brand_code'], 'exist', 'skipOnError' => true, 'targetClass' => VehicleBrand::class, 'targetAttribute' => ['brand_code' => 'code']],
            [['fuel_type_code'], 'exist', 'skipOnError' => true, 'targetClass' => FuelType::class, 'targetAttribute' => ['fuel_type_code' => 'code']],
            [['cost_center_code'], 'exist', 'skipOnError' => true, 'targetClass' => CenterCost::class, 'targetAttribute' => ['cost_center_code' => 'code']],
            [['sat_vehicle_config_code'], 'exist', 'skipOnError' => true, 'targetClass' => SatVehicleConfig::class, 'targetAttribute' => ['sat_vehicle_config_code' => 'code']],
            [['nom012_code'], 'exist', 'skipOnError' => true, 'targetClass' => Nom012::class, 'targetAttribute' => ['nom012_code' => 'code']],
            [['service_type_code'], 'exist', 'skipOnError' => true, 'targetClass' => ServiceType::class, 'targetAttribute' => ['service_type_code' => 'code']],
            [['cargo_type_code'], 'exist', 'skipOnError' => true, 'targetClass' => CargoType::class, 'targetAttribute' => ['cargo_type_code' => 'code']],
            [['acquisition'], 'in', 'range' => [self::ACQ_P, self::ACQ_R]],
            [['acquisition'], 'default', 'value' => self::ACQ_P],
            [['status'], 'in', 'range' => [self::STATUS_A, self::STATUS_I, self::STATUS_M, self::STATUS_O]],
            [['status'], 'default', 'value' => self::STATUS_A],
            [['active'], 'in', 'range' => [self::ACTIVE_Y, self::ACTIVE_N]],
            [['active'], 'default', 'value' => self::ACTIVE_Y],
            [['available'], 'in', 'range' => [self::AVAILABLE_Y, self::AVAILABLE_N]],
            [['available'], 'default', 'value' => self::AVAILABLE_Y],
            [['createuser', 'updateuser'], 'integer'],
            [['createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'vehicle_code'           => 'Código Unidad',
            'vehicle_name'           => 'Nombre',
            'object'                 => 'Objeto',
            'vehicle_type_code'      => 'Tipo de Unidad',
            'brand_code'             => 'Marca',
            'model'                  => 'Modelo',
            'unit_year'              => 'Año',
            'plate_no'               => 'Placas',
            'economic_no'            => 'No. Económico',
            'vin'                    => 'VIN/NIV',
            'engine_no'              => 'No. Motor',
            'serial_no'              => 'No. Serie',
            'fuel_type_code'         => 'Tipo Combustible',
            'fuel_capacity'          => 'Cap. Tanque (L)',
            'current_fuel'           => 'Combustible Actual (L)',
            'fuel_performance'       => 'Rendimiento (km/L)',
            'current_km'             => 'Km Actuales',
            'initial_km'             => 'Km Iniciales',
            'weight_capacity'        => 'Cap. Carga (Ton)',
            'volume_capacity'        => 'Cap. Volumen (m³)',
            'cargo_length'           => 'Largo Caja (m)',
            'cargo_width'            => 'Ancho Caja (m)',
            'cargo_height'           => 'Alto Caja (m)',
            'unit_length'            => 'Largo Unidad (m)',
            'unit_width'             => 'Ancho Unidad (m)',
            'unit_height'            => 'Alto Unidad (m)',
            'gps_id'                 => 'ID GPS',
            'gps_model'              => 'Modelo GPS',
            'gps_provider'           => 'Proveedor GPS',
            'iave'                   => 'IAVE / TELEVIA',
            'fixed_asset_code'       => 'Código Activo Fijo',
            'gl_account'             => 'Cuenta Contable',
            'cost_center_code'       => 'Centro de Costo',
            'acquisition'            => 'Adquisición',
            'purchase_date'          => 'Fecha Compra',
            'purchase_price'         => 'Precio Compra',
            'sat_vehicle_config_code'=> 'Config. SAT',
            'nom012_code'            => 'NOM-012',
            'service_type_code'      => 'Tipo Servicio',
            'cargo_type_code'        => 'Tipo Carga',
            'active'                 => 'Activo',
            'status'                 => 'Estatus',
            'available'              => 'Disponible',
            'default_driver_code'    => 'Operador Principal',
            'default_driver2_code'   => 'Operador Secundario',
            'default_trailer1_code'  => 'Remolque 1',
            'default_trailer2_code'  => 'Remolque 2',
            'default_dolly_code'     => 'Dolly',
            'notes'                  => 'Notas',
            'last_service_date'      => 'Último Servicio',
            'createdate'             => 'Fecha Creación',
            'createtime'             => 'Hora Creación',
            'createuser'             => 'Creado por',
            'updatedate'             => 'Fecha Actualización',
            'updatetime'             => 'Hora Actualización',
            'updateuser'             => 'Actualizado por',
        ];
    }

    public static function getAcquisitionOptions(): array
    {
        return [self::ACQ_P => 'Propio', self::ACQ_R => 'Renta'];
    }

    public static function getAcquisitionLabel(string $value): string
    {
        return self::getAcquisitionOptions()[$value] ?? $value;
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_A => 'Activo',
            self::STATUS_I => 'Inactivo',
            self::STATUS_M => 'Mantenimiento',
            self::STATUS_O => 'Baja',
        ];
    }

    public static function getStatusLabel(string $value): string
    {
        return self::getStatusOptions()[$value] ?? $value;
    }

    public static function getActiveOptions(): array
    {
        return [self::ACTIVE_Y => 'Sí', self::ACTIVE_N => 'No'];
    }

    public static function getAvailableOptions(): array
    {
        return [self::AVAILABLE_Y => 'Sí', self::AVAILABLE_N => 'No'];
    }

    public function getVehicleType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(VehicleType::class, ['code' => 'vehicle_type_code']);
    }

    public function getVehicleBrand(): \yii\db\ActiveQuery
    {
        return $this->hasOne(VehicleBrand::class, ['code' => 'brand_code']);
    }

    public function getFuelType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(FuelType::class, ['code' => 'fuel_type_code']);
    }

    public function getCostCenter(): \yii\db\ActiveQuery
    {
        return $this->hasOne(CenterCost::class, ['code' => 'cost_center_code']);
    }

    public function getSatVehicleConfig(): \yii\db\ActiveQuery
    {
        return $this->hasOne(SatVehicleConfig::class, ['code' => 'sat_vehicle_config_code']);
    }

    public function getNom012(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Nom012::class, ['code' => 'nom012_code']);
    }

    public function getServiceType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ServiceType::class, ['code' => 'service_type_code']);
    }

    public function getCargoType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(CargoType::class, ['code' => 'cargo_type_code']);
    }

    public function getVehicleDocuments(): \yii\db\ActiveQuery
    {
        return $this->hasMany(VehicleDocument::class, ['vehicle_code' => 'vehicle_code'])->orderBy(['line_num' => SORT_ASC]);
    }

    public function getVehicleTires(): \yii\db\ActiveQuery
    {
        return $this->hasMany(VehicleTire::class, ['vehicle_code' => 'vehicle_code'])->orderBy(['line_num' => SORT_ASC]);
    }
}
