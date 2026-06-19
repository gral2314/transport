<!-- Columna izquierda: Puesto e Ingreso -->
<div class="col-md-4 col-sd-12">
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Fecha de Ingreso</b> <span class="text-danger">*</span></span>
        <input type="date" class="form-control" name="employee[hire_date]" id="employee[hire_date]" >
        <div class="invalid-feedback"></div>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Puesto</b> <span class="text-danger">*</span></span>
        <select class="form-control" name="employee[position_code]" id="employee[position_code]" >
            <option value="">-- Seleccionar --</option>
        </select>
        <div class="invalid-feedback"></div>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Área</b> <span class="text-danger">*</span></span>
        <select class="form-control" name="employee[area_code]" id="employee[area_code]" >
            <option value="">-- Seleccionar --</option>
        </select>
        <div class="invalid-feedback"></div>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Sucursal</b></span>
        <select class="form-control" name="employee[branch_code]" id="employee[branch_code]">
            <option value="">-- Seleccionar --</option>
        </select>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Tipo Empleado</b></span>
        <select class="form-control" name="employee[employee_type_code]" id="employee[employee_type_code]">
            <option value="">-- Seleccionar --</option>
        </select>
    </div>
</div>
<div class="col-md-4 col-sd-12">
</div>

<!-- Columna derecha: Administración, Estatus e Historial -->
<div class="col-md-4 col-sd-12">
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Usuario Sistema</b></span>
        <select class="form-control" name="employee[user_id]" id="employee[user_id]">
            <option value="">-- Seleccionar --</option>
        </select>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Jefe Directo</b></span>
        <select class="form-control" name="employee[direct_manager_code]" id="employee[direct_manager_code]">
            <option value="">-- Seleccionar --</option>
        </select>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Turno</b></span>
        <select class="form-control" name="employee[shift_type]" id="employee[shift_type]">
            <option value="">-- Seleccionar --</option>
            <option value="MORNING">Matutino</option>
            <option value="EVENING">Vespertino</option>
            <option value="NIGHT">Nocturno</option>
            <option value="MIXED">Mixto</option>
        </select>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Estatus</b> <span class="text-danger">*</span></span>
        <select class="form-control" name="employee[employee_status]" id="employee[employee_status]" required>
            <option value="ACTIVE">Activo</option>
            <option value="INACTIVE">Inactivo</option>
            <option value="SUSPENDED">Suspendido</option>
            <option value="VACATION">Vacaciones</option>
        </select>
        <div class="invalid-feedback"></div>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Expediente Completo</b></span>
        <select class="form-control" name="employee[documentation_complete]" id="employee[documentation_complete]">
            <option value="N">No</option>
            <option value="Y">Sí</option>
        </select>
    </div>
    <div class="input-group input-group-sm mb-1">
        <span class="input-group-text col-5"><b>Registro Activo</b></span>
        <select class="form-control" name="employee[active]" id="employee[active]">
            <option value="Y">Sí</option>
            <option value="N">No</option>
        </select>
    </div>
</div>
