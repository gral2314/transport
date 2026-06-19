<?php

namespace app\models\tables;
    use app\components\behaviors\AuditBehavior;
use Yii;

/**
 * This is the model class for table "bp".
 *
 * @property string $cardcode Código único del socio de negocios
 * @property string $cardname Nombre del socio de negocios
 * @property string $cardtype Tipo de socio de negocios (C=Cliente, S=Proveedor, L=Lead)
 * @property string|null $lictradnum Número de licencia o identificación fiscal
 * @property int $card_group FK al Grupo al que pertenece el socio de negocios
 * @property string $currency FK a la moneda del socio de negocios
 * @property string $tel Teléfono del socio de negocios
 * @property string $email Correo electrónico del socio de negocios
 * @property int|null $payment_cond Fk Condiciones de pago del socio de negocios
 * @property string|null $payment_method Fk Método de pago del socio de negocios
 * @property string|null $cfdi_use_code Fk Código de uso de CFDI del socio de negocios
 * @property string|null $cfdi_regimen_code Fk Código de régimen de CFDI del socio de negocios
 * @property int|null $vendor_code Fk Código de vendedor del socio de negocios
 * @property string|null $comments Comentarios adicionales
 * @property string $active Registro activo (Y=Sí, N=No)
 * @property string|null $createdate Fecha de creación
 * @property string|null $createtime Hora de creación
 * @property int|null $createuser Usuario que crea
 * @property string|null $updatedate Fecha de actualización
 * @property string|null $updatetime Hora de actualización
 * @property int|null $updateuser Usuario que actualiza
 *
 * @property BpAddress[] $bpAddresses
 * @property BpContacts[] $bpContacts
 * @property GroupSn $cardGroup
 * @property CfdiRegimenFiscal $cfdiRegimenCode
 * @property CfdiUseSn $cfdiUseCode
 * @property Currency $currency0
 * @property PaymentConditions $paymentCond
 * @property PaymentMethods $paymentMethod
 * @property Vendors $vendorCode
 */
