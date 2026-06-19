<?php
/** 
 * Vista CRUD usando CrudWidget — Gestión de Empleados (RRHH)
 * VERSIÓN: 1.0 - Modal manual con tabs: Datos Generales, Datos Laborales, Documentos, Roles, Notas
 * JavaScript: web/scripts/rrhh/employees/rrhh.employees.js
 */

use app\components\widgets\crud\CrudWidget;
use app\assets\DynamicAssetBundle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use app\models\tables\Employee;

$this->title = 'Gestión de Empleados';

// Registrar DynamicAssetBundle para auto-cargar JS
DynamicAssetBundle::register($this);

// ✅ KPIs: Calcular datos con consultas directas
$kpiTotal = Employee::find()->count();
$kpiActive = Employee::find()->where(['employee_status' => 'ACTIVE', 'active' => 'Y'])->count();
$kpiInactive = Employee::find()->where(['in', 'employee_status', ['INACTIVE', 'SUSPENDED']])->count();
$kpiIncompleteDocs = Employee::find()->where(['documentation_complete' => 'N'])->count();

?>
<div class="rrhh-employees-index container-fluid">

    <!-- KPIs Section -->
    <div class="row mb-3">
        <div class="col-md-3 col-sd-6">
            <div class="card bg-primary text-white kpi-card shadow-sm">
                <div class="card-body p-2">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-auto">
                            <i class="fa-solid fa-users fa-2x"></i>
                        </div>
                        <div class="col">
                            <h2 class="text-white f-w-300 mb-0" id="kpi-total-employees"><?= $kpiTotal ?></h2>
                            <h6 class="text-white mb-0">Total Empleados</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sd-6">
            <div class="card bg-success text-white kpi-card shadow-sm">
                <div class="card-body p-2">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-auto">
                            <i class="fa-solid fa-circle-check fa-2x"></i>
                        </div>
                        <div class="col">
                            <h2 class="text-white f-w-300 mb-0" id="kpi-active"><?= $kpiActive ?></h2>
                            <h6 class="text-white mb-0">Empleados Activos</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sd-6">
            <div class="card bg-warning text-white kpi-card shadow-sm">
                <div class="card-body p-2">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-auto">
                            <i class="fa-solid fa-user-slash fa-2x"></i>
                        </div>
                        <div class="col">
                            <h2 class="text-white f-w-300 mb-0" id="kpi-inactive"><?= $kpiInactive ?></h2>
                            <h6 class="text-white mb-0">Inactivos / Suspendidos</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sd-6">
            <div class="card bg-info text-white kpi-card shadow-sm">
                <div class="card-body p-2">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-auto">
                            <i class="fa-solid fa-file-circle-exclamation fa-2x"></i>
                        </div>
                        <div class="col">
                            <h2 class="text-white f-w-300 mb-0" id="kpi-incomplete"><?= $kpiIncompleteDocs ?></h2>
                            <h6 class="text-white mb-0">Docs. Incompletos</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CrudWidget Section -->
    <div class="col-12 row">
        <div class="card col-12 shadow-sm">
            <div class="card-body p-2">
                <div class="row mb-2">
                    <div class="col-6">
                        <h4 class="card-title">Listado de Empleados</h4>
                        <h6 class="card-subtitle text-muted">Administración y control de personal, puestos, roles y expedientes.</h6>
                    </div>
                    <div class="col-6 text-end">
                        <button type="button" class="btn btn-success" id="btn-add-employee" data-bs-toggle="modal" data-bs-target="#employee-modal">
                            <i class="ti ti-circle-plus me-1"></i>Agregar Empleado
                        </button>
                    </div>
                </div>
                <div class="card-text">
                    <?php
                    // DataTable usando CrudWidget (solo tabla, sin form auto-generado)
                    echo CrudWidget::widget([
                        'title' => null,
                        'description' => null,
                        'endpoints' => [
                            'list'   => ['employee/list'],
                            'save'   => ['employee/save'],
                            'delete' => ['employee/delete'],
                        ],
                        'table' => [
                            'id' => 'tbl-employees',
                            'varName' => 'tbl_employees',
                            'pkField' => 'employee_code',
                            'paging' => true,
                            'pageLength' => 10,
                            'autoWidth' => false,
                            'columns' => [
                                ['data' => 'employee_code', 'title' => 'Código', 'className' => 'text-center', 'width' => '110px'],
                                ['data' => 'first_name', 'title' => 'Nombre Completo', 'className' => 'text-justify', 'width' => '250px',
                                    'render' => 'function(data, type, row) { 
                                        var second = row.second_last_name ? " " + row.second_last_name : "";
                                        return (row.first_name || "") + " " + (row.last_name || "") + second; 
                                    }'],
                                ['data' => 'position_name', 'title' => 'Puesto', 'className' => 'text-justify', 'width' => '180px'],
                                ['data' => 'area_name', 'title' => 'Área', 'className' => 'text-justify', 'width' => '140px'],
                                ['data' => 'branch_name', 'title' => 'Sucursal', 'className' => 'text-justify', 'width' => '140px'],
                                ['data' => 'employee_status_name', 'title' => 'Estatus', 'className' => 'text-center', 'width' => '110px',
                                    'render' => 'function(data, type, row) { 
                                        var badges = {"Activo": "success", "Inactivo": "secondary", "Suspendido": "warning", "Vacaciones": "info"};
                                        var color = badges[data] || "secondary"; 
                                        return "<span class=\"badge bg-" + color + "\">" + (data || "-") + "</span>"; 
                                    }'],
                                ['data' => 'documentation_complete', 'title' => 'Expediente', 'className' => 'text-center', 'width' => '110px',
                                    'render' => 'function(data) { 
                                        return data === "Y" ? "<span class=\"badge bg-success\">Completo</span>" : "<span class=\"badge bg-warning\">Incompleto</span>"; 
                                    }'],
                            ],
                            'includeActiveColumn' => false,
                            'actions' => ['edit', 'delete'],
                            'editButtonColor' => 'success',
                            'deleteButtonColor' => 'danger',
                            'exportButtons' => ['copy', 'excel', 'csv'],
                        ],
                        'form' => false, // Desactivar modal auto-generado
                        'addButton' => false, // Botón manual arriba
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Manual con Tabs: Estructura premium idéntica a Unidades -->
<div class="modal fade" id="employee-modal" tabindex="-1" aria-labelledby="employee-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-2">
                <h6 class="modal-title mb-0" id="employee-modal-label">
                    <i class="fa-solid fa-user-tie me-2"></i>Datos Maestros del Empleado
                </h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="employee-form" class="needs-validation" novalidate>
                <div class="modal-body p-2" style="min-height: 600px;">
                    <input type="hidden" id="employee_employee_code" name="employee[employee_code]" value="">
                    
                    <!-- Encabezado del Modal: Código y Datos Básicos -->
                    <div class="card mb-2 shadow-sm">
                        <div class="card-body p-2">
                            <div class="row g-2">
                                <div class="col-md-6 col-sd-12">
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text col-5"><b>Código Empleado</b> <span class="text-danger">*</span></span>
                                        <input type="text" class="form-control" placeholder="Código de Empleado" name="employee[employee_code]" id="employee[employee_code]" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sd-12">
                                    <!-- Espacio para estatus rápido u otro indicador si es necesario -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs nav-tabs-sm mb-2 bg-gray-300" id="employee-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 px-3 text-blue-800" id="tab-general" data-bs-toggle="tab"
                                data-bs-target="#pill-general" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-address-card"></i> Datos Generales
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-laboral" data-bs-toggle="tab"
                                data-bs-target="#pill-laboral" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-briefcase"></i> Datos Laborales
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-docs" data-bs-toggle="tab"
                                data-bs-target="#pill-docs" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-file-invoice"></i> Control Documental
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-roles" data-bs-toggle="tab"
                                data-bs-target="#pill-roles" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-user-shield"></i> Roles Asignados
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 text-blue-800" id="tab-notes" data-bs-toggle="tab"
                                data-bs-target="#pill-notes" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="fa-solid fa-comment-dots"></i> Observaciones
                            </button>
                        </li>
                    </ul>
  
                    <!-- Tab Content -->
                    <div class="tab-content border border-top-0 p-2 bg-white" style="min-height: 300px;">
                        <!-- Tab: Datos Generales -->
                        <div class="tab-pane fade show active" id="pill-general" role="tabpanel">
                            <div class="row g-2">
                                <?php include('_tab_general.php'); ?>
                            </div>
                        </div>
                        
                        <!-- Tab: Datos Laborales -->
                        <div class="tab-pane fade" id="pill-laboral" role="tabpanel">
                            <div class="row g-2">
                                <?php include('_tab_laboral.php'); ?>
                            </div>
                        </div>
                        
                        <!-- Tab: Control Documental -->
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
                                            <th style="width: 250px;">Tipo de Documento</th>
                                            <th style="width: 130px;" class="text-center">Entregado</th>
                                            <th style="width: 150px;" class="text-center">Vencimiento</th>
                                            <th>Notas</th>
                                            <th style="width: 100px;" class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="no-records"><td colspan="6" class="text-center text-muted">No hay documentos registrados</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab: Roles -->
                        <div class="tab-pane fade" id="pill-roles" role="tabpanel">
                            <div class="card p-3 border-0 bg-light">
                                <h6>Selección de Roles del Empleado</h6>
                                <p class="text-muted small">Selecciona los roles funcionales asignados a este empleado dentro de la operación.</p>
                                <div class="row g-2" id="roles-container">
                                    <!-- Checkboxes se generarán dinámicamente -->
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Observaciones -->
                        <div class="tab-pane fade" id="pill-notes" role="tabpanel">
                            <div class="form-group mb-1">
                                <label for="employee[notes]"><b>Notas u Observaciones del Expediente</b></label>
                                <textarea class="form-control" name="employee[notes]" id="employee[notes]" rows="8" placeholder="Escribe observaciones o comentarios relevantes del empleado aquí..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" id="btn-save-employee" class="btn btn-sm btn-success">
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
    'list' => Url::to(['employee/list']),
    'get'  => Url::to(['employee/get']),
    'save' => Url::to(['employee/save']),
    'delete' => Url::to(['employee/delete']),
    'getFormOptions' => Url::to(['employee/get-form-options']),
    'baseUrl' => Yii::getAlias('@web'),
];

$this->registerJs('window.employeeConfig = ' . Json::htmlEncode($cfg) . ';', \yii\web\View::POS_HEAD);

$this->registerCss('
.kpi-card { cursor: default; }
.kpi-card i { opacity: 0.8; }
.nav-tabs-sm .nav-link { font-size: 0.875rem; padding: 0.375rem 0.75rem; }
.form-label-sm { font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; }
.modal-header.bg-light { background-color: #f8f9fa !important; }
');

$this->registerJsFile('@web/scripts/rrhh/employees/rrhh.employees.js', ['depends' => [\app\assets\DynamicAssetBundle::class]]);
?>
