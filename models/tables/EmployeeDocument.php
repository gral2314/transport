<?php

declare(strict_types=1);

namespace app\models\tables;

use yii\db\ActiveRecord;

/**
 * EmployeeDocument — ActiveRecord para {{%employee_document}} (Control documental del empleado)
 *
 * @property string      $employee_code
 * @property string      $document_type_code
 * @property string      $delivered
 * @property string|null $expiration_date
 * @property string      $active
 * @property string|null $notes
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 *
 * @property Employee             $employee
 * @property DocumentTypeCatalog  $documentType
 */
class EmployeeDocument extends ActiveRecord
{
    public const DELIVERED_Y = 'Y';
    public const DELIVERED_N = 'N';

    public const ACTIVE_Y = 'Y';
    public const ACTIVE_N = 'N';

    public static function tableName(): string
    {
        return '{{%employee_document}}';
    }

    public static function primaryKey(): array
    {
        return ['employee_code', 'document_type_code'];
    }

    public function rules(): array
    {
        return [
            [['employee_code', 'document_type_code'], 'required'],
            [['employee_code', 'document_type_code'], 'string', 'max' => 50],
            [['expiration_date'], 'safe'],
            [['delivered', 'active'], 'string'],
            [['delivered'], 'in', 'range' => [self::DELIVERED_Y, self::DELIVERED_N]],
            [['active'], 'in', 'range' => [self::ACTIVE_Y, self::ACTIVE_N]],
            [['notes'], 'string', 'max' => 300],
            [['createdate', 'updatedate'], 'safe'],
            [['createtime', 'updatetime'], 'safe'],
            [['createuser', 'updateuser'], 'integer'],
            [['employee_code'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::class, 'targetAttribute' => ['employee_code' => 'employee_code']],
            [['document_type_code'], 'exist', 'skipOnError' => true, 'targetClass' => DocumentTypeCatalog::class, 'targetAttribute' => ['document_type_code' => 'code']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'employee_code' => 'Código de Empleado',
            'document_type_code' => 'Tipo de Documento',
            'delivered' => 'Entregado',
            'expiration_date' => 'Fecha de Vencimiento',
            'active' => 'Activo',
            'notes' => 'Observaciones',
            'createdate' => 'Fecha de Creación',
            'createtime' => 'Hora de Creación',
            'createuser' => 'Creado Por',
            'updatedate' => 'Fecha de Actualización',
            'updatetime' => 'Hora de Actualización',
            'updateuser' => 'Actualizado Por',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::class, ['employee_code' => 'employee_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentType()
    {
        return $this->hasOne(DocumentTypeCatalog::class, ['code' => 'document_type_code']);
    }
}
