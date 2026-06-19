<?php

namespace app\models\tables;
    use app\components\behaviors\AuditBehavior;
use Yii;

/**
 * This is the model class for table "items".
 *
 * @property string $itemcode FK del empleado
 * @property string $itemname FK al catálogo de tipos de documento
 * @property int $item_group FK Código del país de la dirección
 * @property string $is_inventory Articulo de inventario (Y=Sí, N=No)
 * @property string $is_purchase Articulo de compra (Y=Sí, N=No)
 * @property string $is_sales Articulo de venta (Y=Sí, N=No)
 * @property string $tire_code FK Código de llanta plantilla
 * @property string $uom_purchase Unidad de medida para compras
 * @property string $uom_sales Unidad de medida para ventas
 * @property string $uom_inventory Unidad de medida para inventario
 * @property string $active Registro activo (Y=Sí, N=No)
 * @property string|null $notes Observaciones
 * @property string|null $createdate Fecha de creación
 * @property string|null $createtime Hora de creación
 * @property int|null $createuser Usuario que crea
 * @property string|null $updatedate Fecha de actualización
 * @property string|null $updatetime Hora de actualización
 * @property int|null $updateuser Usuario que actualiza
 *
 * @property GroupItems $itemGroup
 * @property ItemWarehouse[] $itemWarehouses
 * @property Tire $tireCode
 * @property Warehouse[] $warehouseCodes
 */
