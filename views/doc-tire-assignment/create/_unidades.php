<?php

declare(strict_types=1);

/**
 * @var yii\web\View $this
 * @var array $config
 * @var array $document
 * @var array $formOptions
 * @var bool $isNewRecord
 */

use yii\helpers\Html;
?>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0"><?= Html::encode($config['vehicleSummaryTitle'] ?? 'Unidades') ?></h6>
    <button type="button" class="btn btn-outline-success btn-sm" id="add-vehicle-row"
            title="Agregar unidad al documento">
        <i class="fa-solid fa-plus"></i> Agregar Unidad
    </button>
</div>

<div class="table-responsive">
    <table class="table table-sm align-middle" id="doc-vehicles-table">
        <thead class="table-light">
            <tr>
                <th>Unidad</th>
                <th>Odómetro</th>
                <th>Comentarios</th>
                <th style="width: 80px;">Acciones</th>
            </tr>
        </thead>
        <tbody id="doc-vehicles-body">
            <tr>
                <td colspan="4" class="text-center text-muted py-4">
                    No hay unidades vinculadas. Haga clic en <strong>"Agregar Unidad"</strong> para seleccionar.
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal de selección de unidades -->
<div id="mdl-units" class="modal fade modal-lg" tabindex="-1"
     aria-labelledby="mdl-units-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdl-units-label">Seleccionar unidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover table-sm w-100"
                           id="mdl-tbl-units"
                           aria-describedby="Catálogo de unidades">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Placas</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody id="mdl-tbl-units-body">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    Cargando unidades...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center">
                <div>
                    <span id="mdl-unit-counter" class="small text-muted">0 / 2 unidades</span>
                    <span id="mdl-unit-limit-msg" class="small text-danger ms-2" style="display:none;">
                        <i class="fa-solid fa-info-circle"></i> Máximo 2 unidades
                    </span>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btn-vincular-unidades">Seleccionar</button>
                </div>
            </div>
        </div>
    </div>
</div>