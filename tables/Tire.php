<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use yii\db\ActiveRecord;

/**
 * Tire — ActiveRecord para {{%tire}} (Llantas)
 *
 * @property string      $tire_code
 * @property string      $tire_name
 * @property string      $object
 * @property string|null $brand_code
 * @property string|null $model_code
 * @property string|null $size_code
 * @property string|null $type_code
 * @property string|null $serial_no
 * @property string|null $dot_code
 * @property string|null $manufacture_date
 * @property string|null $purchase_date
 * @property float|null  $purchase_price
 * @property float|null  $current_km
 * @property float|null  $max_km
 * @property int|null    $retread_qty
 * @property string      $operational_status
 * @property string      $physical_condition
 * @property string      $location_status
 * @property string      $is_final
 * @property string|null $notes
 * @property string|null $tread_design_code
 * @property float|null  $tire_width
 * @property float|null  $aspect_ratio
 * @property string|null $structure_type
 * @property float|null  $rim_size
 * @property string|null $load_idx
 * @property float|null  $max_load
 * @property float|null  $max_press
 * @property string|null $traction_rate
 * @property string|null $temp_rate
 * @property string|null $country_code
 * @property float|null  $orig_tread_depth
 * @property float|null  $init_tread_depth
 * @property float|null  $curr_tread_depth
 * @property float|null  $tread_wear_factor
 * @property string|null $usage_type_code
 * @property float|null  $init_km
 * @property int         $repair_qty
 * @property string|null $assigned_unit_code
 * @property string|null $assigned_position_code
 * @property string|null $assigned_axle_code
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 *
 * @property TireBrand|null        $brand
 * @property TireModel|null        $model
 * @property TireSize|null         $size
 * @property TireType|null         $type
 * @property TireTreadDesign|null  $treadDesign
 * @property TireUsageType|null    $usageType
 * @property Country|null          $country
 * @property Vehicle|null          $assignedUnit
 * @property VehicleTire[]         $vehicleTires
 */
class Tire extends ActiveRecord
{
    // operational_status: AV=Disponible, US=En Uso, MT=Mantenimiento, DS=Desechada
    public const OP_STATUS_AV = 'AV';
    public const OP_STATUS_US = 'US';
    public const OP_STATUS_MT = 'MT';
    public const OP_STATUS_DS = 'DS';

    // physical_condition: NW=Nueva, RT=Reencauchada, GD=Buena, LW=Desgaste Bajo, IW=Desgaste Irregular, SD=Dañada, PU=Pinchada, UN=Sin Inspección
    public const COND_NW = 'NW';
    public const COND_RT = 'RT';
    public const COND_GD = 'GD';
    public const COND_LW = 'LW';
    public const COND_IW = 'IW';
    public const COND_SD = 'SD';
    public const COND_PU = 'PU';
    public const COND_UN = 'UN';

    // location_status: WH=Almacén, VH=En Vehículo, WS=Taller, SC=Desecho, SP=En Reencauche
    public const LOC_WH = 'WH';
    public const LOC_VH = 'VH';
    public const LOC_WS = 'WS';
    public const LOC_SC = 'SC';
    public const LOC_SP = 'SP';

    public const IS_FINAL_Y = 'Y';
    public const IS_FINAL_N = 'N';

    // structure_type: R=Radial, B=Bias, D=Diagonal
    public const STRUCT_R = 'R';
    public const STRUCT_B = 'B';
    public const STRUCT_D = 'D';

    // traction_rate: AA, A, B, C
    public const TRAC_AA = 'AA';
    public const TRAC_A = 'A';
    public const TRAC_B = 'B';
    public const TRAC_C = 'C';

    // temp_rate: A, B, C
    public const TEMP_A = 'A';
    public const TEMP_B = 'B';
    public const TEMP_C = 'C';

    public static function tableName(): string
    {
        return '{{%tire}}';
    }

