<!-- Tab: Información General -->
<div class="col-md-4">
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-4"><b>Marca</b> <span class="text-danger">*</span></span>
        <select class="form-control" name="tire[brand_code]" id="tire[brand_code]" required>
            <option value="">-- Seleccionar --</option>
        </select>
        <div class="invalid-feedback"></div>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-4"><b>Modelo</b> <span class="text-danger">*</span></span>
        <select class="form-control" name="tire[model_code]" id="tire[model_code]" required>
            <option value="">-- Seleccionar --</option>
        </select>
        <div class="invalid-feedback"></div>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-4"><b>Medida</b> <span class="text-danger">*</span></span>
        <select class="form-control" name="tire[size_code]" id="tire[size_code]" required>
            <option value="">-- Seleccionar --</option>
        </select>
        <div class="invalid-feedback"></div>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-4"><b>Tipo de Llanta</b> <span class="text-danger">*</span></span>
        <select class="form-control" name="tire[type_code]" id="tire[type_code]" required>
            <option value="">-- Seleccionar --</option>
        </select>
        <div class="invalid-feedback"></div>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-4"><b>Número de Serie</b></span>
        <input type="text" class="form-control" placeholder="Serie" name="tire[serial_no]" id="tire[serial_no]">
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-4"><b>Código DOT</b></span>
        <input type="text" class="form-control" placeholder="DOT" name="tire[dot_code]" id="tire[dot_code]">
        <button type="button" class="btn btn-sm btn-outline-info" id="btn-dot-info" title="Info DOT">
            <i class="fa-solid fa-circle-info"></i>
        </button>
    </div>
</div>

<div class="col-md-4">
</div>
<div class="col-md-4">
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-4"><b>Fecha Fabricación</b></span>
        <input type="date" class="form-control" name="tire[manufacture_date]" id="tire[manufacture_date]">
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-4"><b>Fecha Compra</b></span>
        <input type="date" class="form-control" name="tire[purchase_date]" id="tire[purchase_date]">
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-4"><b>Precio Compra</b></span>
        <input type="number" step="0.01" class="form-control text-end" placeholder="0.00" name="tire[purchase_price]" id="tire[purchase_price]">
        <span class="input-group-text">$</span>
    </div>
    
    <!-- <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-4"><b>Objeto</b> <span class="text-danger">*</span></span>
        <input type="text" class="form-control" placeholder="Objeto SAP" name="tire[object]" id="tire[object]" >
        <div class="invalid-feedback"></div>
    </div> -->
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-4"><b>Km Máximos</b></span>
        <input type="number" step="0.01" class="form-control text-end" placeholder="0.00" name="tire[max_km]" id="tire[max_km]">
        <span class="input-group-text">km</span>
    </div>
    
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-4"><b>No. Reencauches</b></span>
        <input type="number" class="form-control text-end" placeholder="0" name="tire[retread_qty]" id="tire[retread_qty]" value="0">
    </div>
</div>

<div class="col-12 mt-2">
    <div class="alert alert-info alert-sm py-1 mb-0">
        <i class="fa-solid fa-info-circle me-1"></i>
        <small><b>Nota:</b> Los campos marcados con <span class="text-danger">*</span> son obligatorios.</small>
    </div>
</div>
