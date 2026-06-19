<?php

namespace app\models\tables;
    use app\components\behaviors\AuditBehavior;
use Yii;

/**
 * This is the model class for table "item_warehouse".
 *
 * @property string $itemcode FK artículo
 * @property string $warehouse_code FK almacén
 * @property float $onhand Existencia actual
 * @property float $committed Comprometido en ventas
 * @property float $ordered Pedido a proveedor
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null $updateuser
 *
 * @property Items $itemcode0
 * @property Warehouse $warehouseCode
 */
class ItemWarehouse extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%item_warehouse}}';
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
            [['createdate', 'createtime', 'createuser', 'updatedate', 'updatetime', 'updateuser'], 'default', 'value' => null],
            [['ordered'], 'default', 'value' => 0.000000],
            [['itemcode', 'warehouse_code'], 'required'],
            [['onhand', 'committed', 'ordered'], 'number'],
            [['createdate', 'createtime', 'updatedate', 'updatetime'], 'safe'],
            [['createuser', 'updateuser'], 'integer'],
            [['itemcode'], 'string', 'max' => 100],
            [['warehouse_code'], 'string', 'max' => 50],
            [['itemcode', 'warehouse_code'], 'unique', 'targetAttribute' => ['itemcode', 'warehouse_code']],
            [['itemcode'], 'exist', 'skipOnError' => true, 'targetClass' => Items::class, 'targetAttribute' => ['itemcode' => 'itemcode']],
            [['warehouse_code'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['warehouse_code' => 'code']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'itemcode' => 'FK artículo',
            'warehouse_code' => 'FK almacén',
            'onhand' => 'Existencia actual',
            'committed' => 'Comprometido en ventas',
            'ordered' => 'Pedido a proveedor',
            'createdate' => 'Createdate',
            'createtime' => 'Createtime',
            'createuser' => 'Createuser',
            'updatedate' => 'Updatedate',
            'updatetime' => 'Updatetime',
            'updateuser' => 'Updateuser',
        ];
    }

    /**
     * Gets query for [[Itemcode0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemcode0()
    {
        return $this->hasOne(Items::class, ['itemcode' => 'itemcode']);
    }

    /**
     * Gets query for [[WarehouseCode]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseCode()
    {
        return $this->hasOne(Warehouse::class, ['code' => 'warehouse_code']);
    }

}
