<?php

namespace app\models\system;

use Yii;
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\db\Command;
/**
 * This is the model class for table "company".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $namecomercial
 * @property string|null $calle
 * @property string|null $noext
 * @property string|null $noint
 * @property string|null $colonia
 * @property string|null $delegacion
 * @property int|null $codigopostal
 * @property string|null $ciudad
 * @property string|null $estado
 * @property string|null $pais
 * @property string|null $rfc
 * @property string|null $regimen
 */
class Company extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigopostal'], 'integer'],
            [['name', 'namecomercial'], 'string', 'max' => 250],
            [['calle', 'colonia', 'delegacion', 'ciudad', 'estado'], 'string', 'max' => 100],
            [['noext', 'noint', 'pais'], 'string', 'max' => 50],
            [['rfc'], 'string', 'max' => 13],
            [['regimen'], 'string', 'max' => 3],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'namecomercial' => 'Namecomercial',
            'calle' => 'Calle',
            'noext' => 'Noext',
            'noint' => 'Noint',
            'colonia' => 'Colonia',
            'delegacion' => 'Delegacion',
            'codigopostal' => 'Codigopostal',
            'ciudad' => 'Ciudad',
            'estado' => 'Estado',
            'pais' => 'Pais',
            'rfc' => 'Rfc',
            'regimen' => 'Regimen',
        ];
    }
}
