<!-- Columna izquierda -->
<div class="col-md-4 col-sd-12">
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Marca</b></span>
        <select class="form-control" name="vehicle[brand_code]" id="vehicle[brand_code]">
            <option value="">-- Seleccionar --</option>
        </select>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Modelo</b></span>
        <input type="text" class="form-control" placeholder="Modelo" name="vehicle[model]" id="vehicle[model]">
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Año</b></span>
        <input type="text" class="form-control" placeholder="Año" name="vehicle[unit_year]" id="vehicle[unit_year]">
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Placas</b></span>
        <input type="text" class="form-control" placeholder="Placas" name="vehicle[plate_no]" id="vehicle[plate_no]">
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>No. Económico</b></span>
        <input type="text" class="form-control" placeholder="No. Económico" name="vehicle[economic_no]" id="vehicle[economic_no]">
    </div>

    <hr>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>IAVE / TELEVIA</b></span>
        <input type="text" class="form-control" placeholder="IAVE / TELEVIA" name="vehicle[iave]" id="vehicle[iave]">
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Config. SAT</b></span>
        <select class="form-control" name="vehicle[sat_vehicle_config_code]" id="vehicle[sat_vehicle_config_code]">
            <option value="">-- Seleccionar --</option>
        </select>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>NOM-012</b></span>
        <select class="form-control" name="vehicle[nom012_code]" id="vehicle[nom012_code]">
            <option value="">-- Seleccionar --</option>
        </select>
    </div>


</div>
<!-- Columna central -->
<div class="col-md-4 col-sd-12">
</div>
<!-- Columna derecha -->
<div class="col-md-4 col-sd-12">
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Adquisición</b></span>
        <select class="form-control" name="vehicle[acquisition]" id="vehicle[acquisition]">
            <option value="P">Compra</option>
            <option value="R">Renta</option>
        </select>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Fecha Compra</b></span>
        <input type="date" class="form-control" placeholder="Fecha Compra" name="vehicle[purchase_date]" id="vehicle[purchase_date]">
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Precio Compra</b></span>
        <input type="text" class="form-control" placeholder="Precio Compra" name="vehicle[purchase_price]" id="vehicle[purchase_price]">
    </div>
    <hr>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Tipo Servicio</b></span>
        <select class="form-control" name="vehicle[service_type_code]" id="vehicle[service_type_code]">
            <option value="">-- Seleccionar --</option>
        </select>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Tipo Carga</b></span>
        <select class="form-control" name="vehicle[cargo_type_code]" id="vehicle[cargo_type_code]">
            <option value="">-- Seleccionar --</option>
        </select>
    </div>
    <hr>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Comentarios</b></span>
        
        <textarea class="form-control" placeholder="Comentarios" name="vehicle[notes]" id="vehicle[notes]" rows="4"></textarea>
    </div>

</div>
