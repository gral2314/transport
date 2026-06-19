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
//DynamicAssetBundle::register($this);

// CrudWidget solo para la tabla (sin modal)
?>

<style>
    /* Estilos para el Módulo Visual de Llantas */
    .cursor-grab {
        cursor: grab;
    }
    .cursor-grab:active {
        cursor: grabbing;
    }

    /* Contenedor del camión */
    .truck-chassis-layout {
        background-image: linear-gradient(rgba(240, 240, 240, 0.5) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(240, 240, 240, 0.5) 1px, transparent 1px);
        background-size: 10px 10px;
    }

    /* Línea gris que representa el eje de metal */
    .axis-line {
        position: absolute;
        top: 50%;
        left: 10%;
        right: 10%;
        height: 6px;
        background-color: #dee2e6;
        transform: translateY(-50%);
        z-index: 0;
    }

    /* Caja contenedora / Zona de soltar (Drop Zone) */
    .tyre-drop-zone {
        width: 38px;
        height: 62px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
        transition: all 0.2s ease;
    }

    /* Zona vacía: Esperando Llanta */
    .tyre-drop-zone.empty {
        border: 2px dashed #cbd5e1;
        background-color: #f8fafc;
    }
    .tyre-drop-zone.empty:hover, .tyre-drop-zone.drag-over {
        border-color: #0d6efd;
        background-color: #e7f1ff;
        transform: scale(1.05);
    }

    /* Texto de guía interno en las zonas vacías */
    .tyre-drop-zone .zone-label {
        font-size: 0.55rem;
        font-weight: bold;
        color: #94a3b8;
        text-align: center;
        pointer-events: none; /* Evita interferencia con el drag */
    }

    /* La Llanta física ya colocada */
    .tyre-placed {
        width: 100%;
        height: 100%;
        background-color: #1e293b; /* Color llanta oscura */
        border: 2px solid #475569;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #f8fafc;
        cursor: pointer;
        box-shadow: inset 0 0 4px rgba(255,255,255,0.2);
    }
    .tyre-placed:hover {
        background-color: #0f172a;
        border-color: #3b82f6;
    }

    .tyre-placed .tyre-code {
        font-size: 0.55rem;
        font-weight: bold;
        letter-spacing: -0.5px;
        white-space: nowrap;
        transform: rotate(-90deg); /* Rota el texto para que quepa verticalmente */
    }
    /* Utilidad de texto extra pequeño */
    .text-xs { font-size: 0.75rem !important; }

    /* Efecto cuando arrastras un elemento válido sobre una zona drop */
    .tyre-drop-zone.drag-over {
        border-color: #198754 !important; /* Verde bootstrap */
        background-color: #d1e7dd !important;
        box-shadow: 0 0 8px rgba(25, 135, 84, 0.5);
    }

    /* Efecto visual si la zona está "bloqueada" o inválida */
    .tyre-drop-zone.drag-invalid {
        border-color: #dc3545 !important;
        background-color: #f8d7da !important;
        cursor: no-drop;
    }

    /* Animación sutil para la zona de descarte */
    #trash-drop-zone.drag-over {
        background-color: #f8d7da !important;
        border-color: #dc3545 !important;
        color: #a51d24 !important;
        transform: scale(1.02);
        transition: all 0.2s ease;
    }
</style>

<div class="doc-tire-form container-fluid py-4">
    
    <div class="row align-items-center g-3 mb-4">
        <div class="col-md-7 col-lg-8">
            <div class="d-flex align-items-baseline gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0">Asignación de Llantas</h3>
                <span class="fs-4 text-secondary fw-semibold">#<span id="docnum-text">Se asigna al guardar</span></span>
                <span class="badge bg-primary px-2.5 py-1.5 ms-2 fs-7 shadow-sm">PLAN</span>
            </div>
            <div class="text-muted small">
                Captura el encabezado, líneas operativas, evidencias y resumen antes de cerrar o cancelar.
            </div>
        </div>
        
        <div class="col-md-5 col-lg-4 text-md-end">
            <div class="d-inline-flex gap-2">
                <a href="/trasnportone/web/doc-tire-assignment/index" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 px-3">
                    <i class="fa-solid fa-arrow-left"></i> Regresar
                </a>
                <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 px-3" id="doc-tire-open-preview" disabled>
                    <i class="fa-solid fa-eye"></i> Preview
                </button>
                <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1 px-4 shadow-sm" id="doc-tire-save">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </div>
        </div>
    </div>

    <form id="doc-tire-form-shell">
        <input type="hidden" name="docentry" id="docentry" value="">
        <input type="hidden" name="docnum" id="docnum" value="Se asigna al guardar">

        <div class="row g-4">
            <div class="col-xl-9 col-lg-8">
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i> Datos generales
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-3 col-sm-6">
                                <label for="doc_date" class="form-label small fw-semibold text-secondary">Fecha Documento</label>
                                <input type="date" class="form-control form-control-sm" name="doc_date" id="doc_date" value="2026-06-04">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label for="doc_duedate" class="form-label small fw-semibold text-secondary">Fecha Ejecución</label>
                                <input type="date" class="form-control form-control-sm" name="doc_duedate" id="doc_duedate" value="2026-06-04">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label for="priority" class="form-label small fw-semibold text-secondary">Prioridad</label>
                                <select class="form-select form-select-sm" name="priority" id="priority">
                                    <option value="">Seleccionar...</option>
                                    <option value="LOW" selected>Baja</option>
                                    <option value="MEDIUM">Media</option>
                                    <option value="HIGH">Alta</option>
                                    <option value="URGENT">Urgente</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label for="origin_type" class="form-label small fw-semibold text-secondary">Origen</label>
                                <select class="form-select form-select-sm" name="origin_type" id="origin_type">
                                    <option value="">Seleccionar...</option>
                                    <option value="MANUAL" selected>Manual</option>
                                    <option value="MAINTENANCE">Mantenimiento</option>
                                    <option value="INSPECTION">Inspección</option>
                                    <option value="REPAIR">Reparación</option>
                                    <option value="WAREHOUSE">Almacén</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent pt-3 border-0">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#doc-tab-vehicles" type="button" role="tab" aria-selected="true">Unidades</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#doc-tab-details" type="button" role="tab" aria-selected="false">Detalles</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#doc-tab-attachments" type="button" role="tab" aria-selected="false">Adjuntos</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="doc-tab-vehicles" role="tabpanel">
                                <?php require_once('_unidades.php'); ?>
                                
                            </div>

                            <div class="tab-pane fade" id="doc-tab-details" role="tabpanel">
                                <div class="row g-4">
                                    <?php require_once('_llantas.php'); ?>
                                </div>
                            </div>