class Items extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const IS_INVENTORY_Y = 'Y';
    const IS_INVENTORY_N = 'N';
    const IS_PURCHASE_Y = 'Y';
    const IS_PURCHASE_N = 'N';
    const IS_SALES_Y = 'Y';
    const IS_SALES_N = 'N';
    const ACTIVE_Y = 'Y';
    const ACTIVE_N = 'N';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%items}}';
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
            [['notes', 'createdate', 'createtime', 'createuser', 'updatedate', 'updatetime', 'updateuser'], 'default', 'value' => null],
            [['active'], 'default', 'value' => 'Y'],
            [['itemcode', 'itemname', 'item_group', 'tire_code', 'uom_purchase', 'uom_sales', 'uom_inventory'], 'required'],
            [['item_group', 'createuser', 'updateuser'], 'integer'],
            [['is_inventory', 'is_purchase', 'is_sales', 'active'], 'string'],
            [['createdate', 'createtime', 'updatedate', 'updatetime'], 'safe'],
            [['itemcode'], 'string', 'max' => 100],
            [['itemname'], 'string', 'max' => 200],
            [['tire_code', 'uom_purchase', 'uom_sales', 'uom_inventory'], 'string', 'max' => 50],
            [['notes'], 'string', 'max' => 300],
            ['is_inventory', 'in', 'range' => array_keys(self::optsIsInventory())],
            ['is_purchase', 'in', 'range' => array_keys(self::optsIsPurchase())],
            ['is_sales', 'in', 'range' => array_keys(self::optsIsSales())],
            ['active', 'in', 'range' => array_keys(self::optsActive())],
            [['itemcode'], 'unique'],
            [['item_group'], 'exist', 'skipOnError' => true, 'targetClass' => GroupItems::class, 'targetAttribute' => ['item_group' => 'code']],
            [['tire_code'], 'exist', 'skipOnError' => true, 'targetClass' => Tire::class, 'targetAttribute' => ['tire_code' => 'tire_code']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'itemcode' => 'FK del empleado',
            'itemname' => 'FK al catálogo de tipos de documento',
            'item_group' => 'FK Código del país de la dirección',
            'is_inventory' => 'Articulo de inventario (Y=Sí, N=No)',
            'is_purchase' => 'Articulo de compra (Y=Sí, N=No)',
            'is_sales' => 'Articulo de venta (Y=Sí, N=No)',
            'tire_code' => 'FK Código de llanta plantilla',
            'uom_purchase' => 'Unidad de medida para compras',
            'uom_sales' => 'Unidad de medida para ventas',
            'uom_inventory' => 'Unidad de medida para inventario',
            'active' => 'Registro activo (Y=Sí, N=No)',
            'notes' => 'Observaciones',
            'createdate' => 'Fecha de creación',
            'createtime' => 'Hora de creación',
            'createuser' => 'Usuario que crea',
            'updatedate' => 'Fecha de actualización',
            'updatetime' => 'Hora de actualización',
            'updateuser' => 'Usuario que actualiza',
        ];
    }

    /**
     * Gets query for [[ItemGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemGroup()
    {
        return $this->hasOne(GroupItems::class, ['code' => 'item_group']);
    }

    /**
     * Gets query for [[ItemWarehouses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemWarehouses()
    {
        return $this->hasMany(ItemWarehouse::class, ['itemcode' => 'itemcode']);
    }

    /**
     * Gets query for [[TireCode]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTireCode()
    {
        return $this->hasOne(Tire::class, ['tire_code' => 'tire_code']);
    }

    /**
     * Gets query for [[WarehouseCodes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseCodes()
    {
        return $this->hasMany(Warehouse::class, ['code' => 'warehouse_code'])->viaTable('item_warehouse', ['itemcode' => 'itemcode']);
    }


    /**
     * column is_inventory ENUM value labels
     * @return string[]
     */
    public static function optsIsInventory()
    {
        return [
            self::IS_INVENTORY_Y => 'Y',
            self::IS_INVENTORY_N => 'N',
        ];
    }

    /**
     * column is_purchase ENUM value labels
     * @return string[]
     */
    public static function optsIsPurchase()
    {
        return [
            self::IS_PURCHASE_Y => 'Y',
            self::IS_PURCHASE_N => 'N',
        ];
    }

    /**
     * column is_sales ENUM value labels
     * @return string[]
     */
    public static function optsIsSales()
    {
        return [
            self::IS_SALES_Y => 'Y',
            self::IS_SALES_N => 'N',
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
    public function displayIsInventory()
    {
        return self::optsIsInventory()[$this->is_inventory];
    }

    /**
     * @return bool
     */
    public function isIsInventoryY()
    {
        return $this->is_inventory === self::IS_INVENTORY_Y;
    }

    public function setIsInventoryToY()
    {
        $this->is_inventory = self::IS_INVENTORY_Y;
    }

    /**
     * @return bool
     */
    public function isIsInventoryN()
    {
        return $this->is_inventory === self::IS_INVENTORY_N;
    }

    public function setIsInventoryToN()
    {
        $this->is_inventory = self::IS_INVENTORY_N;
    }

    /**
     * @return string
     */
    public function displayIsPurchase()
    {
        return self::optsIsPurchase()[$this->is_purchase];
    }

    /**
     * @return bool
     */
    public function isIsPurchaseY()
    {
        return $this->is_purchase === self::IS_PURCHASE_Y;
    }

    public function setIsPurchaseToY()
    {
        $this->is_purchase = self::IS_PURCHASE_Y;
    }

    /**
     * @return bool
     */
    public function isIsPurchaseN()
    {
        return $this->is_purchase === self::IS_PURCHASE_N;
    }

    public function setIsPurchaseToN()
    {
        $this->is_purchase = self::IS_PURCHASE_N;
    }

    /**
     * @return string
     */
    public function displayIsSales()
    {
        return self::optsIsSales()[$this->is_sales];
    }

    /**
     * @return bool
     */
    public function isIsSalesY()
    {
        return $this->is_sales === self::IS_SALES_Y;
    }

    public function setIsSalesToY()
    {
        $this->is_sales = self::IS_SALES_Y;
    }

    /**
     * @return bool
     */
    public function isIsSalesN()
    {
        return $this->is_sales === self::IS_SALES_N;
    }

    public function setIsSalesToN()
    {
        $this->is_sales = self::IS_SALES_N;
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
