<!-- Tab: Asignación -->
<div class="col-md-12">
    <div class="card">
        <div class="card-body p-2">
            <h6 class="text-muted mb-3"><i class="fa-solid fa-truck-moving"></i> Asignación a Unidad</h6>
            
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text col-4"><b>Unidad Asignada</b></span>
                        <select class="form-control" name="tire[assigned_unit_code]" id="tire[assigned_unit_code]">
                            <option value="">-- Sin Asignar --</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-info" id="btn-view-unit" title="Ver Unidad">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text col-4"><b>Estado</b></span>
                        <input type="text" class="form-control bg-light" id="tire_assignment_status" readonly value="Sin Asignar">
                    </div>
                </div>
                <div class="col-md-4">
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text col-4"><b>Baja Definitiva</b></span>
                        <select class="form-control" name="tire[is_final]" id="tire[is_final]">
                            <option value="N">No</option>
                            <option value="Y">Sí</option>
                        </select>
                    </div>
                    
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text col-4"><b>Ubicación Actual</b></span>
                        <input type="text" class="form-control bg-light" id="tire_location_display" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-2">
        <div class="card-body p-2">
            <h6 class="text-muted mb-2"><i class="fa-solid fa-clock-rotate-left"></i> Historial de Asignaciones</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover" id="tire-assignments-history">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 150px;">Fecha Inicio</th>
                            <th style="width: 150px;">Fecha Fin</th>
                            <th>Unidad</th>
                            <th style="width: 80px;">Eje</th>
                            <th style="width: 100px;">Posición</th>
                            <th style="width: 120px;">Km Inicial</th>
                            <th style="width: 120px;">Km Final</th>
                            <th style="width: 120px;">Km Recorridos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                <i class="fa-solid fa-info-circle me-1"></i>
                                Sin historial de asignaciones
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="col-12 mt-2">
    <div class="alert alert-info alert-sm py-1 mb-0">
        <i class="fa-solid fa-info-circle me-1"></i>
        <small>
            <b>Nota:</b> La asignación a una unidad se registra automáticamente desde el módulo de vehículos. 
            El historial de asignaciones permite rastrear la vida útil de la llanta en diferentes unidades.
        </small>
    </div>
</div>
