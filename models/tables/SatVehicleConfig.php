<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use yii\db\ActiveRecord;

/**
 * SatVehicleConfig — ActiveRecord para {{%sat_vehicle_config}} (Configuración Vehicular SAT)
 *
 * @property string      $code
 * @property string      $name
 * @property int         $max_ejes
 * @property int         $max_tires
 * @property int         $max_remolque
 * @property string      $active
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 */
class SatVehicleConfig extends ActiveRecord
{
    public const ACTIVE_Y = 'Y';
    public const ACTIVE_N = 'N';

    public static function tableName(): string
    {
        return '{{%sat_vehicle_config}}';
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
            [['code', 'name', 'max_ejes', 'max_tires', 'max_remolque'], 'required'],
            [['code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 200],
            [['max_ejes', 'max_tires', 'max_remolque'], 'integer'],
            [['active'], 'in', 'range' => [self::ACTIVE_Y, self::ACTIVE_N]],
            [['active'], 'default', 'value' => self::ACTIVE_Y],
            [['createuser', 'updateuser'], 'integer'],
            [['createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'code'         => 'Código SAT',
            'name'         => 'Descripción',
            'max_ejes'     => 'Máx. Ejes',
            'max_tires'    => 'Máx. Llantas',
            'max_remolque' => 'Máx. Remolques',
            'active'       => 'Activo',
            'createdate'   => 'Fecha Creación',
            'createtime'   => 'Hora Creación',
            'createuser'   => 'Creado por',
            'updatedate'   => 'Fecha Actualización',
            'updatetime'   => 'Hora Actualización',
            'updateuser'   => 'Actualizado por',
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
            ->orderBy(['code' => SORT_ASC])
            ->select(['name', 'code'])
            ->indexBy('code')
            ->column();
    }
}