<!-- <template id="template-truck-6x4">
    <div class="truck-chassis-layout d-flex flex-column gap-5 position-relative bg-white border rounded shadow-sm p-5" style="width: 280px; border-left: 4px solid #0d6efd !important;">
        <div class="position-absolute top-0 start-50 translate-middle-x bg-secondary text-white text-uppercase tracking-wider px-3 py-0.5 rounded-bottom" style="font-size: 0.65rem; font-weight: bold;">
            <i class="fa-solid fa-caret-up"></i> Frente (Cabina)
        </div>
        
        <div class="truck-axis-row" data-axis-index="1">
            <div class="d-flex justify-content-between align-items-center position-relative">
                <div class="axis-line"></div>
                <div class="tyre-drop-zone empty" data-position="1-IZQ"><div class="zone-label">E1-IZQ</div></div>
                <span class="badge bg-secondary-subtle text-secondary small z-1">Eje 1</span>
                <div class="tyre-drop-zone empty" data-position="1-DER"><div class="zone-label">E1-DER</div></div>
            </div>
        </div>
        <div class="truck-axis-row" data-axis-index="2">
            <div class="d-flex justify-content-between align-items-center position-relative">
                <div class="axis-line"></div>
                <div class="d-flex gap-1">
                    <div class="tyre-drop-zone empty" data-position="2-IZQ-EXT"><div class="zone-label">E2-IE</div></div>
                    <div class="tyre-drop-zone empty" data-position="2-IZQ-INT"><div class="zone-label">E2-II</div></div>
                </div>
                <span class="badge bg-secondary-subtle text-secondary small z-1">Eje 2</span>
                <div class="d-flex gap-1">
                    <div class="tyre-drop-zone empty" data-position="2-DER-INT"><div class="zone-label">E2-DI</div></div>
                    <div class="tyre-drop-zone empty" data-position="2-DER-EXT"><div class="zone-label">E2-DE</div></div>
                </div>
            </div>
        </div>
        <div class="truck-axis-row" data-axis-index="3">
            <div class="d-flex justify-content-between align-items-center position-relative">
                <div class="axis-line"></div>
                <div class="d-flex gap-1">
                    <div class="tyre-drop-zone empty" data-position="3-IZQ-EXT"><div class="zone-label">E3-IE</div></div>
                    <div class="tyre-drop-zone empty" data-position="3-IZQ-INT"><div class="zone-label">E3-II</div></div>
                </div>
                <span class="badge bg-secondary-subtle text-secondary small z-1">Eje 3</span>
                <div class="d-flex gap-1">
                    <div class="tyre-drop-zone empty" data-position="3-DER-INT"><div class="zone-label">E3-DI</div></div>
                    <div class="tyre-drop-zone empty" data-position="3-DER-EXT"><div class="zone-label">E3-DE</div></div>
                </div>
            </div>
        </div>
    </div>
</template> -->


                            <div class="tab-pane fade" id="doc-tab-attachments" role="tabpanel">
                                <?php require_once('_anexos.php'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4">
                <div class="d-flex flex-column gap-4 position-sticky" style="top: 1rem;">
                    
                    <div class="card border-0 shadow-sm bg-dark text-white">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3 tracking-wide text-uppercase small text-info">Resumen del Documento</h6>
                            <div class="d-flex justify-content-between small mb-2 border-bottom border-secondary pb-2 opacity-75">
                                <span>Líneas de detalle</span>
                                <strong id="summary-detail-count">0</strong>
                            </div>
                            <div class="d-flex justify-content-between small mb-2 border-bottom border-secondary pb-2 opacity-75">
                                <span>Archivos adjuntos</span>
                                <strong id="summary-attachment-count">0</strong>
                            </div>
                            <div class="d-flex justify-content-between small mb-0 pb-1 opacity-75">
                                <span>Unidades vinculadas</span>
                                <strong id="summary-vehicle-count">0</strong>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <label for="comments" class="form-label fw-bold text-dark small text-uppercase tracking-wider mb-2">Comentarios Generales</label>
                            <textarea class="form-control bg-light text-dark" rows="5" name="comments" id="comments" placeholder="Escribe aquí observaciones internas del documento..."></textarea>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>
