<?php

declare(strict_types=1);

namespace app\models\tables;

use app\components\behaviors\AuditBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Modelo para la tabla {{%doc_series}}.
 *
 * Gestiona las series de numeración para documentos.
 *
 * @property int $id
 * @property string $name
 * @property string $object_name
 * @property string $prefix
 * @property string $suffix
 * @property int $padding_length
 * @property int $current_consecutive
 * @property int $max_consecutive
 * @property string $is_active
 * @property string $is_default
 * @property string $createdate
 * @property string $createtime
 * @property string $createuser
 * @property string $updatedate
 * @property string $updatetime
 * @property string $updateuser
 *
 * @property DocTireMovement[] $docTireMovements
 * @property DocTireDisposal[] $docTireDisposals
 * @property DocTireRepair[] $docTireRepairs
 */
class Series extends ActiveRecord
{
    /** @var string Activo */
    public const ACTIVE_Y = 'Y';
    /** @var string Inactivo */
    public const ACTIVE_N = 'N';

    /** @var string Default */
    public const IS_DEFAULT_Y = 'Y';
    /** @var string No default */
    public const IS_DEFAULT_N = 'N';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%doc_series}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'audit' => [
                'class' => AuditBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'object_name'], 'required'],
            [['name', 'object_name'], 'string', 'max' => 50],
            [['prefix', 'suffix'], 'string', 'max' => 10],
            [['padding_length', 'current_consecutive', 'max_consecutive'], 'integer'],
            [['padding_length'], 'default', 'value' => 6],
            [['current_consecutive'], 'default', 'value' => 0],
            [['max_consecutive'], 'default', 'value' => 999999],
            [['prefix', 'suffix'], 'default', 'value' => ''],
            [['is_active'], 'default', 'value' => self::ACTIVE_Y],
            [['is_active'], 'in', 'range' => [self::ACTIVE_Y, self::ACTIVE_N]],
            [['is_default'], 'default', 'value' => self::IS_DEFAULT_N],
            [['is_default'], 'in', 'range' => [self::IS_DEFAULT_Y, self::IS_DEFAULT_N]],
            [['object_name', 'prefix', 'suffix'], 'unique', 'targetAttribute' => ['object_name', 'prefix', 'suffix']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Nombre',
            'object_name' => 'Objeto',
            'prefix' => 'Prefijo',
            'suffix' => 'Sufijo',
            'padding_length' => 'Relleno',
            'current_consecutive' => 'Consecutivo actual',
            'max_consecutive' => 'Consecutivo máximo',
            'is_active' => 'Activo',
            'is_default' => 'Default',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────────────

    /**
     * @return ActiveQuery
     */
    public function getDocTireMovements(): ActiveQuery
    {
        return $this->hasMany(DocTireMovement::class, ['series_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getDocTireDisposals(): ActiveQuery
    {
        return $this->hasMany(DocTireDisposal::class, ['series_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getDocTireRepairs(): ActiveQuery
    {
        return $this->hasMany(DocTireRepair::class, ['series_id' => 'id']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    /**
     * Obtener lista [id => name] para dropdowns.
     *
     * @param string|null $objectName Filtrar por objeto (opcional)
     * @return array
     */
    public static function getDropdownList(?string $objectName = null): array
    {
        $query = static::find()
            ->select(['id', 'name'])
            ->where(['is_active' => self::ACTIVE_Y])
            ->orderBy(['name' => SORT_ASC]);

        if ($objectName !== null) {
            $query->andWhere(['object_name' => $objectName]);
        }

        return $query->all();
    }

    /**
     * Obtener el ID de la serie activa por defecto para un objeto.
     *
     * @param string $objectName Nombre del objeto (ej: DocTireMovement)
     * @return int|null ID de la serie default, o null si no hay
     */
    public static function getDefaultId(string $objectName): ?int
    {
        $model = static::find()
            ->where([
                'object_name' => $objectName,
                'is_active' => self::ACTIVE_Y,
                'is_default' => self::IS_DEFAULT_Y,
            ])
            ->one();

        return $model !== null ? (int)$model->id : null;
    }

    /**
     * Obtener opciones activas para Select2.
     *
     * @param string|null $objectName Filtrar por objeto (opcional)
     * @return array
     */
    public static function getActiveOptions(?string $objectName = null): array
    {
        $query = static::find()
            ->where(['is_active' => self::ACTIVE_Y])
            ->orderBy(['name' => SORT_ASC]);

        if ($objectName !== null) {
            $query->andWhere(['object_name' => $objectName]);
        }

        $result = [];
        foreach ($query->all() as $model) {
            $result[] = [
                'value' => $model->id,
                'label' => $model->name,
            ];
        }
        return $result;
    }
}
