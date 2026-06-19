<?php

use app\assets\DynamicAssetBundle;
use app\components\widgets\crud\CrudWidget;
use app\models\tables\Bp;

DynamicAssetBundle::register($this);

$this->title = 'Gestión de Socios de Negocio';

$kpiTotal = Bp::find()->count();
$kpiCustomers = Bp::find()->where(['cardtype' => 'C'])->count();
$kpiSuppliers = Bp::find()->where(['cardtype' => 'S'])->count();
$kpiLeads = Bp::find()->where(['cardtype' => 'L'])->count();

?>
<div class="sn-index container-fluid">
    <div class="row mb-2">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body p-2"><h2 class="mb-0" id="kpi-sn-total"><?= $kpiTotal ?></h2><small>Total SN</small></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body p-2"><h2 class="mb-0" id="kpi-sn-c"><?= $kpiCustomers ?></h2><small>Clientes</small></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white"><div class="card-body p-2"><h2 class="mb-0" id="kpi-sn-s"><?= $kpiSuppliers ?></h2><small>Proveedores</small></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body p-2"><h2 class="mb-0" id="kpi-sn-l"><?= $kpiLeads ?></h2><small>Leads</small></div></div></div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-2">
                <button id="btn-add-sn" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#sn-modal"><i class="ti ti-circle-plus"></i> Agregar Socio</button>
            </div>
            <?= CrudWidget::widget([
                'title' => null,
                'description' => null,
                'endpoints' => ['list' => ['sn/list'], 'save' => ['sn/save'], 'delete' => ['sn/delete']],
                'table' => [
                    'id' => 'tbl-sn',
                    'varName' => 'tbl_sn',
                    'pkField' => 'cardcode',
                    'columns' => [
                        ['data' => 'cardcode', 'title' => 'Código', 'width' => '120px'],
                        ['data' => 'cardname', 'title' => 'Nombre'],
                        ['data' => 'cardtype', 'title' => 'Tipo', 'width' => '90px', 'className' => 'text-center'],
                        ['data' => 'currency_name', 'title' => 'Moneda', 'width' => '130px'],
                        ['data' => 'email', 'title' => 'Correo'],
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

<div class="modal fade" id="sn-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-2">
                <h6 class="modal-title" id="sn-modal-label"><i class="fa-solid fa-address-book me-2"></i>Socio de Negocio</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sn-form">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">Código</label><input class="form-control form-control-sm" name="cardcode" id="sn_cardcode" required></div>
                        <div class="col-md-8"><label class="form-label">Nombre</label><input class="form-control form-control-sm" name="cardname" id="sn_cardname" required></div>
                        <div class="col-md-3"><label class="form-label">Tipo</label><select class="form-control form-control-sm" name="cardtype" id="sn_cardtype"><option value="C">Cliente</option><option value="S">Proveedor</option><option value="L">Lead</option></select></div>
                        <div class="col-md-3"><label class="form-label">Grupo</label><select class="form-control form-control-sm" name="card_group" id="sn_card_group"></select></div>
                        <div class="col-md-3"><label class="form-label">Moneda</label><select class="form-control form-control-sm" name="currency" id="sn_currency"></select></div>
                        <div class="col-md-3"><label class="form-label">Vendedor</label><select class="form-control form-control-sm" name="vendor_code" id="sn_vendor"></select></div>
                    </div>

                    <ul class="nav nav-tabs mt-3" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sn-tab-contact">Contacto</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sn-tab-finance">Finanzas</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sn-tab-notes">Comentarios</button></li>
                    </ul>
                    <div class="tab-content border border-top-0 p-2">
                        <div class="tab-pane fade show active" id="sn-tab-contact">
                            <div class="row g-2">
                                <div class="col-md-4"><label class="form-label">Teléfono</label><input class="form-control form-control-sm" name="tel" id="sn_tel"></div>
                                <div class="col-md-8"><label class="form-label">Email</label><input class="form-control form-control-sm" name="email" id="sn_email"></div>
                                <div class="col-md-6"><label class="form-label">RFC / Licencia Fiscal</label><input class="form-control form-control-sm" name="lictradnum" id="sn_lictradnum"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="sn-tab-finance">
                            <div class="row g-2">
                                <div class="col-md-4"><label class="form-label">Condición de pago</label><select class="form-control form-control-sm" name="payment_cond" id="sn_payment_cond"></select></div>
                                <div class="col-md-4"><label class="form-label">Método de pago</label><select class="form-control form-control-sm" name="payment_method" id="sn_payment_method"></select></div>
                                <div class="col-md-4"><label class="form-label">Uso CFDI</label><select class="form-control form-control-sm" name="cfdi_use_code" id="sn_cfdi_use"></select></div>
                                <div class="col-md-4"><label class="form-label">Régimen CFDI</label><select class="form-control form-control-sm" name="cfdi_regimen_code" id="sn_cfdi_regimen"></select></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="sn-tab-notes">
                            <div class="row g-2">
                                <div class="col-md-6"><label class="form-label">Activo</label><select class="form-control form-control-sm" name="active" id="sn_active"><option value="Y">Sí</option><option value="N">No</option></select></div>
                                <div class="col-md-12"><label class="form-label">Comentarios</label><textarea class="form-control" name="comments" id="sn_comments" rows="4"></textarea></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btn-save-sn">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.snConfig = {
    list: '<?= \yii\helpers\Url::to(['sn/list']) ?>',
    get: '<?= \yii\helpers\Url::to(['sn/get']) ?>',
    save: '<?= \yii\helpers\Url::to(['sn/save']) ?>',
    getFormOptions: '<?= \yii\helpers\Url::to(['sn/get-form-options']) ?>'
};
</script>