class Bp extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const CARDTYPE_C = 'C';
    const CARDTYPE_S = 'S';
    const CARDTYPE_L = 'L';
    const ACTIVE_Y = 'Y';
    const ACTIVE_N = 'N';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bp}}';
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
            [['lictradnum', 'payment_cond', 'payment_method', 'cfdi_use_code', 'cfdi_regimen_code', 'vendor_code', 'comments', 'createdate', 'createtime', 'createuser', 'updatedate', 'updatetime', 'updateuser'], 'default', 'value' => null],
            [['cardtype'], 'default', 'value' => 'C'],
            [['active'], 'default', 'value' => 'Y'],
            [['cardcode', 'cardname', 'card_group', 'currency', 'tel', 'email'], 'required'],
            [['cardtype', 'active'], 'string'],
            [['card_group', 'payment_cond', 'vendor_code', 'createuser', 'updateuser'], 'integer'],
            [['createdate', 'createtime', 'updatedate', 'updatetime'], 'safe'],
            [['cardcode', 'payment_method'], 'string', 'max' => 15],
            [['cardname', 'lictradnum', 'cfdi_use_code', 'cfdi_regimen_code'], 'string', 'max' => 50],
            [['currency'], 'string', 'max' => 3],
            [['tel'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 250],
            [['comments'], 'string', 'max' => 300],
            ['cardtype', 'in', 'range' => array_keys(self::optsCardtype())],
            ['active', 'in', 'range' => array_keys(self::optsActive())],
            [['cardcode'], 'unique'],
            [['card_group'], 'exist', 'skipOnError' => true, 'targetClass' => GroupSn::class, 'targetAttribute' => ['card_group' => 'code']],
            [['cfdi_regimen_code'], 'exist', 'skipOnError' => true, 'targetClass' => CfdiRegimenFiscal::class, 'targetAttribute' => ['cfdi_regimen_code' => 'code']],
            [['cfdi_use_code'], 'exist', 'skipOnError' => true, 'targetClass' => CfdiUseSn::class, 'targetAttribute' => ['cfdi_use_code' => 'code']],
            [['currency'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::class, 'targetAttribute' => ['currency' => 'code']],
            [['payment_cond'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentConditions::class, 'targetAttribute' => ['payment_cond' => 'code']],
            [['payment_method'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentMethods::class, 'targetAttribute' => ['payment_method' => 'code']],
            [['vendor_code'], 'exist', 'skipOnError' => true, 'targetClass' => Vendors::class, 'targetAttribute' => ['vendor_code' => 'code']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cardcode' => 'Código único del socio de negocios',
            'cardname' => 'Nombre del socio de negocios',
            'cardtype' => 'Tipo de socio de negocios (C=Cliente, S=Proveedor, L=Lead)',
            'lictradnum' => 'Número de licencia o identificación fiscal',
            'card_group' => 'FK al Grupo al que pertenece el socio de negocios',
            'currency' => 'FK a la moneda del socio de negocios',
            'tel' => 'Teléfono del socio de negocios',
            'email' => 'Correo electrónico del socio de negocios',
            'payment_cond' => 'Fk Condiciones de pago del socio de negocios',
            'payment_method' => 'Fk Método de pago del socio de negocios',
            'cfdi_use_code' => 'Fk Código de uso de CFDI del socio de negocios',
            'cfdi_regimen_code' => 'Fk Código de régimen de CFDI del socio de negocios',
            'vendor_code' => 'Fk Código de vendedor del socio de negocios',
            'comments' => 'Comentarios adicionales',
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
     * Gets query for [[BpAddresses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBpAddresses()
    {
        return $this->hasMany(BpAddress::class, ['cardcode' => 'cardcode']);
    }

    /**
     * Gets query for [[BpContacts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBpContacts()
    {
        return $this->hasMany(BpContacts::class, ['cardcode' => 'cardcode']);
    }

    /**
     * Gets query for [[CardGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCardGroup()
    {
        return $this->hasOne(GroupSn::class, ['code' => 'card_group']);
    }

    /**
     * Gets query for [[CfdiRegimenCode]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCfdiRegimenCode()
    {
        return $this->hasOne(CfdiRegimenFiscal::class, ['code' => 'cfdi_regimen_code']);
    }

    /**
     * Gets query for [[CfdiUseCode]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCfdiUseCode()
    {
        return $this->hasOne(CfdiUseSn::class, ['code' => 'cfdi_use_code']);
    }

    /**
     * Gets query for [[Currency0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency0()
    {
        return $this->hasOne(Currency::class, ['code' => 'currency']);
    }

    /**
     * Gets query for [[PaymentCond]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentCond()
    {
        return $this->hasOne(PaymentConditions::class, ['code' => 'payment_cond']);
    }

    /**
     * Gets query for [[PaymentMethod]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethod()
    {
        return $this->hasOne(PaymentMethods::class, ['code' => 'payment_method']);
    }

    /**
     * Gets query for [[VendorCode]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendorCode()
    {
        return $this->hasOne(Vendors::class, ['code' => 'vendor_code']);
    }


    /**
     * column cardtype ENUM value labels
     * @return string[]
     */
    public static function optsCardtype()
    {
        return [
            self::CARDTYPE_C => 'C',
            self::CARDTYPE_S => 'S',
            self::CARDTYPE_L => 'L',
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
    public function displayCardtype()
    {
        return self::optsCardtype()[$this->cardtype];
    }

    /**
     * @return bool
     */
    public function isCardtypeC()
    {
        return $this->cardtype === self::CARDTYPE_C;
    }

    public function setCardtypeToC()
    {
        $this->cardtype = self::CARDTYPE_C;
    }

    /**
     * @return bool
     */
    public function isCardtypeS()
    {
        return $this->cardtype === self::CARDTYPE_S;
    }

    public function setCardtypeToS()
    {
        $this->cardtype = self::CARDTYPE_S;
    }

    /**
     * @return bool
     */
    public function isCardtypeL()
    {
        return $this->cardtype === self::CARDTYPE_L;
    }

    public function setCardtypeToL()
    {
        $this->cardtype = self::CARDTYPE_L;
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
