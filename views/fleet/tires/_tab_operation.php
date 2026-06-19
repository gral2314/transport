<!-- Tab: Operación -->
<div class="col-md-4">
    <h6 class="text-muted mb-2"><i class="fa-solid fa-gauge-high"></i> Métricas de Desgaste</h6>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Prof. Original</b></span>
        <input type="number" step="0.01" class="form-control text-end" placeholder="0.00" name="tire[orig_tread_depth]" id="tire[orig_tread_depth]">
        <span class="input-group-text">mm</span>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-copy-orig-to-init" title="Copiar a Inicial">
            <i class="fa-solid fa-arrow-down"></i>
        </button>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Prof. Inicial</b></span>
        <input type="number" step="0.01" class="form-control text-end" placeholder="0.00" name="tire[init_tread_depth]" id="tire[init_tread_depth]">
        <span class="input-group-text">mm</span>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Prof. Actual</b></span>
        <input type="number" step="0.01" class="form-control text-end" placeholder="0.00" name="tire[curr_tread_depth]" id="tire[curr_tread_depth]">
        <span class="input-group-text">mm</span>
        <button type="button" class="btn btn-sm btn-outline-info" id="btn-calc-wear" title="Calcular Desgaste">
            <i class="fa-solid fa-calculator"></i>
        </button>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Factor Desgaste</b></span>
        <input type="number" step="0.0001" class="form-control text-end" placeholder="0.0000" name="tire[tread_wear_factor]" id="tire[tread_wear_factor]" readonly>
        <span class="input-group-text">mm/km</span>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>% Desgaste</b></span>
        <input type="text" class="form-control text-end bg-light" id="tire_wear_percentage" readonly placeholder="-">
        <span class="input-group-text">%</span>
    </div>
    
    <div class="progress mb-2" style="height: 20px;">
        <div class="progress-bar" id="tire_wear_progress" role="progressbar" style="width: 0%;" 
             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
    </div>
</div>
<div class="col-md-4">
</div>
<div class="col-md-4">
    <h6 class="text-muted mb-2"><i class="fa-solid fa-road"></i> Historial de Uso</h6>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Km Inicial</b></span>
        <input type="number" step="0.01" class="form-control text-end" placeholder="0.00" name="tire[init_km]" id="tire[init_km]">
        <span class="input-group-text">km</span>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Km Actuales</b></span>
        <input type="text" class="form-control text-end bg-light" id="tire_current_km_display" readonly>
        <span class="input-group-text">km</span>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Km Recorridos</b></span>
        <input type="text" class="form-control text-end bg-light" id="tire_traveled_km" readonly placeholder="-">
        <span class="input-group-text">km</span>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Cant. Reparaciones</b></span>
        <input type="number" class="form-control text-end" placeholder="0" name="tire[repair_qty]" id="tire[repair_qty]" value="0">
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>No. Reencauches</b></span>
        <input type="text" class="form-control text-end bg-light" id="tire_retread_display" readonly>
    </div>
</div>

<div class="col-12 mt-2">
    <div class="alert alert-warning alert-sm py-1 mb-0">
        <i class="fa-solid fa-triangle-exclamation me-1"></i>
        <small>
            <b>Importante:</b> La profundidad mínima legal es 1.6mm. Se recomienda reemplazo a 3mm. 
            El factor de desgaste se calcula automáticamente: (Prof. Inicial - Prof. Actual) / Km Recorridos.
        </small>
    </div>
</div>
