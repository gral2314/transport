<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use yii\db\ActiveRecord;

/**
 * VehicleType — ActiveRecord para {{%vehicle_type}} (Tipos de Unidad)
 *
 * @property string      $code
 * @property string      $name
 * @property string|null $type_unidad
 * @property string      $active
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 *
 * @property VehicleTypeAxle[] $axles
 */
class VehicleType extends ActiveRecord
{
    public const ACTIVE_Y = 'Y';
    public const ACTIVE_N = 'N';
    
    // Tipos de unidad
    public const TYPE_UTILITARIO = 'Uti';
    public const TYPE_UNIDAD     = 'Uni';
    public const TYPE_REMOLQUE   = 'Rem';
    public const TYPE_DOLLY      = 'Dol';

    public static function tableName(): string
    {
        return '{{%vehicle_type}}';
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
            [['code', 'name'], 'required'],
            [['code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 100],
            [['type_unidad'], 'in', 'range' => [self::TYPE_UTILITARIO, self::TYPE_UNIDAD, self::TYPE_REMOLQUE, self::TYPE_DOLLY]],
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
            'code'        => 'Código',
            'name'        => 'Nombre',
            'type_unidad' => 'Tipo de Unidad',
            'active'      => 'Activo',
            'createdate'  => 'Fecha Creación',
            'createtime'  => 'Hora Creación',
            'createuser'  => 'Creado por',
            'updatedate'  => 'Fecha Actualización',
            'updatetime'  => 'Hora Actualización',
            'updateuser'  => 'Actualizado por',
        ];
    }

    public static function getActiveOptions(): array
    {
        return [self::ACTIVE_Y => 'Sí', self::ACTIVE_N => 'No'];
    }
    
    public static function getTypeUnidadOptions(): array
    {
        return [
            self::TYPE_UTILITARIO => 'Utilitario',
            self::TYPE_UNIDAD     => 'Unidad',
            self::TYPE_REMOLQUE   => 'Remolque',
            self::TYPE_DOLLY      => 'Dolly',
        ];
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
    
    /**
     * Retorna dropdown list extendido con metadatos adicionales:
     * axles, tires, type_unidad para cada tipo de vehículo
     * 
     * @return array ['code' => ['name' => '...', 'axles' => int, 'tires' => int, 'type_unidad' => '...']]
     */
    public static function getDropdownListExtended(): array
    {
        $models = self::find()
            ->with('axles.axleType')
            ->where(['active' => self::ACTIVE_Y])
            ->orderBy(['name' => SORT_ASC])
            ->all();
        
        $result = [];
        foreach ($models as $model) {
            $totalAxles = count($model->axles);
            $totalTires = 0;
            
            foreach ($model->axles as $axle) {
                if ($axle->axleType) {
                    $totalTires += (int)$axle->axleType->tire_qty;
                }
            }
            
            $result[$model->code] = [
                'name'        => $model->name,
                'axles'       => $totalAxles,
                'tires'       => $totalTires,
                'type_unidad' => $model->type_unidad ?? '',
            ];
        }
        
        return $result;
    }

    // ── Relaciones ─────────────────────────────────────────────────

    public function getAxles(): \yii\db\ActiveQuery
    {
        return $this->hasMany(VehicleTypeAxle::class, ['code' => 'code'])
            ->orderBy(['line_num' => SORT_ASC]);
    }
}
