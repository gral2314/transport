<!-- Tab: Datos Técnicos -->
<div class="col-md-4">
    <h6 class="text-muted mb-2"><i class="fa-solid fa-ruler"></i> Especificaciones Dimensionales</h6>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Ancho Llanta</b></span>
        <input type="number" step="0.01" class="form-control text-end" placeholder="0.00" name="tire[tire_width]" id="tire[tire_width]">
        <span class="input-group-text">mm</span>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Relación Aspecto</b></span>
        <input type="number" step="0.01" class="form-control text-end" placeholder="0.00" name="tire[aspect_ratio]" id="tire[aspect_ratio]">
        <span class="input-group-text">%</span>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Tipo Estructura</b></span>
        <select class="form-control" name="tire[structure_type]" id="tire[structure_type]">
            <option value="">-- Seleccionar --</option>
            <option value="R">R - Radial</option>
            <option value="B">B - Bias</option>
            <option value="D">D - Diagonal</option>
        </select>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Tamaño Rin</b></span>
        <input type="number" step="0.01" class="form-control text-end" placeholder="0.00" name="tire[rim_size]" id="tire[rim_size]">
        <span class="input-group-text">pulg</span>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Diseño Rodada</b></span>
        <select class="form-control" name="tire[tread_design_code]" id="tire[tread_design_code]">
            <option value="">-- Seleccionar --</option>
        </select>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>País Fabricación</b></span>
        <select class="form-control" name="tire[country_code]" id="tire[country_code]">
            <option value="">-- Seleccionar --</option>
        </select>
    </div>
</div>
<div class="col-md-4">
</div>
<div class="col-md-4">
    <h6 class="text-muted mb-2"><i class="fa-solid fa-weight-hanging"></i> Capacidades y Clasificación</h6>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Índice de Carga</b></span>
        <input type="text" class="form-control" placeholder="Ej: 120" name="tire[load_idx]" id="tire[load_idx]">
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Carga Máxima</b></span>
        <input type="number" step="0.01" class="form-control text-end" placeholder="0.00" name="tire[max_load]" id="tire[max_load]">
        <span class="input-group-text">kg</span>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Presión Máxima</b></span>
        <input type="number" step="0.01" class="form-control text-end" placeholder="0.00" name="tire[max_press]" id="tire[max_press]">
        <span class="input-group-text">PSI</span>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Clasif. Tracción</b></span>
        <select class="form-control" name="tire[traction_rate]" id="tire[traction_rate]">
            <option value="">-- Seleccionar --</option>
            <option value="AA">AA - Excelente</option>
            <option value="A">A - Buena</option>
            <option value="B">B - Regular</option>
            <option value="C">C - Aceptable</option>
        </select>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Clasif. Temperatura</b></span>
        <select class="form-control" name="tire[temp_rate]" id="tire[temp_rate]">
            <option value="">-- Seleccionar --</option>
            <option value="A">A - Alta Resistencia</option>
            <option value="B">B - Resistencia Media</option>
            <option value="C">C - Resistencia Básica</option>
        </select>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Tipo de Uso</b></span>
        <select class="form-control" name="tire[usage_type_code]" id="tire[usage_type_code]">
            <option value="">-- Seleccionar --</option>
        </select>
    </div>
</div>

<div class="col-12 mt-2">
    <div class="alert alert-secondary alert-sm py-1 mb-0">
        <i class="fa-solid fa-lightbulb me-1"></i>
        <small>
            <b>Guía rápida:</b> 
            Estructura R (Radial) es la más común en vehículos modernos. 
            Clasificación de Tracción: AA/A mejor agarre en mojado. 
            Temperatura: A mayor resistencia al calor.
        </small>
    </div>
</div>
