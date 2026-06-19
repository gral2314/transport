<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use yii\db\ActiveRecord;

/**
 * VehicleDocument — ActiveRecord para {{%vehicle_document}} (Documentos del Vehículo)
 *
 * @property string      $vehicle_code
 * @property int         $line_num
 * @property string      $object
 * @property string      $doc_type_code
 * @property string|null $document_no
 * @property string|null $issue_date
 * @property string|null $exp_date
 * @property string|null $next_alert
 * @property string|null $attach
 * @property string|null $notes
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null    $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null    $updateuser
 *
 * @property Vehicle         $vehicle
 * @property DocTypeVehicule $docType
 */
class VehicleDocument extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%vehicle_document}}';
    }

    public static function primaryKey(): array
    {
        return ['vehicle_code', 'line_num'];
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
            [['vehicle_code', 'line_num', 'object', 'doc_type_code'], 'required'],
            [['vehicle_code', 'doc_type_code', 'document_no'], 'string', 'max' => 50],
            [['object'], 'string', 'max' => 50],
            [['attach', 'notes'], 'string'],
            [['line_num'], 'integer'],
            [['issue_date', 'exp_date', 'next_alert'], 'date', 'format' => 'php:Y-m-d'],
            [['vehicle_code'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::class, 'targetAttribute' => ['vehicle_code' => 'vehicle_code']],
            [['doc_type_code'], 'exist', 'skipOnError' => true, 'targetClass' => DocTypeVehicule::class, 'targetAttribute' => ['doc_type_code' => 'code']],
            [['createuser', 'updateuser'], 'integer'],
            [['createdate', 'updatedate'], 'date', 'format' => 'php:Y-m-d'],
            [['createtime', 'updatetime'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'vehicle_code' => 'Unidad',
            'line_num'     => 'Línea',
            'object'       => 'Objeto',
            'doc_type_code'=> 'Tipo Documento',
            'document_no'  => 'No. Documento',
            'issue_date'   => 'Fecha Expedición',
            'exp_date'     => 'Fecha Vencimiento',
            'next_alert'   => 'Próxima Alerta',
            'attach'       => 'Archivo Adjunto',
            'notes'        => 'Notas',
            'createdate'   => 'Fecha Creación',
            'createtime'   => 'Hora Creación',
            'createuser'   => 'Creado por',
            'updatedate'   => 'Fecha Actualización',
            'updatetime'   => 'Hora Actualización',
            'updateuser'   => 'Actualizado por',
        ];
    }

    public function getVehicle(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Vehicle::class, ['vehicle_code' => 'vehicle_code']);
    }

    public function getDocType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(DocTypeVehicule::class, ['code' => 'doc_type_code']);
    }
}
