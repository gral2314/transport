<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use app\models\system\User;
use yii\db\ActiveRecord;

class DocTireDisposal extends ActiveRecord
{
    public const DOC_STATUS_O = 'O';
    public const DOC_STATUS_C = 'C';

    public const STATUS_PLAN = 'PLAN';
    public const STATUS_EXEC = 'EXEC';
    public const STATUS_VAL = 'VAL';
    public const STATUS_CLOSE = 'CLOSE';

    public const CANCELED_Y = 'Y';
    public const CANCELED_N = 'N';

    public const INDEXES = [
        'ux_doc_tire_disposal_docnum' => ['columns' => ['docnum'], 'unique' => true],
        'idx_doc_tire_disposal_status' => ['columns' => ['status'], 'unique' => false],
        'idx_doc_tire_disposal_doc_status' => ['columns' => ['doc_status'], 'unique' => false],
        'idx_doc_tire_disposal_disposal_date' => ['columns' => ['disposal_date'], 'unique' => false],
    ];

    public static function tableName(): string
    {
        return '{{%doc_tire_disposal}}';
    }

    public static function primaryKey(): array
    {
        return ['docentry'];
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
            [['docnum', 'doc_date', 'disposal_date'], 'required'],
            [['docentry', 'series_id', 'createuser', 'updateuser'], 'integer'],
            [['doc_date', 'disposal_date', 'createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
            [['docnum'], 'string', 'max' => 30],
            [['comments'], 'string', 'max' => 300],

            [['docnum'], 'unique'],

            [['doc_status'], 'in', 'range' => [self::DOC_STATUS_O, self::DOC_STATUS_C]],
            [['doc_status'], 'default', 'value' => self::DOC_STATUS_O],
            [['status'], 'in', 'range' => [self::STATUS_PLAN, self::STATUS_EXEC, self::STATUS_VAL, self::STATUS_CLOSE]],
            [['status'], 'default', 'value' => self::STATUS_PLAN],
            [['canceled'], 'in', 'range' => [self::CANCELED_Y, self::CANCELED_N]],
            [['canceled'], 'default', 'value' => self::CANCELED_N],

            [['series_id'], 'exist', 'skipOnError' => true, 'targetClass' => Series::class, 'targetAttribute' => ['series_id' => 'id']],
            [['createuser'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['createuser' => 'id']],
            [['updateuser'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updateuser' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'docentry' => 'Docentry',
            'docnum' => 'Número Documento',
            'doc_date' => 'Fecha Documento',
            'doc_status' => 'Estado Documento',
            'status' => 'Estado Operativo',
            'canceled' => 'Cancelado',
            'disposal_date' => 'Fecha Baja',
            'comments' => 'Comentarios',
            'series_id' => 'Serie',
            'createdate' => 'Fecha Creación',
            'createtime' => 'Hora Creación',
            'createuser' => 'Creado Por',
            'updatedate' => 'Fecha Actualización',
            'updatetime' => 'Hora Actualización',
            'updateuser' => 'Actualizado Por',
        ];
    }

    public function getSeries(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Series::class, ['id' => 'series_id']);
    }

    public function getCreateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'createuser']);
    }

    public function getUpdateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'updateuser']);
    }

    public function getDetails(): \yii\db\ActiveQuery
    {
        return $this->hasMany(DocTireDisposalDetail::class, ['docentry' => 'docentry'])->orderBy(['linenum' => SORT_ASC]);
    }

    public function getAttachments(): \yii\db\ActiveQuery
    {
        return $this->hasMany(DocTireDisposalAttach::class, ['docentry' => 'docentry'])->orderBy(['linenum' => SORT_ASC]);
    }
}
