<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use yii\db\ActiveRecord;

/**
 * DocTypeVehicule — ActiveRecord para {{%doc_type_vehicule}} (Tipos de Documento Vehicular)
 *
 * @property string      $code
 * @property string      $name
 * @property int|null    $alert_time
 * @property int|null    $alert_repit
 * @property string      $active
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 */
class DocTypeVehicule extends ActiveRecord
{
    public const ACTIVE_Y = 'Y';
    public const ACTIVE_N = 'N';

    public static function tableName(): string
    {
        return '{{%doc_type_vehicule}}';
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
            [['name'], 'string', 'max' => 200],
            [['alert_time', 'alert_repit'], 'integer'],
            [['alert_time', 'alert_repit'], 'default', 'value' => null],
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
            'alert_time'  => 'Días para Alerta',
            'alert_repit' => 'Repetición Alerta (días)',
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

    public static function getDropdownList(): array
    {
        return self::find()
            ->where(['active' => self::ACTIVE_Y])
            ->orderBy(['name' => SORT_ASC])
            ->select(['name', 'code'])
            ->indexBy('code')
            ->column();
    }
}
