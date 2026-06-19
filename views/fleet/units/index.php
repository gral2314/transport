<?php
/** 
 * Vista CRUD usando CrudWidget — Gestión de Unidades (Flotilla)
 * VERSIÓN: 1.0 - Modal manual con tabs: General, Documentos, Llantas
 * JavaScript: web/scripts/fleet/units/fleet.units.js
 */

use app\components\widgets\crud\CrudWidget;
use app\assets\DynamicAssetBundle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use app\components\widgets\TextInputWidget;
use app\components\widgets\SelectWidget;
use app\models\tables\Vehicle;
use app\models\tables\VehicleDocument;


$this->title = 'Gestión de Unidades';

// Registrar DynamicAssetBundle para auto-cargar JS
DynamicAssetBundle::register($this);

// ✅ KPIs: Calcular datos con consultas directas
$kpiTotalUnits = Vehicle::find()->count();
$kpiAvailable = Vehicle::find()->where(['available' => Vehicle::AVAILABLE_Y, 'status' => Vehicle::STATUS_A])->count();
$kpiMaintenance = Vehicle::find()->where(['status' => Vehicle::STATUS_M])->count();

// Documentos por vencer en los próximos 30 días
$today = date('Y-m-d');
$next30Days = date('Y-m-d', strtotime('+30 days'));
$kpiExpiringDocs = VehicleDocument::find()
    ->where(['between', 'exp_date', $today, $next30Days])
    ->andWhere(['>', 'exp_date', $today])  // Excluir vencidos
    ->count();

