<?php
declare(strict_types=1);
/**
* @var yii\web\View $this
* @var array $config
* @var array $document
* @var array $formOptions
* @var bool $isNewRecord
*/
use yii\helpers\Html;
?>

<!-- ===== ZONA OPERATIVA PRINCIPAL (Fullscreen Toggle) ===== -->
<div id="visual-workspace" class="visual-workspace-container mb-2">
    
    <!-- Botón Fullscreen / Restaurar -->
    <div class="d-flex justify-content-between mb-1 px-2">
        <!-- Botón Agregar (Mantiene funcionalidad modal) -->
            <button type="button" class="btn btn-outline-success btn-sm mt-1" id="add-detail-row">
                <i class="fa-solid fa-plus"></i> Nueva Llanta
            </button>
        <button type="button" id="btn-toggle-fullscreen" class="btn btn-outline-secondary btn-sm mt-1" title="Maximizar área de trabajo">
            <i class="fa-solid fa-expand"></i> Vista Completa
        </button>
    </div>

    <div class="row g-3 h-100">
        
        <!-- COL-2: LLANTAS DISPONIBLES (Objetos Visuales Arrastrables) -->
        <div class="col-lg-2 d-flex flex-column border rounded bg-light p-3 visual-panel-left">
            <h6 class="fw-semibold mb-2 text-primary">
                <i class="fa-solid fa-warehouse"></i> Disponibles
            </h6>
            
            <!-- Búsqueda compacta -->
            <div class="mb-2">
                <input type="text" class="form-control form-control-sm" 
                       id="search-available-tires" 
                       placeholder="Buscar llanta..." autocomplete="off">
            </div>

            <!-- Contenedor de Objetos Visuales (Reemplaza la tabla antigua) -->
            <div id="available-tires-container" class="flex-grow-1 overflow-auto pe-1" style="min-height: 300px;">
                <div class="text-center text-muted py-4 small">
                    <i class="fa-solid fa-box-open fa-2x mb-2 opacity-25"></i><br>
                    Sin llantas cargadas.<br>Use "+ Nueva" para agregar.
                </div>
            </div>

            
        </div>

        <!-- COL-8: ZONA DE TRABAJO (Chasis + Staging + Trash) -->
        <div class="col-lg-8 d-flex flex-column gap-3 visual-panel-center">
            
            <!-- Área del Chasis -->
            <div class="flex-grow-1 border rounded bg-white p-3 position-relative shadow-sm">
                <h6 class="fw-semibold mb-2 text-primary">
                    <i class="fa-solid fa-truck"></i> Taller Visual
                </h6>
                
                <div id="dynamic-truck-container" 
                     class="d-flex flex-wrap gap-3 justify-content-center align-items-start bg-gray-200 p-3 rounded"
                     style="min-height: 400px;">
                    <div class="text-center text-muted py-5 w-100">
                        <i class="fa-solid fa-truck-moving fa-3x mb-2 opacity-25"></i>
                        <p class="mb-0">No hay unidades seleccionadas.</p>
                        <small>Seleccione unidades en la pestaña <strong>"Unidades"</strong>.</small>
                    </div>
                </div>
            </div>

            <!-- Zona Inferior de Trabajo: Staging + Trash -->
            <div class="row g-2">
                
            </div>
        </div>

        <!-- COL-2: CUARENTENA RÁPIDA O ACCESOS (Opcional, puede usarse para filtros o resumen rápido) -->
        <!-- COL-2: CUARENTENA RÁPIDA Y BAJA -->
        <div class="col-lg-2 d-flex flex-column gap-2 visual-panel-right">

            <!-- Staging / Cuarentena -->
            <div class="border rounded bg-light p-2 flex-grow-1">
                <h6 class="fw-semibold mb-1 text-warning small">
                    <i class="fa-solid fa-box-open"></i> Cuarentena / Staging
                </h6>
                <div id="staging-zone" 
                        class="border border-warning border-dashed rounded bg-warning-subtle p-2 d-flex flex-wrap gap-2 align-items-center min-vh-staging" style="min-height: 150px;">
                    <span class="text-muted small w-100 text-center py-2" id="staging-empty-msg">
                        Las llantas retiradas aparecerán aquí. Asigne destino final.
                    </span>
                </div>
            </div>

            <!-- Trash / Baja -->
            <div class="border rounded bg-light p-2">
                <h6 class="fw-semibold mb-1 text-danger small">
                    <i class="fa-solid fa-trash-can"></i> Baja / Chatarrización
                </h6>
                <div id="trash-drop-zone" 
                        class="border border-danger border-dashed rounded bg-danger-subtle p-2 d-flex align-items-center justify-content-center text-center min-vh-staging" style="min-height: 150px;">
                    <div>
                        <i class="fa-solid fa-trash-can fa-lg text-danger mb-1"></i>
                        <span class="d-block small text-muted">Arrastre aquí para dar de <strong>BAJA</strong></span>
                    </div>
                </div>
            </div>
<!-- Movimientos -->
            <div class="col-md-12">
                <div class="small fw-semibold mb-1">Movimientos</div>
                <div id="summary-movement-list" class="summary-movements-list" style="max-height:200px;overflow-y:auto;font-size:12px;">
                    <!-- Los movimientos se renderizan aquí vía JS -->
                </div>
                <div id="summary-empty-message" class="text-center text-muted p-3 small">
                    <i class="fa-solid fa-box-open mb-1"></i><br>
                    Sin movimientos registrados
                </div>
            </div>
        </div>

    </div>
</div>


<div class="accordion" id="accordionPanelsStayOpenExample">
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="false" aria-controls="panelsStayOpen-collapseOne">
        Detalles y Auditoría
      </button>
    </h2>
    <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse">
      <div class="accordion-body">
            <!-- ===== ZONA DE AUDITORÍA INFERIOR ===== -->
            <div class="row g-3 mt-1">
                
                <!-- COL-6: Tabla de Llantas Seleccionadas (Inventario Activo) -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-semibold py-2">
                            <i class="fa-solid fa-list-check text-primary"></i> Inventario Activo
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0" id="tbl-active-tires">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Código</th>
                                            <th>Tamaño</th>
                                            <th>Ubicación Actual</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbl-active-tires-body">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3 small">
                                                No hay llantas activas en el documento.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COL-6: Bitácora de Movimientos (Historial Clínico) -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-semibold py-2">
                            <i class="fa-solid fa-clock-rotate-left text-secondary"></i> Bitácora de Movimientos
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                <table class="table table-sm table-striped mb-0" id="tbl-movement-log">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th style="width: 80px;">Hora</th>
                                            <th>Acción</th>
                                            <th>Detalle</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbl-movement-log-body">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3 small">
                                                Sin movimientos registrados aún.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
      </div>
    </div>
  </div>
</div>




<!-- Modal de selección de llantas (Se mantiene intacto) -->
<div id="mdl-tires" class="modal fade modal-lg" tabindex="-1" aria-labelledby="mdl-tires-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdl-tires-label">Seleccionar llantas del almacén</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover table-sm w-100" id="mdl-tbl-tires">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">Sel.</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Tamaño</th>
                                <th>Diseño</th>
                            </tr>
                        </thead>
                        <tbody id="mdl-tbl-tires-body">
                            <tr><td colspan="5" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-add-tires">Agregar seleccionadas</button>
            </div>
        </div>
    </div>
</div>