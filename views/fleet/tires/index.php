<?php
/** 
 * Vista CRUD usando CrudWidget — Gestión de Llantas (Flotilla)
 * VERSIÓN: 1.0 - Modal manual con tabs: General, Técnicos, Operación, Asignación, Notas
 * JavaScript: web/scripts/fleet/tires/fleet.tires.js
 */

use app\components\widgets\crud\CrudWidget;
use app\assets\DynamicAssetBundle;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\tables\Tire;

$this->title = 'Gestión de Llantas';

// Registrar DynamicAssetBundle para auto-cargar JS
DynamicAssetBundle::register($this);

// ✅ KPIs: Calcular datos con consultas directas
$kpiTotalTires = Tire::find()->count();
$kpiAvailable = Tire::find()->where(['operational_status' => Tire::OP_STATUS_AV])->count();
$kpiInUse = Tire::find()->where(['operational_status' => Tire::OP_STATUS_US])->count();
$kpiMaintenance = Tire::find()->where(['operational_status' => Tire::OP_STATUS_MT])->count();

?>
<div class="fleet-tires-index container-fluid">

    <!-- KPIs Section -->
    <div class="row">
        <div class="col-md-3 col-sd-6">
            <div class="card bg-primary text-white kpi-card">
                <div class="card-body p-1">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-auto">
                            <i class="ph ph-tire fa-2x"></i>
                        </div>
                        <div class="col">
                            <h2 class="text-white f-w-300" id="kpi-total-tires"><?= $kpiTotalTires ?></h2>
                            <h5 class="text-white">Total llantas</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sd-6">
            <div class="card bg-success text-white kpi-card">
                <div class="card-body p-1">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-auto">
                            <i class="fa-solid fa-circle-check fa-2x"></i>
                        </div>
                        <div class="col">
                            <h2 class="text-white f-w-300" id="kpi-available"><?= $kpiAvailable ?></h2>
                            <h5 class="text-white">Disponibles</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sd-6">
            <div class="card bg-info text-white kpi-card">
                <div class="card-body p-1">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-auto">
                            <i class="fa-solid fa-truck fa-2x"></i>
                        </div>
                        <div class="col">
                            <h2 class="text-white f-w-300" id="kpi-in-use"><?= $kpiInUse ?></h2>
                            <h5 class="text-white">En uso</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sd-6">
            <div class="card bg-warning text-white kpi-card">
                <div class="card-body p-1">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-auto">
                            <i class="fa-solid fa-screwdriver-wrench fa-2x"></i>
                        </div>
                        <div class="col">
                            <h2 class="text-white f-w-300" id="kpi-maintenance"><?= $kpiMaintenance ?></h2>
                            <h5 class="text-white">En mantenimiento</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CrudWidget Section -->
    <div class="col-12 row">
        <div class="card col-12">
            <div class="card-body p-1">
                <div class="row">
                    <div class="col-6">
                        <h4 class="card-title">Inventario de Llantas</h4>
                        <h6>Gestión completa del inventario de llantas con seguimiento de desgaste.</h6>
                    </div>
                    <div class="col-6 text-end">
                        <!-- Botón Agregar Manual -->
                        <button type="button" class="btn btn-success m-2" id="btn-add-tire" data-bs-toggle="modal"
                            data-bs-target="#tire-modal">
                            <i class="ti ti-circle-plus me-1"></i>Agregar Llanta
                        </button>
                    </div>
                </div>
                <div class="card-text">

                    <?php
                    // DataTable usando CrudWidget (solo tabla, sin form)
                    echo CrudWidget::widget([
                        'title' => null,
                        'description' => null,
                        
                        'endpoints' => [
                            'list'   => ['tire/list'],
                            'save'   => ['tire/save'],
                            'delete' => ['tire/delete'],
                        ],
                        
                        'table' => [
                            'id' => 'tbl-tires',
                            'varName' => 'tbl_tires',
                            'pkField' => 'tire_code',
                            'paging' => true,
                            'pageLength' => 10,
                            'autoWidth' => false,
                            'noFilter' => [8, 9],
                            'columns' => [
                                ['data' => 'tire_code', 'title' => 'Código', 'className' => 'text-justify', 'width' => '110px'],
                                ['data' => 'tire_name', 'title' => 'Nombre', 'className' => 'text-justify', 'width' => '200px'],
                                ['data' => 'brand_name', 'title' => 'Marca', 'className' => 'text-justify', 'width' => '120px'],
                                ['data' => 'model_name', 'title' => 'Modelo', 'className' => 'text-justify', 'width' => '120px'],
                                ['data' => 'size_name', 'title' => 'Medida', 'className' => 'text-center', 'width' => '100px'],
                                ['data' => 'serial_no', 'title' => 'Serie', 'className' => 'text-justify', 'width' => '130px'],
                                ['data' => 'operational_status_name', 'title' => 'Estado Oper.', 'className' => 'text-center', 'width' => '120px', 
                                  'render' => 'function(data) { var badges = {"Disponible": "success", "En Uso": "info", "Mantenimiento": "warning", "Desechada": "danger"}; var color = badges[data] || "secondary"; return "<span class=\"badge bg-" + color + "\">" + (data || "-") + "</span>"; }'],
                                ['data' => 'location_status_name', 'title' => 'Ubicación', 'className' => 'text-center', 'width' => '100px',
                                  'render' => 'function(data) { return data || "-"; }'],
                                ['data' => 'curr_tread_depth', 'title' => 'Prof. Actual', 'className' => 'text-end', 'width' => '90px',
                                  'render' => 'function(data) { return data ? parseFloat(data).toFixed(2) + " mm" : "-"; }'],
                                ['data' => 'assigned_unit_code', 'title' => 'Unidad Asig.', 'className' => 'text-center', 'width' => '110px',
                                  'render' => 'function(data) { return data || "<span class=\"text-muted\">-</span>"; }'],
                            ],
                            'includeActiveColumn' => false,
                            'actions' => ['edit', 'delete'],
                            'editButtonColor' => 'success',
                            'deleteButtonColor' => 'danger',
                            'exportButtons' => ['copy', 'excel', 'csv'],
                        ],
                        
                        'form' => false, // Desactivar modal auto-generado
                        'addButton' => false, // Ya lo creamos arriba
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Manual con Tabs -->
<div class="modal fade" id="tire-modal" tabindex="-1" aria-labelledby="tire-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-2">
                <h6 class="modal-title mb-0" id="tire-modal-label">
                    <i class="ph ph-tire me-2"></i>Datos de Llanta
                </h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="tire-form" class="needs-validation" novalidate>
                <div class="modal-body p-2" style="min-height: 600px;">
                    <input type="hidden" id="tire_tire_code_hidden" name="tire[tire_code]" value="">
                    
                    <!-- Encabezado: Datos Maestros -->
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="row g-2">
                                <div class="col-md-4 col-sd-12">
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text col-5"><b>Código Llanta</b> <span class="text-danger">*</span></span>
                                        <input type="text" class="form-control" placeholder="Código" name="tire[tire_code]" id="tire[tire_code]" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text col-5"><b>Nombre</b> <span class="text-danger">*</span></span>
                                        <input type="text" class="form-control" placeholder="Nombre" name="tire[tire_name]" id="tire[tire_name]" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sd-12">
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text col-5"><b>Estado Operacional</b></span>
                                        <select class="form-control" name="tire[operational_status]" id="tire[operational_status]">
                                            <option value="<?= Tire::OP_STATUS_AV ?>">Disponible</option>
                                            <option value="<?= Tire::OP_STATUS_US ?>">En Uso</option>
                                            <option value="<?= Tire::OP_STATUS_MT ?>">Mantenimiento</option>
                                            <option value="<?= Tire::OP_STATUS_DS ?>">Desechada</option>
                                        </select>
                                    </div>
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text col-5"><b>Ubicación</b></span>
                                        <select class="form-control" name="tire[location_status]" id="tire[location_status]">
                                            <option value="<?= Tire::LOC_WH ?>">Almacén</option>
                                            <option value="<?= Tire::LOC_VH ?>">En Vehículo</option>
                                            <option value="<?= Tire::LOC_WS ?>">Taller</option>
                                            <option value="<?= Tire::LOC_SP ?>">En Reencauche</option>
                                            <option value="<?= Tire::LOC_SC ?>">Desecho</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sd-12">
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text col-5"><b>Condición Física</b></span>
                                        <select class="form-control" name="tire[physical_condition]" id="tire[physical_condition]">
                                            <option value="<?= Tire::COND_NW ?>">Nueva</option>
                                            <option value="<?= Tire::COND_RT ?>">Reencauchada</option>
                                            <option value="<?= Tire::COND_GD ?>">Buena</option>
                                            <option value="<?= Tire::COND_LW ?>">Desgaste Bajo</option>
                                            <option value="<?= Tire::COND_IW ?>">Desgaste Irregular</option>
                                            <option value="<?= Tire::COND_SD ?>">Dañada</option>
                                            <option value="<?= Tire::COND_PU ?>">Pinchada</option>
                                            <option value="<?= Tire::COND_UN ?>">Sin Inspección</option>
                                        </select>
                                    </div>
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text col-5"><b>Km Actuales</b></span>
                                        <input type="number" step="0.01" class="form-control text-end" placeholder="0.00" name="tire[current_km]" id="tire[current_km]">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs nav-tabs-sm mb-2 bg-gray-300" id="tire-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 px-3 text-blue-800" id="tab-general" data-bs-toggle="tab"
                                data-bs-target="#pill-general" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-file-lines"></i> Info. General
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-technical" data-bs-toggle="tab"
                                data-bs-target="#pill-technical" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-gears"></i> Datos Técnicos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-operation" data-bs-toggle="tab"
                                data-bs-target="#pill-operation" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-gauge-high"></i> Operación
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-assignment" data-bs-toggle="tab"
                                data-bs-target="#pill-assignment" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-truck"></i> Asignación
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-notes" data-bs-toggle="tab"
                                data-bs-target="#pill-notes" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-note-sticky"></i> Notas
                            </button>
                        </li>
                    </ul>
  
                    <!-- Tab Content -->
                    <div class="tab-content border border-top-0 p-2" style="min-height: 400px;">
                        <!-- Tab: Información General -->
                        <div class="tab-pane fade show active" id="pill-general" role="tabpanel">
                            <div class="row g-2">
                                <?php include('_tab_general.php'); ?>
                            </div>
                        </div>
                        
                        <!-- Tab: Datos Técnicos -->
                        <div class="tab-pane fade" id="pill-technical" role="tabpanel">
                            <div class="row g-2">
                                <?php include('_tab_technical.php'); ?>
                            </div>
                        </div>

                        <!-- Tab: Operación -->
                        <div class="tab-pane fade" id="pill-operation" role="tabpanel">
                            <div class="row g-2">
                                <?php include('_tab_operation.php'); ?>
                            </div>
                        </div>
                        
                        <!-- Tab: Asignación -->
                        <div class="tab-pane fade" id="pill-assignment" role="tabpanel">
                            <div class="row g-2">
                                <?php include('_tab_assignment.php'); ?>
                            </div>
                        </div>
                        
                        <!-- Tab: Notas -->
                        <div class="tab-pane fade" id="pill-notes" role="tabpanel">
                            <div class="row g-2">
                                <?php include('_tab_notes.php'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" method="POST" id="btn-save-tire" class="btn btn-sm btn-success">
                        <i class="ti ti-device-floppy me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.kpi-card {
    transition: transform 0.2s;
}
.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
