<?php

namespace app\models\tables;
    use app\components\behaviors\AuditBehavior;
use Yii;

/**
 * This is the model class for table "warehouse".
 *
 * @property string $code Código único del almacén
 * @property string $name Nombre del almacén
 * @property string $active Activo (Y=Sí, N=No)
 * @property string|null $createdate Fecha de creación
 * @property string|null $createtime Hora de creación
 * @property int|null $createuser Usuario que crea
 * @property string|null $updatedate Fecha de actualización
 * @property string|null $updatetime Hora de actualización
 * @property int|null $updateuser Usuario que actualiza
 *
 * @property ItemWarehouse[] $itemWarehouses
 * @property Items[] $itemcodes
 */
class Warehouse extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ACTIVE_Y = 'Y';
    const ACTIVE_N = 'N';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%warehouse}}';
    }
    public function behaviors(): array
    {
        return [
            'audit' => ['class' => AuditBehavior::class],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['createdate', 'createtime', 'createuser', 'updatedate', 'updatetime', 'updateuser'], 'default', 'value' => null],
            [['active'], 'default', 'value' => 'Y'],
            [['code', 'name'], 'required'],
            [['active'], 'string'],
            [['createdate', 'createtime', 'updatedate', 'updatetime'], 'safe'],
            [['createuser', 'updateuser'], 'integer'],
            [['code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 250],
            ['active', 'in', 'range' => array_keys(self::optsActive())],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Código único del almacén',
            'name' => 'Nombre del almacén',
            'active' => 'Activo (Y=Sí, N=No)',
            'createdate' => 'Fecha de creación',
            'createtime' => 'Hora de creación',
            'createuser' => 'Usuario que crea',
            'updatedate' => 'Fecha de actualización',
            'updatetime' => 'Hora de actualización',
            'updateuser' => 'Usuario que actualiza',
        ];
    }

    /**
     * Gets query for [[ItemWarehouses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemWarehouses()
    {
        return $this->hasMany(ItemWarehouse::class, ['warehouse_code' => 'code']);
    }

    /**
     * Gets query for [[Itemcodes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemcodes()
    {
        return $this->hasMany(Items::class, ['itemcode' => 'itemcode'])->viaTable('item_warehouse', ['warehouse_code' => 'code']);
    }


    /**
     * column active ENUM value labels
     * @return string[]
     */
    public static function optsActive()
    {
        return [
            self::ACTIVE_Y => 'Y',
            self::ACTIVE_N => 'N',
        ];
    }

    /**
     * @return string
     */
    public function displayActive()
    {
        return self::optsActive()[$this->active];
    }

    /**
     * @return bool
     */
    public function isActiveY()
    {
        return $this->active === self::ACTIVE_Y;
    }

    public function setActiveToY()
    {
        $this->active = self::ACTIVE_Y;
    }

    /**
     * @return bool
     */
    public function isActiveN()
    {
        return $this->active === self::ACTIVE_N;
    }

    public function setActiveToN()
    {
        $this->active = self::ACTIVE_N;
    }
}
