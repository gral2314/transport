<?php
/** 
 * Vista CRUD usando CrudWidget — Tipos de Vehículo con configuración de ejes
 * VERSIÓN: 3.0 - Modal manual con tabla de ejes integrada
 * JavaScript: web/scripts/config/vehicule/vehicle-type-axles-manager.js
 */

use app\components\widgets\crud\CrudWidget;
use app\assets\DynamicAssetBundle;
use yii\helpers\Url;
use yii\helpers\Html;

// Registrar DynamicAssetBundle para auto-cargar JS
DynamicAssetBundle::register($this);

// CrudWidget solo para la tabla (sin modal)
?>
<div class="col-12 row">
    <div class="card col-12">
        <div class="card-body p-1">
            <h4 class="card-title">Tipos de Vehículo (con Ejes)</h4>
            <h6>Catálogo de tipos de vehículo con configuración de ejes integrada.</h6>
            <div class="card-text">
                <!-- Botón Agregar Manual -->
                <button type="button" class="btn btn-success m-2" id="btn-add-vehicle-type" data-bs-toggle="modal" data-bs-target="#mdl-vehicle-type">
                    <i class="ti ti-circle-plus me-1"></i>Agregar
                </button>
                
                <?php
                // DataTable usando CrudWidget (solo tabla, sin form)
                echo CrudWidget::widget([
                    'title' => null, // Ya lo pusimos arriba
                    'description' => null,
                    
                    'endpoints' => [
                        'list'   => ['vehicle-type/list'],
                        'save'   => ['vehicle-type/save'], // Requerido por CrudWidget
                        'delete' => ['vehicle-type/delete'],
                    ],
                    
                    'table' => [
                        'id' => 'tbl-vehicle-type',
                        'varName' => 'tbl_vehicle_type',
                        'pkField' => 'code',
                        'paging' => true,
                        'pageLength' => 10,
                        'columns' => [
                            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
                            ['data' => 'name', 'title' => 'Nombre', 'className' => 'text-justify'],
                        ],
                        'includeActiveColumn' => true,
                        'actions' => ['edit', 'delete'],
                        'editButtonColor' => 'success',
                        'deleteButtonColor' => 'danger',
                        'editModalId' => 'mdl-vehicle-type',
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

<!-- Modal Manual con Tabla de Ejes Pre-renderizada -->
<div class="modal fade" id="mdl-vehicle-type" tabindex="-1" aria-labelledby="mdl-vehicle-type-label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdl-vehicle-type-label">
                    <i class="ti ti-truck me-2"></i>Tipo de Vehículo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="frm-vehicle-type">
                <div class="modal-body">
                    <div class="row">
                        <!-- Campo: Código -->
                        <div class="col-md-6 mb-3">
                            <label for="input-code" class="form-label">
                                <i class="ti ti-qrcode me-1"></i>Código <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="input-code" name="code" required maxlength="50">
                        </div>
                        
                        <!-- Campo: Nombre -->
                        <div class="col-md-6 mb-3">
                            <label for="input-name" class="form-label">
                                <i class="ti ti-tag me-1"></i>Nombre del Tipo de Vehículo <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="input-name" name="name" required maxlength="100">
                        </div>
                        
                        <!-- Campo: Activo (Switch) -->
                        <div class="col-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="input-active" name="active" checked>
                                <label class="form-check-label" for="input-active">Activo</label>
                            </div>
                        </div>
                        
                        <!-- Separador -->
                        <div class="col-12">
                            <hr>
                        </div>
                        
                        <!-- Tabla de Ejes (Pre-renderizada en HTML) -->
                        <div class="col-9 mt-3">
                            <h6 class="mb-3">
                                <i class="ti ti-axle me-2"></i>Configuración de Ejes
                                <small class="text-muted">(Mínimo 1 eje requerido)</small>
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="tbl-axles">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 80px;" class="text-center">#</th>
                                            <th>Tipo de Eje</th>
                                            <th style="width: 120px;" class="text-center">Cant. Llantas</th>
                                            <th style="width: 100px;" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="axles-tbody">
                                        <tr id="axles-empty-row">
                                            <td colspan="4" class="text-center text-muted">
                                                <i class="ti ti-info-circle"></i> No hay ejes configurados. Haga clic en "Agregar Eje" para comenzar.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="btn-add-axle">
                                <i class="ti ti-plus"></i> Agregar Eje
                            </button>
                        </div>
                        
                        <!-- Vista Previa de Composición de Ejes -->
                        <div class="col-3 mt-3">
                            <h6 class="mb-3">
                                <i class="ti ti-eye me-2"></i>Vista Previa de Composición de Ejes
                            </h6>
                            <div id="axle-composition-preview" class="border rounded p-3 bg-light text-center" style="min-height: 200px;">
                                <p class="text-muted"><i class="ti ti-info-circle"></i> Sin ejes configurados</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-save-vehicle-type">
                        <i class="ti ti-device-floppy me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Configurar URLs para JavaScript
$baseUrl = \yii\helpers\Url::base(true);
$saveUrl = \yii\helpers\Url::to(['vehicle-type/save']);

$js = <<<JS
// Configurar VehicleTypeAxlesManager
if (typeof VehicleTypeAxlesManager !== 'undefined') {
    VehicleTypeAxlesManager.configure({ 
        baseUrl: '{$baseUrl}',
        saveUrl: '{$saveUrl}',
        tableVar: 'tbl_vehicle_type'
    });
}
JS;

$this->registerJs($js, \yii\web\View::POS_READY);

