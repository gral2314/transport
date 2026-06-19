<?php

use app\assets\DynamicAssetBundle;
use app\components\widgets\crud\CrudWidget;
use app\models\tables\Items;

DynamicAssetBundle::register($this);

$this->title = 'Gestión de Artículos';

$kpiTotal = Items::find()->count();
$kpiInventory = Items::find()->where(['is_inventory' => 'Y'])->count();
$kpiSales = Items::find()->where(['is_sales' => 'Y'])->count();
$kpiPurchase = Items::find()->where(['is_purchase' => 'Y'])->count();

?>
<div class="items-index container-fluid">
    <div class="row mb-2">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body p-2"><h2 class="mb-0" id="kpi-items-total"><?= $kpiTotal ?></h2><small>Total ítems</small></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body p-2"><h2 class="mb-0" id="kpi-items-inventory"><?= $kpiInventory ?></h2><small>Inventario</small></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white"><div class="card-body p-2"><h2 class="mb-0" id="kpi-items-sales"><?= $kpiSales ?></h2><small>Venta</small></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body p-2"><h2 class="mb-0" id="kpi-items-purchase"><?= $kpiPurchase ?></h2><small>Compra</small></div></div></div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-2">
                <button id="btn-add-item" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#item-modal"><i class="ti ti-circle-plus"></i> Agregar Ítem</button>
            </div>
            <?= CrudWidget::widget([
                'title' => null,
                'description' => null,
                'endpoints' => ['list' => ['items/list'], 'save' => ['items/save'], 'delete' => ['items/delete']],
                'table' => [
                    'id' => 'tbl-items',
                    'varName' => 'tbl_items',
                    'pkField' => 'itemcode',
                    'columns' => [
                        ['data' => 'itemcode', 'title' => 'Código', 'width' => '150px'],
                        ['data' => 'itemname', 'title' => 'Descripción'],
                        ['data' => 'group_name', 'title' => 'Grupo', 'width' => '170px'],
                        ['data' => 'is_inventory', 'title' => 'Inv', 'className' => 'text-center', 'width' => '70px'],
                        ['data' => 'is_sales', 'title' => 'Vta', 'className' => 'text-center', 'width' => '70px'],
                        ['data' => 'is_purchase', 'title' => 'Cpa', 'className' => 'text-center', 'width' => '70px'],
                    ],
                    'includeActiveColumn' => false,
                    'actions' => ['edit', 'delete'],
                ],
                'form' => false,
                'addButton' => false,
            ]) ?>
        </div>
    </div>
</div>

<div class="modal fade" id="item-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-2">
                <h6 class="modal-title" id="item-modal-label"><i class="fa-solid fa-boxes-stacked me-2"></i>Artículo</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="item-form">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">Código</label><input class="form-control form-control-sm" name="itemcode" id="item_itemcode" required></div>
                        <div class="col-md-8"><label class="form-label">Descripción</label><input class="form-control form-control-sm" name="itemname" id="item_itemname" required></div>
                        <div class="col-md-4"><label class="form-label">Grupo</label><select class="form-control form-control-sm" name="item_group" id="item_group"></select></div>
                        <div class="col-md-4"><label class="form-label">Plantilla de llanta</label><select class="form-control form-control-sm" name="tire_code" id="item_tire_code"></select></div>
                        <div class="col-md-4"><label class="form-label">Activo</label><select class="form-control form-control-sm" name="active" id="item_active"><option value="Y">Sí</option><option value="N">No</option></select></div>
                    </div>

                    <ul class="nav nav-tabs mt-3" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#item-tab-uom">Unidades</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#item-tab-flags">Banderas</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#item-tab-notes">Notas</button></li>
                    </ul>
                    <div class="tab-content border border-top-0 p-2">
                        <div class="tab-pane fade show active" id="item-tab-uom">
                            <div class="row g-2">
                                <div class="col-md-4"><label class="form-label">UM Compra</label><input class="form-control form-control-sm" name="uom_purchase" id="item_uom_purchase" required></div>
                                <div class="col-md-4"><label class="form-label">UM Venta</label><input class="form-control form-control-sm" name="uom_sales" id="item_uom_sales" required></div>
                                <div class="col-md-4"><label class="form-label">UM Inventario</label><input class="form-control form-control-sm" name="uom_inventory" id="item_uom_inventory" required></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="item-tab-flags">
                            <div class="row g-2">
                                <div class="col-md-4"><label class="form-label">Es Inventario</label><select class="form-control form-control-sm" name="is_inventory" id="item_is_inventory"><option value="Y">Sí</option><option value="N">No</option></select></div>
                                <div class="col-md-4"><label class="form-label">Es Compra</label><select class="form-control form-control-sm" name="is_purchase" id="item_is_purchase"><option value="Y">Sí</option><option value="N">No</option></select></div>
                                <div class="col-md-4"><label class="form-label">Es Venta</label><select class="form-control form-control-sm" name="is_sales" id="item_is_sales"><option value="Y">Sí</option><option value="N">No</option></select></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="item-tab-notes">
                            <textarea class="form-control" rows="5" name="notes" id="item_notes"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btn-save-item">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.itemsConfig = {
    list: '<?= \yii\helpers\Url::to(['items/list']) ?>',
    get: '<?= \yii\helpers\Url::to(['items/get']) ?>',
    save: '<?= \yii\helpers\Url::to(['items/save']) ?>',
    getFormOptions: '<?= \yii\helpers\Url::to(['items/get-form-options']) ?>'
};
</script>