?>
<div class="fleet-units-index container-fluid">

    <!-- KPIs Section -->
    <div class="row">
    <div class="col-md-3 col-sd-6">
        <div class="card bg-primary text-white kpi-card">
            <div class="card-body p-1">
                <div class="row align-items-center justify-content-center">
                    <div class="col-auto">
                        <i class="fa-solid fa-truck fa-2x"></i>
                    </div>
                    <div class="col">
                        <h2 class="text-white f-w-300" id="kpi-total-units"><?= $kpiTotalUnits ?></h2>
                        <h5 class="text-white">Total unidades</h5>
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
                        <h5 class="text-white">Unidades disponibles</h5>
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

    <div class="col-md-3 col-sd-6">
        <div class="card bg-info text-white kpi-card">
            <div class="card-body p-1">
                <div class="row align-items-center justify-content-center">
                    <div class="col-auto">
                        <i class="fa-solid fa-file-circle-exclamation fa-2x"></i>
                    </div>
                    <div class="col">
                        <h2 class="text-white f-w-300" id="kpi-expiring"><?= $kpiExpiringDocs ?></h2>
                        <h5 class="text-white">Docs por vencer (30d)</h5>
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
                        <h4 class="card-title">Unidades de Flotilla</h4>
                        <h6>Gestión completa de unidades vehiculares con documentos y llantas.</h6>
                    </div>
                    <div class="col-6 text-end">
                        <!-- Botón Agregar Manual -->
                        <button type="button" class="btn btn-success m-2" id="btn-add-unit" data-bs-toggle="modal"
                            data-bs-target="#unit-modal">
                            <i class="ti ti-circle-plus me-1"></i>Agregar Unidad
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
              'list'   => ['vehicle/list'],
              'save'   => ['vehicle/unit-save'],
              'delete' => ['vehicle/delete'],
            ],
            
            'table' => [
              'id' => 'tbl-units',
              'varName' => 'tbl_units',
              'pkField' => 'vehicle_code',
              'paging' => true,
              'pageLength' => 10,
              'autoWidth' => false,
              'noFilter' =>[5,6],
              'columns' => [
                ['data' => 'vehicle_code', 'title' => 'Unidad', 'className' => 'text-justify', 'width' => '110px'],
                ['data' => 'vehicle_name', 'title' => 'Descripción', 'className' => 'text-justify', 'width' => '250px'],
                ['data' => 'vehicle_type_name', 'title' => 'Tipo de Unidad', 'className' => 'text-wrap text-wrap-custom', 'width' => '380px'],
                ['data' => 'service_type_name', 'title' => 'Tipo de Servicio', 'className' => 'text-justify', 'width' => '200px'],
                ['data' => 'cargo_type_name', 'title' => 'Tipo de Carga', 'className' => 'text-justify', 'width' => '190px'],
                ['data' => 'status_name', 'title' => 'Estado', 'className' => 'text-center', 'width' => '90px', 
                  'render' => 'function(data) { var badges = {"Activo": "success", "Inactivo": "secondary", "Mantenimiento": "warning", "Baja": "danger"}; var color = badges[data] || "secondary"; return "<span class=\"badge bg-" + color + "\">" + (data || "-") + "</span>"; }'],
                ['data' => 'available', 'title' => 'Disponible', 'className' => 'text-center', 'width' => '90px',
                  'render' => 'function(data) { return data === "Y" ? "<span class=\"badge bg-success\">Sí</span>" : "<span class=\"badge bg-secondary\">No</span>"; }'],
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

<!-- Modal Manual con Tabs: Estructura según imagen -->
<div class="modal fade" id="unit-modal" tabindex="-1" aria-labelledby="unit-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-2">
                <h6 class="modal-title mb-0" id="unit-modal-label">
                    <i class="ti ti-truck me-2"></i>Datos Maestros de Unidades
                </h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="unit-form" class="needs-validation" novalidate>
                <div class="modal-body p-2" style="min-height: 700px;">
                    <input type="hidden" id="vehicle_vehicle_code" name="vehicle[vehicle_code]" value="">
                    <!-- Encabezado: Datos Maestros -->
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="row g-2">
                                <div class="col-md-4 col-sd-12">
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text col-5"><b>Código Unidad</b> <span class="text-danger">*</span></span>
                                        <input type="text" class="form-control" placeholder="Código" name="vehicle[vehicle_code]" id="vehicle[vehicle_code]" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text col-5"><b>Nombre</b> <span class="text-danger">*</span></span>
                                        <input type="text" class="form-control" placeholder="Nombre" name="vehicle[vehicle_name]" id="vehicle[vehicle_name]" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text col-5"><b>Tipo de Unidad</b> <span class="text-danger">*</span></span>
                                        <select class="form-control" name="vehicle[vehicle_type_code]" id="vehicle[vehicle_type_code]" required>
                                            <option value="">-- Seleccionar --</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sd-12">
                                </div>
                                <div class="col-md-4 col-sd-12">
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text col-5"><b>Disponible</b></span>
                                        <select class="form-control" name="vehicle[available]" id="vehicle[available]">
                                            <option value="Y">Sí</option>
                                            <option value="N">No</option>
                                        </select>
                                    </div>
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text col-5"><b>Km Actual</b></span>
                                        <input type="text" class="form-control" placeholder="Km Actual" name="vehicle[current_km]" id="vehicle[current_km]">
                                    </div>
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text col-5"><b>Combustible Actual (L)</b></span>
                                        <input type="text" class="form-control" placeholder="Combustible Actual" name="vehicle[current_fuel]" id="vehicle[current_fuel]">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabs Navigation (similar a la imagen) -->
                    <ul class="nav nav-tabs nav-tabs-sm mb-2 bg-gray-300 " id="unit-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 px-3 text-blue-800" id="tab-general" data-bs-toggle="tab"
                                data-bs-target="#pill-general" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-file-lines"></i> Datos Grals.
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-technical" data-bs-toggle="tab"
                                data-bs-target="#pill-technical" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-gears"></i> Datos Tec.
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-tires" data-bs-toggle="tab" data-bs-target="#pill-tires"
                                type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="ph ph-tire"></i> Llantas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-maintenance" data-bs-toggle="tab"
                                data-bs-target="#pill-maintenance" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-screwdriver-wrench"></i> Mantenimiento
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-assignments" data-bs-toggle="tab"
                                data-bs-target="#pill-assignments" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-user-check"></i> Asignaciones
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-finance" data-bs-toggle="tab"
                                data-bs-target="#pill-finance" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-wallet"></i> Finanzas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-docs" data-bs-toggle="tab"
                                data-bs-target="#pill-docs" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-file-alt"></i> Documentos
                            </button>
                        </li>
                    </ul>
  
                    <!-- Tab Content -->
                    <div class="tab-content border border-top-0 p-2" style="min-height: 350px;">
                        <!-- Tab: Datos Generales -->
                        <div class="tab-pane fade show active" id="pill-general" role="tabpanel">
                            <div class="row g-2">
                                <?php include('_tab_gral.php'); ?>
                            </div>
                        </div>
                        
                        <!-- Tab: Datos Técnicos -->
                        <div class="tab-pane fade" id="pill-technical" role="tabpanel">
                            <div class="row g-2">
                                <?php include('_tab_tecincos.php'); ?>
                            </div>
                        </div>

                        <!-- Tab: Llantas -->
                        <div class="tab-pane fade" id="pill-tires" role="tabpanel">
                            <div class="row g-2">
                                <!-- Imagen de configuración de llantas -->
                                <div class="col-3">
                                    <img id="tire-config-image" src="<?= Yii::getAlias('@web') ?>/images/placeholder-tire.svg" 
                                         alt="Seleccione tipo de unidad" 
                                         class="img-fluid" 
                                         style="height: 350px; object-fit: contain;">
                                </div>
                                <!-- Tabla de llantas -->
                                <div class="col-9">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-hover" id="tires-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 50px;" class="text-center">#</th>
                                                    <th>Llanta</th>
                                                    <th style="width: 80px;" class="text-center">No. Eje</th>
                                                    <th style="width: 100px;" class="text-center">Tipo Eje</th>
                                                    <th style="width: 100px;" class="text-center">Posición</th>
                                                    <th style="width: 120px;" class="text-center">Instalación</th>
                                                    <th style="width: 100px;" class="text-end">KM</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="no-tires-msg"><td colspan="7" class="text-center text-muted">No hay llantas instaladas</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab: Mantenimiento -->
                        <div class="tab-pane fade" id="pill-maintenance" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-md-12">
                                    'last_service_date'      => 'Último Servicio',

                                    <p class="text-muted"><i class="ti ti-info-circle"></i> Módulo de mantenimiento en desarrollo</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab: Asignaciones -->
                        <div class="tab-pane fade" id="pill-assignments" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-md-12">
                                    <div class="row g-2">
                                        <?php include('_tab_asigna.php'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Tab: Finanzas -->
                        <div class="tab-pane fade" id="pill-finance" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-md-12">
                                    <div class="row g-2">
                                        <?php include('_tab_finance.php'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab: Documentos -->
                        <div class="tab-pane fade" id="pill-docs" role="tabpanel">
                            <div class="mb-2">
                                <button type="button" id="btn-add-doc" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus me-1"></i>Agregar Documento
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover" id="docs-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">#</th>
                                            <th style="width: 200px;">Tipo Documento</th>
                                            <th style="width: 150px;">No. Documento</th>
                                            <th style="width: 120px;">Fecha Emisión</th>
                                            <th style="width: 120px;">Fecha Vencimiento</th>
                                            <th style="width: 150px;">Archivo</th>
                                            <th>Notas</th>
                                            <th style="width: 120px;" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="8" class="text-center text-muted">No hay documentos registrados</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" method="POST" id="btn-save-unit" class="btn btn-sm btn-success">
                        OK
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Configurar URLs para JavaScript
$cfg = [
  'list' => Url::to(['fleet/list']),
  'get'  => Url::to(['fleet/get']),
  'save' => Url::to(['fleet/unit-save']),
  'getFormOptions' => Url::to(['fleet/get-form-options']),
  'getAxleConfig' => Url::to(['fleet/get-axle-config']), // ✅ NUEVO: Configuración de ejes
  'saveDocument' => Url::to(['fleet/save-document']),
  'deleteDocument' => Url::to(['fleet/delete-document']),
  'saveTire' => Url::to(['fleet/save-tire-line']),
  'deleteTire' => Url::to(['fleet/delete-tire-line']),
  'vehicleTypes' => Url::to(['fleet/vehicle-types']),
  'brands' => Url::to(['fleet/brands']),
  'baseUrl' => Yii::getAlias('@web'), // ✅ NUEVO: URL base del proyecto
  'imagesPath' => Yii::getAlias('@web') . '/images', // ✅ NUEVO: Ruta a imágenes
];

$this->registerJs('window.fleetUnitsConfig = ' . Json::htmlEncode($cfg) . ';', \yii\web\View::POS_HEAD);

$this->registerCss('
.kpi-card { cursor: default; }
.kpi-card img { opacity: 0.8; }
.nav-tabs-sm .nav-link { font-size: 0.875rem; padding: 0.375rem 0.75rem; }
.form-label-sm { font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; }
.modal-header.bg-light { background-color: #f8f9fa !important; }
');