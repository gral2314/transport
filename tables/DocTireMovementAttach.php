<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use app\models\system\User;
use yii\db\ActiveRecord;

class DocTireMovementAttach extends ActiveRecord
{
    public const INDEXES = [
        'ux_doc_tire_movement_attach_docentry_linenum' => ['columns' => ['docentry', 'linenum'], 'unique' => true],
    ];

    public static function tableName(): string
    {
        return '{{%doc_tire_movement_attach}}';
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
            [['docentry', 'linenum', 'filename', 'filepath'], 'required'],
            [['id', 'docentry', 'linenum', 'createuser', 'updateuser'], 'integer'],
            [['notes'], 'string'],
            [['createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
            [['filename'], 'string', 'max' => 255],
            [['filepath'], 'string', 'max' => 500],

            [['docentry', 'linenum'], 'unique', 'targetAttribute' => ['docentry', 'linenum']],

            [['docentry'], 'exist', 'skipOnError' => true, 'targetClass' => DocTireMovement::class, 'targetAttribute' => ['docentry' => 'docentry']],
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
            'filename' => 'Nombre Archivo',
            'filepath' => 'Ruta Archivo',
            'notes' => 'Notas',
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
        return $this->hasOne(DocTireMovement::class, ['docentry' => 'docentry']);
    }

    public function getDetail(): \yii\db\ActiveQuery
    {
        return $this->hasOne(DocTireMovementDetail::class, ['docentry' => 'docentry', 'linenum' => 'linenum']);
    }

    public function getCreateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'createuser']);
    }

    public function getUpdateUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'updateuser']);
    }
}