    public static function primaryKey(): array
    {
        return ['tire_code'];
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
            [['tire_code', 'tire_name'], 'required'],
            [['tire_code', 'brand_code', 'model_code', 'size_code', 'type_code', 'serial_no', 'dot_code'], 'string', 'max' => 50],
            [['tire_name'], 'string', 'max' => 250],
            //[['object'], 'string', 'max' => 50],
            [['object'], 'default', 'value' => 'tire'],
            [['notes'], 'string'],
            [['retread_qty', 'repair_qty'], 'integer'],
            [['repair_qty'], 'default', 'value' => 0],
            [['purchase_price', 'current_km', 'max_km', 'tire_width', 'aspect_ratio', 'rim_size', 'max_load', 'max_press', 'orig_tread_depth', 'init_tread_depth', 'curr_tread_depth', 'tread_wear_factor', 'init_km'], 'number'],
            [['tread_design_code'], 'string', 'max' => 20],
            [['usage_type_code'], 'string', 'max' => 20],
            [['country_code'], 'string', 'max' => 10],
            [['load_idx'], 'string', 'max' => 10],
            [['assigned_unit_code'], 'string', 'max' => 50],
            [['assigned_position_code'], 'string', 'max' => 50],
            [['assigned_axle_code'], 'string', 'max' => 50],
            [['manufacture_date', 'purchase_date'], 'date', 'format' => 'php:Y-m-d'],
            [['brand_code'], 'exist', 'skipOnError' => true, 'targetClass' => TireBrand::class, 'targetAttribute' => ['brand_code' => 'code']],
            [['model_code'], 'exist', 'skipOnError' => true, 'targetClass' => TireModel::class, 'targetAttribute' => ['model_code' => 'code']],
            [['size_code'], 'exist', 'skipOnError' => true, 'targetClass' => TireSize::class, 'targetAttribute' => ['size_code' => 'code']],
            [['type_code'], 'exist', 'skipOnError' => true, 'targetClass' => TireType::class, 'targetAttribute' => ['type_code' => 'code']],
            [['tread_design_code'], 'exist', 'skipOnError' => true, 'targetClass' => TireTreadDesign::class, 'targetAttribute' => ['tread_design_code' => 'code']],
            [['usage_type_code'], 'exist', 'skipOnError' => true, 'targetClass' => TireUsageType::class, 'targetAttribute' => ['usage_type_code' => 'code']],
            [['country_code'], 'exist', 'skipOnError' => true, 'targetClass' => Country::class, 'targetAttribute' => ['country_code' => 'code']],
            [['assigned_unit_code'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::class, 'targetAttribute' => ['assigned_unit_code' => 'vehicle_code']],
            [['structure_type'], 'in', 'range' => [self::STRUCT_R, self::STRUCT_B, self::STRUCT_D]],
            [['traction_rate'], 'in', 'range' => [self::TRAC_AA, self::TRAC_A, self::TRAC_B, self::TRAC_C]],
            [['temp_rate'], 'in', 'range' => [self::TEMP_A, self::TEMP_B, self::TEMP_C]],
            [['operational_status'], 'in', 'range' => [self::OP_STATUS_AV, self::OP_STATUS_US, self::OP_STATUS_MT, self::OP_STATUS_DS]],
            [['operational_status'], 'default', 'value' => self::OP_STATUS_AV],
            [['physical_condition'], 'in', 'range' => [self::COND_NW, self::COND_RT, self::COND_GD, self::COND_LW, self::COND_IW, self::COND_SD, self::COND_PU, self::COND_UN]],
            [['physical_condition'], 'default', 'value' => self::COND_NW],
            [['location_status'], 'in', 'range' => [self::LOC_WH, self::LOC_VH, self::LOC_WS, self::LOC_SC, self::LOC_SP]],
            [['location_status'], 'default', 'value' => self::LOC_WH],
            [['is_final'], 'in', 'range' => [self::IS_FINAL_Y, self::IS_FINAL_N]],
            [['is_final'], 'default', 'value' => self::IS_FINAL_Y],
            [['createuser', 'updateuser'], 'integer'],
            [['createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'tire_code'          => 'Código Llanta',
            'tire_name'          => 'Nombre',
            'object'             => 'Objeto',
            'brand_code'         => 'Marca',
            'model_code'         => 'Modelo',
            'type_code'          => 'Tipo',
            'size_code'          => 'Medida',
            'serial_no'          => 'Número de Serie',
            'dot_code'           => 'Código DOT',
            'manufacture_date'   => 'Fecha Fabricación',
            'purchase_date'      => 'Fecha Compra',
            'purchase_price'     => 'Precio Compra',
            'current_km'         => 'Km Actuales',
            'max_km'             => 'Km Máximos',
            'retread_qty'        => 'No. Reencauches',
            'operational_status' => 'Estado Operacional',
            'physical_condition' => 'Condición Física',
            'location_status'    => 'Ubicación',
            'is_final'           => 'Baja Definitiva',
            'notes'              => 'Notas',
            'tread_design_code'  => 'Diseño Rodada',
            'tire_width'         => 'Ancho (mm)',
            'aspect_ratio'       => 'Relación Aspecto (%)',
            'structure_type'     => 'Tipo Estructura',
            'rim_size'           => 'Tamaño Rin (pulg)',
            'load_idx'           => 'Índice Carga',
            'max_load'           => 'Carga Máx (kg)',
            'max_press'          => 'Presión Máx (PSI)',
            'traction_rate'      => 'Clasif. Tracción',
            'temp_rate'          => 'Clasif. Temperatura',
            'country_code'       => 'País Fabricación',
            'orig_tread_depth'   => 'Prof. Original (mm)',
            'init_tread_depth'   => 'Prof. Inicial (mm)',
            'curr_tread_depth'   => 'Prof. Actual (mm)',
            'tread_wear_factor'  => 'Factor Desgaste',
            'usage_type_code'    => 'Tipo de Uso',
            'init_km'            => 'Km Inicial',
            'repair_qty'         => 'Cant. Reparaciones',
            'assigned_unit_code'    => 'Unidad Asignada',
            'assigned_position_code' => 'Posición Asignada',
            'assigned_axle_code'    => 'Eje Asignado',
            'createdate'            => 'Fecha Creación',
            'createtime'         => 'Hora Creación',
            'createuser'         => 'Creado por',
            'updatedate'         => 'Fecha Actualización',
            'updatetime'         => 'Hora Actualización',
            'updateuser'         => 'Actualizado por',
        ];
    }

    public static function getOperationalStatusOptions(): array
    {
        return [
            self::OP_STATUS_AV => 'Disponible',
            self::OP_STATUS_US => 'En Uso',
            self::OP_STATUS_MT => 'Mantenimiento',
            self::OP_STATUS_DS => 'Desechada',
        ];
    }

    public static function getOperationalStatusLabel(string $value): string
    {
        return self::getOperationalStatusOptions()[$value] ?? $value;
    }

    public static function getPhysicalConditionOptions(): array
    {
        return [
            self::COND_NW => 'Nueva',
            self::COND_RT => 'Reencauchada',
            self::COND_GD => 'Buena',
            self::COND_LW => 'Desgaste Bajo',
            self::COND_IW => 'Desgaste Irregular',
            self::COND_SD => 'Dañada',
            self::COND_PU => 'Pinchada',
            self::COND_UN => 'Sin Inspección',
        ];
    }

    public static function getPhysicalConditionLabel(string $value): string
    {
        return self::getPhysicalConditionOptions()[$value] ?? $value;
    }

    public static function getLocationStatusOptions(): array
    {
        return [
            self::LOC_WH => 'Almacén',
            self::LOC_VH => 'En Vehículo',
            self::LOC_WS => 'Taller',
            self::LOC_SC => 'Desecho',
            self::LOC_SP => 'En Reencauche',
        ];
    }

    public static function getLocationStatusLabel(string $value): string
    {
        return self::getLocationStatusOptions()[$value] ?? $value;
    }

    public static function getIsFinalOptions(): array
    {
        return [self::IS_FINAL_Y => 'Sí', self::IS_FINAL_N => 'No'];
    }

    public static function getStructureTypeOptions(): array
    {
        return [
            self::STRUCT_R => 'Radial',
            self::STRUCT_B => 'Bias',
            self::STRUCT_D => 'Diagonal',
        ];
    }

    public static function getStructureTypeLabel(string $value): string
    {
        return self::getStructureTypeOptions()[$value] ?? $value;
    }

    public static function getTractionRateOptions(): array
    {
        return [
            self::TRAC_AA => 'AA (Excelente)',
            self::TRAC_A  => 'A (Buena)',
            self::TRAC_B  => 'B (Aceptable)',
            self::TRAC_C  => 'C (Mínima)',
        ];
    }

    public static function getTractionRateLabel(string $value): string
    {
        return self::getTractionRateOptions()[$value] ?? $value;
    }

    public static function getTempRateOptions(): array
    {
        return [
            self::TEMP_A => 'A (Alta)',
            self::TEMP_B => 'B (Media)',
            self::TEMP_C => 'C (Baja)',
        ];
    }

    public static function getTempRateLabel(string $value): string
    {
        return self::getTempRateOptions()[$value] ?? $value;
    }

    public function getBrand(): \yii\db\ActiveQuery
    {
        return $this->hasOne(TireBrand::class, ['code' => 'brand_code']);
    }

    public function getModel(): \yii\db\ActiveQuery
    {
        return $this->hasOne(TireModel::class, ['code' => 'model_code']);
    }

    public function getSize(): \yii\db\ActiveQuery
    {
        return $this->hasOne(TireSize::class, ['code' => 'size_code']);
    }

    public function getType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(TireType::class, ['code' => 'type_code']);
    }

    public function getVehicleTires(): \yii\db\ActiveQuery
    {
        return $this->hasMany(VehicleTire::class, ['tire_code' => 'tire_code']);
    }

    public function getTreadDesign(): \yii\db\ActiveQuery
    {
        return $this->hasOne(TireTreadDesign::class, ['code' => 'tread_design_code']);
    }

    public function getUsageType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(TireUsageType::class, ['code' => 'usage_type_code']);
    }

    public function getCountry(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Country::class, ['code' => 'country_code']);
    }

    public function getAssignedUnit(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Vehicle::class, ['vehicle_code' => 'assigned_unit_code']);
    }
}
