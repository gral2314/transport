<?php

namespace app\models\tables;
    use app\components\behaviors\AuditBehavior;
use Yii;

/**
 * This is the model class for table "bp_address".
 *
 * @property string $cardcode Código único del socio de negocios
 * @property string $address_type Tipo de dirección (B=Billing, S=Shipping)
 * @property string $address_code Código único de la dirección dentro del socio de negocios
 * @property string $street Calle de la dirección
 * @property string $city Ciudad de la dirección
 * @property string|null $state_code FK Código del estado de la dirección
 * @property string|null $zip Código postal de la dirección
 * @property string $country_code FK Código del país de la dirección
 * @property string $active Registro activo (Y=Sí, N=No)
 * @property string|null $createdate Fecha de creación
 * @property string|null $createtime Hora de creación
 * @property int|null $createuser Usuario que crea
 * @property string|null $updatedate Fecha de actualización
 * @property string|null $updatetime Hora de actualización
 * @property int|null $updateuser Usuario que actualiza
 *
 * @property Bp $cardcode0
 * @property States $stateCode
 */
class BpAddress extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ADDRESS_TYPE_B = 'B';
    const ADDRESS_TYPE_S = 'S';
    const ACTIVE_Y = 'Y';
    const ACTIVE_N = 'N';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bp_address}}';
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
            [['state_code', 'zip', 'createdate', 'createtime', 'createuser', 'updatedate', 'updatetime', 'updateuser'], 'default', 'value' => null],
            [['address_type'], 'default', 'value' => 'B'],
            [['active'], 'default', 'value' => 'Y'],
            [['cardcode', 'address_code', 'street', 'city', 'country_code'], 'required'],
            [['address_type', 'active'], 'string'],
            [['createdate', 'createtime', 'updatedate', 'updatetime'], 'safe'],
            [['createuser', 'updateuser'], 'integer'],
            [['cardcode', 'address_code'], 'string', 'max' => 15],
            [['street'], 'string', 'max' => 150],
            [['city'], 'string', 'max' => 100],
            [['state_code'], 'string', 'max' => 50],
            [['zip', 'country_code'], 'string', 'max' => 10],
            ['address_type', 'in', 'range' => array_keys(self::optsAddressType())],
            ['active', 'in', 'range' => array_keys(self::optsActive())],
            [['cardcode', 'address_code'], 'unique', 'targetAttribute' => ['cardcode', 'address_code']],
            [['cardcode'], 'exist', 'skipOnError' => true, 'targetClass' => Bp::class, 'targetAttribute' => ['cardcode' => 'cardcode']],
            [['state_code', 'country_code'], 'exist', 'skipOnError' => true, 'targetClass' => States::class, 'targetAttribute' => ['state_code' => 'code', 'country_code' => 'country']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cardcode' => 'Código único del socio de negocios',
            'address_type' => 'Tipo de dirección (B=Billing, S=Shipping)',
            'address_code' => 'Código único de la dirección dentro del socio de negocios',
            'street' => 'Calle de la dirección',
            'city' => 'Ciudad de la dirección',
            'state_code' => 'FK Código del estado de la dirección',
            'zip' => 'Código postal de la dirección',
            'country_code' => 'FK Código del país de la dirección',
            'active' => 'Registro activo (Y=Sí, N=No)',
            'createdate' => 'Fecha de creación',
            'createtime' => 'Hora de creación',
            'createuser' => 'Usuario que crea',
            'updatedate' => 'Fecha de actualización',
            'updatetime' => 'Hora de actualización',
            'updateuser' => 'Usuario que actualiza',
        ];
    }

    /**
     * Gets query for [[Cardcode0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCardcode0()
    {
        return $this->hasOne(Bp::class, ['cardcode' => 'cardcode']);
    }

    /**
     * Gets query for [[StateCode]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStateCode()
    {
        return $this->hasOne(States::class, ['code' => 'state_code', 'country' => 'country_code']);
    }


    /**
     * column address_type ENUM value labels
     * @return string[]
     */
    public static function optsAddressType()
    {
        return [
            self::ADDRESS_TYPE_B => 'B',
            self::ADDRESS_TYPE_S => 'S',
        ];
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
    public function displayAddressType()
    {
        return self::optsAddressType()[$this->address_type];
    }

    /**
     * @return bool
     */
    public function isAddressTypeB()
    {
        return $this->address_type === self::ADDRESS_TYPE_B;
    }

    public function setAddressTypeToB()
    {
        $this->address_type = self::ADDRESS_TYPE_B;
    }

    /**
     * @return bool
     */
    public function isAddressTypeS()
    {
        return $this->address_type === self::ADDRESS_TYPE_S;
    }

    public function setAddressTypeToS()
    {
        $this->address_type = self::ADDRESS_TYPE_S;
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
