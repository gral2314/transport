<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use yii\db\ActiveRecord;

/**
 * TireModel — ActiveRecord para {{%tire_model}} (Modelos de Llanta)
 *
 * @property string      $code
 * @property string      $name
 * @property string      $brand_code
 * @property string      $active
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 *
 * @property TireBrand $brand
 */
class TireModel extends ActiveRecord
{
    public const ACTIVE_Y = 'Y';
    public const ACTIVE_N = 'N';

    public static function tableName(): string
    {
        return '{{%tire_model}}';
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
            [['code', 'name', 'brand_code'], 'required'],
            [['code', 'brand_code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 100],
            [['brand_code'], 'exist', 'skipOnError' => true, 'targetClass' => TireBrand::class, 'targetAttribute' => ['brand_code' => 'code']],
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
            'code'       => 'Código',
            'name'       => 'Nombre',
            'brand_code' => 'Marca',
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

    public static function getDropdownList(?string $brandCode = null): array
    {
        $query = self::find()->where(['active' => self::ACTIVE_Y])->orderBy(['name' => SORT_ASC]);
        if ($brandCode !== null) {
            $query->andWhere(['brand_code' => $brandCode]);
        }
        return $query->select(['name', 'code'])->indexBy('code')->column();
    }

    public function getBrand(): \yii\db\ActiveQuery
    {
        return $this->hasOne(TireBrand::class, ['code' => 'brand_code']);
    }
}
