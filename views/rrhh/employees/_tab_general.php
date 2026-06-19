<!-- Columna izquierda: Identidad -->
<div class="col-md-4 col-sd-12">
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Nombre</b> <span class="text-danger">*</span></span>
        <input type="text" class="form-control" placeholder="Nombre(s)" name="employee[first_name]" id="employee[first_name]" required>
        <div class="invalid-feedback"></div>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Apellido Paterno</b> <span class="text-danger">*</span></span>
        <input type="text" class="form-control" placeholder="Apellido Paterno" name="employee[last_name]" id="employee[last_name]" required>
        <div class="invalid-feedback"></div>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Apellido Materno</b></span>
        <input type="text" class="form-control" placeholder="Apellido Materno" name="employee[second_last_name]" id="employee[second_last_name]">
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Fecha Nacimiento</b></span>
        <input type="date" class="form-control" name="employee[birth_date]" id="employee[birth_date]">
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Género</b></span>
        <select class="form-control" name="employee[gender]" id="employee[gender]">
            <option value="">-- Seleccionar --</option>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
            <option value="O">Otro</option>
        </select>
    </div>
</div>
<div class="col-md-4 col-sd-12">
</div>

<!-- Columna derecha: Contacto y Registro -->
<div class="col-md-4 col-sd-12">
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>CURP</b></span>
        <input type="text" class="form-control" placeholder="CURP (18 dígitos)" name="employee[curp]" id="employee[curp]" maxlength="18">
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Teléfono</b></span>
        <input type="text" class="form-control" placeholder="Número telefónico" name="employee[phone_number]" id="employee[phone_number]">
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Correo Electrónico</b></span>
        <input type="email" class="form-control" placeholder="correo@ejemplo.com" name="employee[email]" id="employee[email]">
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Dirección</b></span>
        <textarea class="form-control" placeholder="Dirección completa" name="employee[address]" id="employee[address]" rows="3"></textarea>
    </div>
</div>
