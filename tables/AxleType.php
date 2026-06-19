<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use yii\db\ActiveRecord;

/**
 * AxleType — ActiveRecord para {{%axle_type}} (Tipos de Eje)
 *
 * @property string      $code
 * @property string      $name
 * @property int         $tire_qty
 * @property string      $active
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 *
 * @property AxleTypeConfig[] $configurations
 * @property VehicleTypeAxle[] $vehicleTypeAxles
 */
class AxleType extends ActiveRecord
{
    public const ACTIVE_Y = 'Y';
    public const ACTIVE_N = 'N';

    public static function tableName(): string
    {
        return '{{%axle_type}}';
    }

    public static function primaryKey(): array
    {
        return ['code'];
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
            [['code', 'name', 'tire_qty'], 'required'],
            [['code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 100],
            [['tire_qty'], 'integer', 'min' => 0],
            [['active'], 'in', 'range' => [self::ACTIVE_Y, self::ACTIVE_N]],
            [['active'], 'default', 'value' => self::ACTIVE_Y],
            [['createuser', 'updateuser'], 'integer'],
            [['createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
            [['code'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'code'       => 'Código',
            'name'       => 'Nombre',
            'tire_qty'   => 'Cantidad de Llantas',
            'active'     => 'Activo',
            'createdate' => 'Fecha Creación',
            'createtime' => 'Hora Creación',
            'createuser' => 'Creado por',
            'updatedate' => 'Fecha Actualización',
            'updatetime' => 'Hora Actualización',
            'updateuser' => 'Actualizado por',
        ];
    }

    public static function getActiveOptions(): array
    {
        return [self::ACTIVE_Y => 'Sí', self::ACTIVE_N => 'No'];
    }

    public static function getDropdownList(): array
    {
        return self::find()
            ->where(['active' => self::ACTIVE_Y])
            ->orderBy(['name' => SORT_ASC])
            ->select(['name', 'code'])
            ->indexBy('code')
            ->column();
    }

    // ── Relaciones ─────────────────────────────────────────────────

    public function getConfigurations(): \yii\db\ActiveQuery
    {
        return $this->hasMany(AxleTypeConfig::class, ['code' => 'code']);
    }

    public function getVehicleTypeAxles(): \yii\db\ActiveQuery
    {
        return $this->hasMany(VehicleTypeAxle::class, ['axle_type_code' => 'code']);
    }
}
