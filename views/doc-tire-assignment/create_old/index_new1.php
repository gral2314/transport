<div class="doc-tire-form container-fluid py-4">
     
    <!-- Encabezado Principal (Mantiene tu estilo limpio) -->
    <div class="row align-items-center g-3 mb-4">
        <div class="col-md-7 col-lg-8">
            <div class="d-flex align-items-baseline gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0">Movimiento y Asignación de Llantas</h3>
                <span class="fs-4 text-secondary fw-semibold">#<span id="docnum-text">Se asigna al guardar</span></span>
                <span class="badge bg-primary px-2.5 py-1.5 ms-2 fs-7 shadow-sm">PLAN</span>
            </div>
            <div class="text-muted small">
                Gestione de forma visual la asignación, rotación y retiro de llantas para un máximo de 2 unidades en co-propiedad.
            </div>
        </div>
        
        <div class="col-md-5 col-lg-4 text-md-end">
            <div class="d-inline-flex gap-2">
                <a href="/trasnportone/web/doc-tire-assignment/index" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 px-3">
                    <svg class="svg-inline--fa fa-arrow-left" viewBox="0 0 512 512" style="width:12px;"><path fill="currentColor" d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 288 480 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-370.7 0 105.4-105.4c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"></path></svg> Regresar
                </a>
                <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 px-3" id="doc-tire-open-preview" disabled>
                    <svg class="svg-inline--fa fa-eye" viewBox="0 0 576 512" style="width:12px;"><path fill="currentColor" d="M288 32c-80.8 0-145.5 36.8-192.6 80.6-46.8 43.5-78.1 95.4-93 131.1-3.3 7.9-3.3 16.7 0 24.6 14.9 35.7 46.2 87.7 93 131.1 47.1 43.7 111.8 80.6 192.6 80.6s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1 3.3-7.9 3.3-16.7 0-24.6-14.9-35.7-46.2-87.7-93-131.1-47.1-43.7-111.8-80.6-192.6-80.6zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64-11.5 0-22.3-3-31.7-8.4-1 10.9-.1 22.1 2.9 33.2 13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-12.2-45.7-55.5-74.8-101.1-70.8 5.3 9.3 8.4 20.1 8.4 31.7z"></path></svg> Preview
                </button>
                <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1 px-4 shadow-sm" id="doc-tire-save">
                    <svg class="svg-inline--fa fa-floppy-disk" viewBox="0 0 448 512" style="width:12px;"><path fill="currentColor" d="M64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-242.7c0-17-6.7-33.3-18.7-45.3L352 50.7C340 38.7 323.7 32 306.7 32L64 32zm32 96c0-17.7 14.3-32 32-32l160 0c17.7 0 32 14.3 32 32l0 64c0 17.7-14.3 32-32 32l-160 0c-17.7 0-32-14.3-32-32l0-64zM224 288a64 64 0 1 1 0 128 64 64 0 1 1 0-128z"></path></svg> Guardar
                </button>
            </div>
        </div>
    </div>

    <form id="doc-tire-form-shell">
        <input type="hidden" name="docentry" id="docentry" value="">
        <input type="hidden" name="docnum" id="docnum" value="Se asigna al guardar">

        <div class="row g-4">
            <!-- Bloque Izquierdo: Formularios y Gráficos -->
            <div class="col-xl-9 col-lg-8">
                
                <!-- Card 1: Datos Generales -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                            <svg class="svg-inline--fa fa-circle-info text-primary me-1" viewBox="0 0 512 512" style="width:14px;"><path fill="currentColor" d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM224 160a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm-8 64l48 0c13.3 0 24 10.7 24 24l0 88 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-80 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l24 0 0-64-24 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z"></path></svg> Datos generales
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

                <!-- Card 2: Panel de Trabajo Operativo -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent pt-3 border-0">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#doc-tab-vehicles" type="button" role="tab">1. Selección de Unidades</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#doc-tab-details" type="button" role="tab">2. Taller Visual (Llantas)</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#doc-tab-attachments" type="button" role="tab">3. Evidencias y Adjuntos</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content">
                            
                            <!-- TAB 1: SELECCIÓN DE UNIDADES (Máximo 2) -->
                            <div class="tab-pane fade show active" id="doc-tab-vehicles" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <span class="fw-bold text-secondary small text-uppercase tracking-wider">Unidades Involucradas</span>
                                        <div class="text-muted text-xs">Selecciona el Tracto y/o Remolque para habilitar el taller visual (Máx. 2).</div>
                                    </div>
                                    <button type="button" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1" id="add-vehicle-row" data-bs-toggle="modal" data-bs-target="#mdl-units">
                                        <svg class="svg-inline--fa fa-plus" viewBox="0 0 448 512" style="width:10px;"><path fill="currentColor" d="M256 64c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 160-160 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l160 0 0 160c0 17.7 14.3 32 32 32s32-14.3 32-32l0-160 160 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-160 0 0-160z"></path></svg> Agregar Unidad
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0" id="doc-vehicles-table" style="width: 100%;">
                                    </table>
                                </div>
                            </div>

                            <!-- TAB 2: TALLER VISUAL (DISEÑO SPLIT GRÁFICO + TABLA LATERAL) -->
                            <div class="tab-pane fade" id="doc-tab-details" role="tabpanel">
                                <div class="row g-3">
                                    
                                    <!-- Sub-Columna Izquierda: Almacén Lateral de Llantas disponibles -->
                                    <div class="col-xl-4 col-lg-5">
                                        <div class="card border border-light-subtle shadow-sm h-100">
                                            <div class="card-header bg-light py-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark">Almacén de Llantas</h6>
                                                        <small class="text-muted text-xs">Arrastre al eje destino</small>
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-xs" id="add-detail-row">
                                                        + Nueva
                                                    </button>
                                                </div>
                                                <div class="mt-2">
                                                    <input type="text" class="form-control form-control-sm" id="search-tyre" placeholder="Buscar serie o eco...">
                                                </div>
                                            </div>

                                            <div class="card-body p-0">
                                                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                                    <table class="table table-sm table-hover align-middle mb-0" id="doc-details-table">
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sub-Columna Derecha: El Canvas del Camión (Soporta 1 o 2 unidades lado a lado) -->
                                    <div class="col-xl-8 col-lg-7">
                                        <div class="card border border-light-subtle shadow-sm h-100 bg-light-subtle">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">Distribución Física en Patio</h6>
                                                    <small class="text-muted">Intercambie llantas libremente entre posiciones u unidades</small>
                                                </div>
                                                <button type="button" class="btn btn-outline-danger btn-sm text-xs d-none" id="btn-reset-layout">Limpiar Cambios</button>
                                            </div>

                                            <div class="card-body p-3">
                                                <!-- Contenedor Dinámico Flexible: Si hay 2 unidades se acomodan lado a lado automáticamente en pantallas grandes -->
                                                <div id="dynamic-truck-container" class="d-flex flex-wrap justify-content-center gap-4 align-items-start">
                                                    
                                                    <!-- UNIDAD 1 (Ejemplo de Render de Tracto) -->
                                                    <div class="truck-chassis-layout bg-white border rounded shadow-sm p-3" style="width: 250px; border-top: 4px solid #0d6efd !important;">
                                                        <div class="text-center border-bottom pb-1 mb-3">
                                                            <span class="fw-bold text-dark small d-block">TRACTO: TR-01</span>
                                                            <span class="text-muted text-xxs">Frente (Cabina) ↑</span>
                                                        </div>
                                                        <!-- Eje 1 Dirección -->
                                                        <div class="truck-axis-row mb-4" data-axis-index="1">
                                                            <div class="d-flex justify-content-between align-items-center position-relative">
                                                                <div class="tyre-drop-zone empty border rounded text-center text-xxs" data-position="TR-01|1-IZQ" style="width:45px; height:65px; line-height:65px;">E1-IZQ</div>
                                                                <span class="badge bg-light text-secondary border text-xxs">Eje 1</span>
                                                                <div class="tyre-drop-zone empty border rounded text-center text-xxs" data-position="TR-01|1-DER" style="width:45px; height:65px; line-height:65px;">E1-DER</div>
                                                            </div>
                                                        </div>
                                                        <!-- Eje 2 Tracción (Doble Rodado) -->
                                                        <div class="truck-axis-row" data-axis-index="2">
                                                            <div class="d-flex justify-content-between align-items-center position-relative">
                                                                <div class="d-flex gap-1">
                                                                    <div class="tyre-drop-zone empty border rounded text-center text-xxs" data-position="TR-01|2-IZQ-EXT" style="width:38px; height:65px; line-height:65px;">E2-IE</div>
                                                                    <div class="tyre-drop-zone empty border rounded text-center text-xxs" data-position="TR-01|2-IZQ-INT" style="width:38px; height:65px; line-height:65px;">E2-II</div>
                                                                </div>
                                                                <span class="badge bg-light text-secondary border text-xxs">Eje 2</span>
                                                                <div class="d-flex gap-1">
                                                                    <div class="tyre-drop-zone empty border rounded text-center text-xxs" data-position="TR-01|2-DER-INT" style="width:38px; height:65px; line-height:65px;">E2-DI</div>
                                                                    <div class="tyre-drop-zone empty border rounded text-center text-xxs" data-position="TR-01|2-DER-EXT" style="width:38px; height:65px; line-height:65px;">E2-DE</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- UNIDAD 2 (Ejemplo de Render de Remolque en Paralelo) -->
                                                    <div class="truck-chassis-layout bg-white border rounded shadow-sm p-3" style="width: 250px; border-top: 4px solid #198754 !important;">
                                                        <div class="text-center border-bottom pb-1 mb-3">
                                                            <span class="fw-bold text-dark small d-block">REMOLQUE: RE-02</span>
                                                            <span class="text-muted text-xxs">Frente Remolque ↑</span>
                                                        </div>
                                                        <!-- Eje 1 Remolque -->
                                                        <div class="truck-axis-row" data-axis-index="1">
                                                            <div class="d-flex justify-content-between align-items-center position-relative">
                                                                <div class="d-flex gap-1">
                                                                    <div class="tyre-drop-zone empty border rounded text-center text-xxs" data-position="RE-02|1-IZQ-EXT" style="width:38px; height:65px; line-height:65px;">R1-IE</div>
                                                                    <div class="tyre-drop-zone empty border rounded text-center text-xxs" data-position="RE-02|1-IZQ-INT" style="width:38px; height:65px; line-height:65px;">R1-II</div>
                                                                </div>
                                                                <span class="badge bg-light text-secondary border text-xxs">Eje 1</span>
                                                                <div class="d-flex gap-1">
                                                                    <div class="tyre-drop-zone empty border rounded text-center text-xxs" data-position="RE-02|1-DER-INT" style="width:38px; height:65px; line-height:65px;">R1-DI</div>
                                                                    <div class="tyre-drop-zone empty border rounded text-center text-xxs" data-position="RE-02|1-DER-EXT" style="width:38px; height:65px; line-height:65px;">R1-DE</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <!-- Zona de Baja / Desinstalación física -->
                                                <div id="trash-drop-zone" class="w-100 border border-dashed border-danger rounded p-2 text-center text-danger mt-3 small" style="background-color: #fff5f5;">
                                                    <i class="bi bi-trash-fill me-1"></i> Arrastre una llanta aquí para **Retirarla/Darla de baja** de la unidad
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- TAB 3: ADJUNTOS (Se queda igual) -->
                            <div class="tab-pane fade" id="doc-tab-attachments" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold text-secondary small text-uppercase tracking-wider">Evidencias y adjuntos</span>
                                    <button type="button" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1" id="add-attachment-row">
                                        <svg class="svg-inline--fa fa-plus" viewBox="0 0 448 512" style="width:10px;"><path fill="currentColor" d="M256 64c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 160-160 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l160 0 0 160c0 17.7 14.3 32 32 32s32-14.3 32-32l0-160 160 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-160 0 0-160z"></path></svg> Agregar Archivo
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0" id="doc-attachments-table">
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- Bloque Derecho: El Resumen Lateral Deslizante -->
            <div class="col-xl-3 col-lg-4">
                <div class="d-flex flex-column gap-4 position-sticky" style="top: 1rem;">
                    <div class="card border-0 shadow-sm bg-dark text-white">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3 tracking-wide text-uppercase small text-info">Resumen de Movimientos</h6>
                            <div class="d-flex justify-content-between small mb-2 border-bottom border-secondary pb-2 opacity-75">
                                <span>Montajes Nuevos</span>
                                <strong id="summary-assigned-count" class="text-success">0</strong>
                            </div>
                            <div class="d-flex justify-content-between small mb-2 border-bottom border-secondary pb-2 opacity-75">
                                <span>Rotaciones Internas</span>
                                <strong id="summary-rotation-count" class="text-warning">0</strong>
                            </div>
                            <div class="d-flex justify-content-between small mb-2 border-bottom border-secondary pb-2 opacity-75">
                                <span>Retiros / Bajas</span>
                                <strong id="summary-baja-count" class="text-danger">0</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<!-- Modal de Selección de Unidades (Optimizado para que valide el límite de 2) -->
<div id="mdl-units" class="modal fade modal-lg" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Buscar Unidades Disponibles</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Seleccione un máximo de **2 unidades** para trabajar simultáneamente (Ideal: Tracto + Remolque).</h6>
                <table class="table table-sm table-hover align-middle mb-0" id="mdl-tbl-units" style="width: 100%;">
                    </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm">Vincular al Documento</button>
            </div>
        </div>
    </div>
</div>