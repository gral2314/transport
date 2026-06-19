<?php

namespace app\models\tables;
    use app\components\behaviors\AuditBehavior;
use Yii;

/**
 * This is the model class for table "bp_contacts".
 *
 * @property string $cardcode Código único del socio de negocios
 * @property string $contact_code Código único del contacto dentro del socio de negocios
 * @property string $name Nombre del contacto
 * @property string|null $last_name Apellido del contacto
 * @property string|null $depto Departamento del contacto
 * @property string $tel Teléfono del contacto
 * @property string $email Correo electrónico del contacto
 * @property string|null $comments Comentarios adicionales
 * @property string $active Registro activo (Y=Sí, N=No)
 * @property string|null $createdate Fecha de creación
 * @property string|null $createtime Hora de creación
 * @property int|null $createuser Usuario que crea
 * @property string|null $updatedate Fecha de actualización
 * @property string|null $updatetime Hora de actualización
 * @property int|null $updateuser Usuario que actualiza
 *
 * @property Bp $cardcode0
 */
class BpContacts extends \yii\db\ActiveRecord
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
        return '{{%bp_contacts}}';
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
            [['last_name', 'depto', 'comments', 'createdate', 'createtime', 'createuser', 'updatedate', 'updatetime', 'updateuser'], 'default', 'value' => null],
            [['active'], 'default', 'value' => 'Y'],
            [['cardcode', 'contact_code', 'name', 'tel', 'email'], 'required'],
            [['active'], 'string'],
            [['createdate', 'createtime', 'updatedate', 'updatetime'], 'safe'],
            [['createuser', 'updateuser'], 'integer'],
            [['cardcode', 'contact_code'], 'string', 'max' => 15],
            [['name', 'last_name'], 'string', 'max' => 100],
            [['depto'], 'string', 'max' => 50],
            [['tel'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 250],
            [['comments'], 'string', 'max' => 300],
            ['active', 'in', 'range' => array_keys(self::optsActive())],
            [['cardcode', 'contact_code'], 'unique', 'targetAttribute' => ['cardcode', 'contact_code']],
            [['cardcode'], 'exist', 'skipOnError' => true, 'targetClass' => Bp::class, 'targetAttribute' => ['cardcode' => 'cardcode']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cardcode' => 'Código único del socio de negocios',
            'contact_code' => 'Código único del contacto dentro del socio de negocios',
            'name' => 'Nombre del contacto',
            'last_name' => 'Apellido del contacto',
            'depto' => 'Departamento del contacto',
            'tel' => 'Teléfono del contacto',
            'email' => 'Correo electrónico del contacto',
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
     * Gets query for [[Cardcode0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCardcode0()
    {
        return $this->hasOne(Bp::class, ['cardcode' => 'cardcode']);
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
