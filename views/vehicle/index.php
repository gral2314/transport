<?php
/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;

$this->title = 'Gestión de Unidades';

?>
<div class="vehicle-index container-fluid">

  <div class="row mb-3">
    <div class="col-3">
      <div class="card bg-primary text-white kpi-card">
        <div class="card-body p-1">
          <div class="row align-items-center justify-content-center">
            <div class="col-auto">
              <img src="../assets/images/widget/shape5.png" alt="kpi-1" style="height:36px">
            </div>
            <div class="col">
              <h2 class="text-white f-w-300" id="kpi-total-units">0</h2>
              <h5 class="text-white">Total unidades</h5>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-3">
      <div class="card bg-success text-white kpi-card">
        <div class="card-body p-1">
          <div class="row align-items-center justify-content-center">
            <div class="col-auto">
              <img src="../assets/images/widget/shape5.png" alt="kpi-2" style="height:36px">
            </div>
            <div class="col">
              <h2 class="text-white f-w-300" id="kpi-available">0</h2>
              <h5 class="text-white">Unidades disponibles</h5>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-3">
      <div class="card bg-warning text-white kpi-card">
        <div class="card-body p-1">
          <div class="row align-items-center justify-content-center">
            <div class="col-auto">
              <img src="../assets/images/widget/shape5.png" alt="kpi-3" style="height:36px">
            </div>
            <div class="col">
              <h2 class="text-white f-w-300" id="kpi-maintenance">0</h2>
              <h5 class="text-white">En mantenimiento</h5>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-3">
      <div class="card bg-info text-white kpi-card">
        <div class="card-body p-1">
          <div class="row align-items-center justify-content-center">
            <div class="col-auto">
              <img src="../assets/images/widget/shape5.png" alt="kpi-4" style="height:36px">
            </div>
            <div class="col">
              <h2 class="text-white f-w-300" id="kpi-expiring">0</h2>
              <h5 class="text-white">Documentos por vencer (30d)</h5>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-2">
    <div class="col">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <?= Html::button('Agregar Unidad', ['class' => 'btn btn-success', 'id' => 'btn-add-unit', 'data-bs-toggle' => 'modal']) ?>
        </div>
        <div>
          <!-- espacio para filtros futuros -->
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <table class="table table-striped" id="units-table">
        <thead>
          <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Placa</th>
            <th>Estado</th>
            <th>Disponible</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <!-- datos cargados por JS -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal: Unidad -->
  <div class="modal fade" id="unit-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="unitModalLabel">Crear Unidad</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <form id="unit-form" class="needs-validation" novalidate>
            <input type="hidden" id="vehicle_vehicle_code" name="vehicle[vehicle_code]" value="">

            <ul class="nav nav-pills mb-3" id="unit-tabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-general" data-bs-toggle="pill" data-bs-target="#pill-general" type="button" role="tab">General</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-docs" data-bs-toggle="pill" data-bs-target="#pill-docs" type="button" role="tab">Documentos</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-tires" data-bs-toggle="pill" data-bs-target="#pill-tires" type="button" role="tab">Llantas</button>
              </li>
            </ul>

            <div class="tab-content">
              <div class="tab-pane fade show active" id="pill-general" role="tabpanel">
                <div class="row">
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label">Código</label>
                      <input type="text" class="form-control" id="vehicle_code" name="vehicle[vehicle_code]" required>
                      <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Nombre</label>
                      <input type="text" class="form-control" id="vehicle_name" name="vehicle[vehicle_name]" required>
                      <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Tipo</label>
                      <select class="form-select" id="vehicle_type_code" name="vehicle[vehicle_type_code]" required>
                        <option value="">-- seleccionar --</option>
                      </select>
                      <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Marca</label>
                      <select class="form-select" id="brand_code" name="vehicle[brand_code]"><option value="">-- seleccionar --</option></select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label">Placa</label>
                      <input type="text" class="form-control" id="plate_no" name="vehicle[plate_no]">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Econ. No</label>
                      <input type="text" class="form-control" id="economic_no" name="vehicle[economic_no]">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Año</label>
                      <input type="number" class="form-control" id="unit_year" name="vehicle[unit_year]" min="1900" max="2100">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Tipo adquisición</label>
                      <select class="form-select" id="acquisition" name="vehicle[acquisition]">
                        <option value="P">Comprada</option>
                        <option value="R">Rentada</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label">Disponible</label>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="available" name="vehicle[available]" value="Y" checked>
                        <label class="form-check-label" for="available">Sí</label>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Estado</label>
                      <select class="form-select" id="status" name="vehicle[status]">
                        <option value="A">Activo</option>
                        <option value="I">Inactivo</option>
                        <option value="M">Mantenimiento</option>
                        <option value="O">Fuera de servicio</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Notas</label>
                      <textarea class="form-control" id="notes" name="vehicle[notes]" rows="4"></textarea>
                    </div>
                  </div>
                </div>
              </div>

              <div class="tab-pane fade" id="pill-docs" role="tabpanel">
                <div class="mb-2">
                  <button type="button" id="btn-add-doc" class="btn btn-sm btn-secondary">Agregar documento</button>
                </div>
                <table class="table table-sm table-bordered" id="docs-table">
                  <thead><tr><th>#</th><th>Tipo</th><th>No</th><th>Emisión</th><th>Vencimiento</th><th>Acciones</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>

              <div class="tab-pane fade" id="pill-tires" role="tabpanel">
                <div class="mb-2">
                  <button type="button" id="btn-add-tire" class="btn btn-sm btn-secondary">Agregar llanta</button>
                </div>
                <table class="table table-sm table-bordered" id="tires-table">
                  <thead><tr><th>#</th><th>Llanta</th><th>Eje</th><th>Posición</th><th>Instalación</th><th>KM</th><th>Acciones</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>

            <div class="mt-3 text-end">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" id="btn-save-unit" class="btn btn-primary">Guardar</button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>

</div>

<?php
$cfg = [
    'list' => Url::to(['vehicle/list']),
    'get'  => Url::to(['vehicle/get']),
    'save' => Url::to(['vehicle/save']),
    'getFormOptions' => Url::to(['vehicle/get-form-options']),
    'saveDocument' => Url::to(['vehicle/save-document']),
    'deleteDocument' => Url::to(['vehicle/delete-document']),
    'saveTire' => Url::to(['vehicle/save-tire-line']),
    'deleteTire' => Url::to(['vehicle/delete-tire-line']),
];
$this->registerJs('window.fleetUnitsConfig = ' . Json::htmlEncode($cfg) . ';', \yii\web\View::POS_HEAD);
$this->registerCss('
.kpi-card { cursor: default; }
');
