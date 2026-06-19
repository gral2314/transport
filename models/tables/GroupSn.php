<?php

namespace app\models\tables;
use app\components\behaviors\AuditBehavior;
use Yii;

/**
 * This is the model class for table "group_sn".
 *
 * @property int $code Código único del grupo
 * @property string $name Nombre del grupo
 * @property string $cardtype Tipo de socio de negocios (C=Cliente, S=Proveedor)
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
class GroupSn extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const CARDTYPE_C = 'C';
    const CARDTYPE_S = 'S';
    const ACTIVE_Y = 'Y';
    const ACTIVE_N = 'N';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%group_sn}}';
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
            [['cardtype'], 'default', 'value' => 'C'],
            [['active'], 'default', 'value' => 'Y'],
            [['code', 'name'], 'required'],
            [['code', 'createuser', 'updateuser'], 'integer'],
            [['cardtype','active'], 'string'],
            [['createdate', 'createtime', 'updatedate', 'updatetime'], 'safe'],
            [['name'], 'string', 'max' => 200],
            ['cardtype', 'in', 'range' => array_keys(self::optsCardtype())],
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
            'code' => 'Código único del grupo',
            'name' => 'Nombre del grupo',
            'cardtype' => 'Tipo de socio de negocios (C=Cliente, S=Proveedor)',
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
        return $this->hasMany(Bp::class, ['card_group' => 'code']);
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
