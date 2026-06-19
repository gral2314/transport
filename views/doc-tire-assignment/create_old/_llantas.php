<div class="col-xl-5 col-lg-6">
    <div class="card border border-light-subtle shadow-sm h-100">
        <div class="card-header bg-light py-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Llantas por Asignar</h6>
                    <small class="text-muted text-xs">Paso 2: Arrastre las llantas al gráfico</small>
                </div>
                <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1"
                    id="add-detail-row">
                    <i class="fa-solid fa-plus"></i> Agregar Llanta
                </button>
            </div>

            <div class="row g-2">
                <div class="col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i
                                class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" class="form-control form-control-sm border-start-0" id="search-tyre"
                            placeholder="Buscar por económico, serie o marca...">
                    </div>
                </div>
                <div class="col-12">
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <input type="radio" class="btn-check" name="filterTyres" id="btn-filter-all" checked>
                        <label class="btn btn-outline-secondary" for="btn-filter-all">Todas (<span
                                id="count-all">2</span>)</label>

                        <input type="radio" class="btn-check" name="filterTyres" id="btn-filter-pending">
                        <label class="btn btn-outline-secondary" for="btn-filter-pending">Pendientes</label>

                        <input type="radio" class="btn-check" name="filterTyres" id="btn-filter-assigned">
                        <label class="btn btn-outline-secondary" for="btn-filter-assigned">Ubicadas</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm table-hover align-middle mb-0" id="doc-details-table">
                    <thead class="table-light text-secondary sticky-top" style="top: 0; z-index: 1;">
                        <tr>
                            <th class="ps-3" style="width: 50px;">Estado</th>
                            <th>Información de Llanta</th>
                            <th>Movimiento</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="doc-details-body">
                        <tr class="tyre-draggable-row" draggable="true" data-tyre-id="LL-1024" data-status="pending">
                            <td class="text-center ps-3">
                                <div class="form-check d-inline-block">
                                    <input class="form-check-input border-danger status-checkbox" type="checkbox"
                                        disabled>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark small tyre-title">LL-1024 (Michelin 295/80)</div>
                                <div class="text-muted text-xs">Serie: 9823471A</div>
                            </td>
                            <td><span
                                    class="badge bg-light text-success border border-success-subtle py-1">Montaje</span>
                            </td>
                            <td class="pe-3 text-end">
                                <span class="text-muted cursor-grab"><i class="fa-solid fa-grip-vertical"></i></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-7 col-lg-6">
    <div class="card border border-light-subtle shadow-sm h-100 bg-light-subtle">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
            <div>
                <h6 class="mb-0 fw-bold text-dark">Distribución Gráfica de la Unidad</h6>
                <small class="text-muted" id="selected-vehicle-label">Seleccione una unidad en la pestaña
                    anterior</small>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm text-xs d-none" id="btn-reset-layout">
                <i class="fa-solid fa-trash-can"></i> Limpiar Posiciones
            </button>
        </div>

        <div class="card-body d-flex flex-column align-items-center justify-content-center p-4 position-relative"
            style="min-height: 450px;">

            <div id="truck-empty-state" class="text-center text-muted p-5">
                <i class="fa-solid fa-truck-front fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">Por favor, agregue y seleccione una unidad en la pestaña <strong>"Unidades"</strong>
                    para cargar su configuración de ejes.</p>
            </div>

            <div id="dynamic-truck-container" class="d-none">
            </div>

            <div id="trash-drop-zone"
                class="w-100 border border-dashed border-danger rounded p-3 text-center text-danger mt-4 d-none"
                style="background-color: #fff5f5;">
                <i class="fa-solid fa-arrow-rotate-left me-1"></i> Arrastra una llanta aquí para quitarla de la posición
                actual
            </div>

        </div>
    </div>
</div>