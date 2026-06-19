<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use yii\db\ActiveRecord;

/**
 * AxleTypeConfig — ActiveRecord para {{%axle_type_config}} (Configuración de Posiciones por Tipo de Eje)
 *
 * @property string      $code
 * @property string      $line_num
 * @property string      $name
 * @property string      $pos_code
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 *
 * @property AxleType $axleType
 */
class AxleTypeConfig extends ActiveRecord
{
    // Posiciones de llanta
    public const POS_LI = 'LI'; // Left Inner
    public const POS_LO = 'LO'; // Left Outer
    public const POS_RI = 'RI'; // Right Inner
    public const POS_RO = 'RO'; // Right Outer
    public const POS_LS = 'LS'; // Left Single
    public const POS_RS = 'RS'; // Right Single
    
    // Posiciones de llanta - Tandem Dual (8 llantas)
    public const POS_LI1 = 'LI1'; // Left Inner Axle 1
    public const POS_LO1 = 'LO1'; // Left Outer Axle 1
    public const POS_RI1 = 'RI1'; // Right Inner Axle 1
    public const POS_RO1 = 'RO1'; // Right Outer Axle 1
    public const POS_LI2 = 'LI2'; // Left Inner Axle 2
    public const POS_LO2 = 'LO2'; // Left Outer Axle 2
    public const POS_RI2 = 'RI2'; // Right Inner Axle 2
    public const POS_RO2 = 'RO2'; // Right Outer Axle 2
    
    // Posiciones de llanta - Tandem Triple (12 llantas, incluye las de arriba más estas)
    public const POS_LI3 = 'LI3'; // Left Inner Axle 3
    public const POS_LO3 = 'LO3'; // Left Outer Axle 3
    public const POS_RI3 = 'RI3'; // Right Inner Axle 3
    public const POS_RO3 = 'RO3'; // Right Outer Axle 3

    public static function tableName(): string
    {
        return '{{%axle_type_config}}';
    }

    public static function primaryKey(): array
    {
        return ['code', 'line_num'];
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
            [['code', 'line_num', 'name', 'pos_code'], 'required'],
            [['code', 'line_num'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 100],
            [['pos_code'], 'in', 'range' => [
                self::POS_LI, self::POS_LO, self::POS_RI, 
                self::POS_RO, self::POS_LS, self::POS_RS,
                self::POS_LI1,self::POS_LO1,self::POS_RI1,self::POS_RO1,
                self::POS_LI2,self::POS_LO2,self::POS_RI2,self::POS_RO2,
                self::POS_LI3,self::POS_LO3,self::POS_RI3,self::POS_RO3,
            ]],
            [['createuser', 'updateuser'], 'integer'],
            [['createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
            [['code'], 'exist', 'skipOnError' => true,
                'targetClass' => AxleType::class, 'targetAttribute' => ['code' => 'code']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'code'       => 'Código Tipo Eje',
            'line_num'   => 'Número Línea',
            'name'       => 'Nombre',
            'pos_code'   => 'Posición',
            'createdate' => 'Fecha Creación',
            'createtime' => 'Hora Creación',
            'createuser' => 'Creado por',
            'updatedate' => 'Fecha Actualización',
            'updatetime' => 'Hora Actualización',
            'updateuser' => 'Actualizado por',
        ];
    }

    public static function getPosCodeOptions(): array
    {
        return [
            self::POS_LI => 'Left Inner',
            self::POS_LO => 'Left Outer',
            self::POS_RI => 'Right Inner',
            self::POS_RO => 'Right Outer',
            self::POS_LS => 'Left Single',
            self::POS_RS => 'Right Single',
            self::POS_LI1 => 'Left Inner Axle 1',
            self::POS_LO1 => 'Left Outer Axle 1',
            self::POS_RI1 => 'Right Inner Axle 1',
            self::POS_RO1 => 'Right Outer Axle 1',
            self::POS_LI2 => 'Left Inner Axle 2',
            self::POS_LO2 => 'Left Outer Axle 2',
            self::POS_RI2 => 'Right Inner Axle 2',
            self::POS_RO2 => 'Right Outer Axle 2',
            self::POS_LI3 => 'Left Inner Axle 3',
            self::POS_LO3 => 'Left Outer Axle 3',
            self::POS_RI3 => 'Right Inner Axle 3',
            self::POS_RO3 => 'Right Outer Axle 3',
            
        ];
    }

    public function getPosCodeLabel(): string
    {
        return self::getPosCodeOptions()[$this->pos_code] ?? $this->pos_code;
    }

    // ── Relaciones ─────────────────────────────────────────────────

    public function getAxleType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(AxleType::class, ['code' => 'code']);
    }
}
