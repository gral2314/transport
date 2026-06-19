<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use app\models\system\User;
use yii\db\ActiveRecord;

class DocTireDisposalDetail extends ActiveRecord
{
    public const DISPOSAL_REASON_WEAR = 'WEAR';
    public const DISPOSAL_REASON_DAMAGE = 'DAMAGE';
    public const DISPOSAL_REASON_ACCIDENT = 'ACCIDENT';
    public const DISPOSAL_REASON_THEFT = 'THEFT';
    public const DISPOSAL_REASON_RETREAD_LIMIT = 'RETREAD_LIMIT';
    public const DISPOSAL_REASON_SIDEWALL_DAMAGE = 'SIDEWALL_DAMAGE';
    public const DISPOSAL_REASON_OTHER = 'OTHER';

    public const INDEXES = [
        'ux_doc_tire_disposal_detail_docentry_linenum' => ['columns' => ['docentry', 'linenum'], 'unique' => true],
        'idx_doc_tire_disposal_detail_tire_code' => ['columns' => ['tire_code'], 'unique' => false],
        'idx_doc_tire_disposal_detail_reason' => ['columns' => ['disposal_reason'], 'unique' => false],
    ];

    public static function tableName(): string
    {
        return '{{%doc_tire_disposal_detail}}';
    }

    public static function primaryKey(): array
    {
        return ['id'];
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
            [['docentry', 'linenum', 'tire_code'], 'required'],
            [['id', 'docentry', 'linenum', 'createuser', 'updateuser'], 'integer'],
            [['scrap_value'], 'number'],
            [['createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
            [['tire_code'], 'string', 'max' => 50],
            [['comments'], 'string', 'max' => 300],

            [['docentry', 'linenum'], 'unique', 'targetAttribute' => ['docentry', 'linenum']],

            [['disposal_reason'], 'in', 'range' => [
                self::DISPOSAL_REASON_WEAR,
                self::DISPOSAL_REASON_DAMAGE,
                self::DISPOSAL_REASON_ACCIDENT,
                self::DISPOSAL_REASON_THEFT,
                self::DISPOSAL_REASON_RETREAD_LIMIT,
                self::DISPOSAL_REASON_SIDEWALL_DAMAGE,
                self::DISPOSAL_REASON_OTHER,
            ]],
            [['disposal_reason'], 'default', 'value' => self::DISPOSAL_REASON_OTHER],

            [['docentry'], 'exist', 'skipOnError' => true, 'targetClass' => DocTireDisposal::class, 'targetAttribute' => ['docentry' => 'docentry']],
            [['tire_code'], 'exist', 'skipOnError' => true, 'targetClass' => Tire::class, 'targetAttribute' => ['tire_code' => 'tire_code']],
            [['createuser'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['createuser' => 'id']],
            [['updateuser'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updateuser' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'Id',
            'docentry' => 'Docentry',
            'linenum' => 'Número Línea',
            'tire_code' => 'Llanta',
            'disposal_reason' => 'Motivo Baja',
            'scrap_value' => 'Valor Recuperación',
            'comments' => 'Comentarios',
            'createdate' => 'Fecha Creación',
            'createtime' => 'Hora Creación',
            'createuser' => 'Creado Por',
            'updatedate' => 'Fecha Actualización',
            'updatetime' => 'Hora Actualización',
            'updateuser' => 'Actualizado Por',
        ];
    }

    public function getDocument(): \yii\db\ActiveQuery
    {
        return $this->hasOne(DocTireDisposal::class, ['docentry' => 'docentry']);
    }

    public function getTire(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Tire::class, ['tire_code' => 'tire_code']);
    }

    public function getCreateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'createuser']);
    }

    public function getUpdateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'updateuser']);
    }

    public function getAttachments(): \yii\db\ActiveQuery
    {
        return $this->hasMany(DocTireDisposalAttach::class, ['docentry' => 'docentry', 'linenum' => 'linenum']);
    }
}
