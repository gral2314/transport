<?php

use app\components\widgets\DataTableWidget;
use app\assets\DynamicAssetBundle;
use yii\helpers\Url;
use yii\helpers\Html;

// Registrar DynamicAssetBundle para auto-cargar JS
//DynamicAssetBundle::register($this);

// CrudWidget solo para la tabla (sin modal)
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="fw-bold text-secondary small text-uppercase tracking-wider">Unidades involucradas</span>
    <button type="button" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1"
        id="add-vehicle-row" data-bs-toggle="modal" data-bs-target="#mdl-units"><i class="fa-solid fa-plus"></i> Agregar
        Unidad</button>

</div>
<div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0" id="doc-vehicles-table">
        <thead class="table-light text-secondary">
            <tr>
                <th>Unidad</th>
                <th>Odómetro</th>
                <th>Comentarios</th>
                <th style="width: 60px;"></th>
            </tr>
        </thead>
        <tbody id="doc-vehicles-body">
            <tr>
                <td colspan="4" class="text-center text-muted py-4">Sin registros añadidos.</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal de unidades -->
<div id="mdl-units" class="modal fade modal-lg" tabindex="-1" aria-labelledby="mdl-units" style="display: none;"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdl-units">Unidades activas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover table-sm table-responsive-sm col-12"
                        id="mdl-tbl-units" aria-describedby="Catalogo de Unidades"></table>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary">Seleccionar</button>
            </div>
        </div>
    </div>
</div>