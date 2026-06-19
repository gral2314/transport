<?php

namespace app\models\tables;
    use app\components\behaviors\AuditBehavior;
use Yii;

/**
 * This is the model class for table "currency".
 *
 * @property string $code Código único de la moneda
 * @property string $name Nombre de la moneda
 * @property string $symbol Símbolo de la moneda
 * @property int $decimals Decimales de la moneda
 * @property string $txt_singular Texto en singular de la moneda
 * @property string $txt_plural Texto en plural de la moneda
 * @property string $active Activo (Y=Sí, N=No)
 * @property string|null $createdate Fecha de creación
 * @property string|null $createtime Hora de creación
 * @property int|null $createuser Usuario que crea
 * @property string|null $updatedate Fecha de actualización
 * @property string|null $updatetime Hora de actualización
 * @property int|null $updateuser Usuario que actualiza
 *
 * @property Bp[] $bps
 */
class Currency extends \yii\db\ActiveRecord
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
        return '{{%currency}}';
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
            [['code', 'name', 'symbol', 'decimals', 'txt_singular', 'txt_plural'], 'required'],
            [['decimals', 'createuser', 'updateuser'], 'integer'],
            [['active'], 'string'],
            [['createdate', 'createtime', 'updatedate', 'updatetime'], 'safe'],
            [['code'], 'string', 'max' => 3],
            [['name', 'txt_singular', 'txt_plural'], 'string', 'max' => 250],
            [['symbol'], 'string', 'max' => 10],
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
            'code' => 'Código único de la moneda',
            'name' => 'Nombre de la moneda',
            'symbol' => 'Símbolo de la moneda',
            'decimals' => 'Decimales de la moneda',
            'txt_singular' => 'Texto en singular de la moneda',
            'txt_plural' => 'Texto en plural de la moneda',
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
     * Gets query for [[Bps]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBps()
    {
        return $this->hasMany(Bp::class, ['currency' => 'code']);
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
