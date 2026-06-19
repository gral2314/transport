<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $kpis */

use app\assets\FullCalendarAsset;
use yii\helpers\Html;
use yii\helpers\Json;

$moduleConfig = [
    'config' => $config,
    'kpis'   => $kpis,
];
?>
<div class="doc-tire-index container-fluid">
    <!-- KPIs Row -->
    <div class="row g-2 mb-3">
        <div class="col-md">
            <div class="card bg-primary text-white h-100">
                <div class="card-body p-3">
                    <small class="text-white-50">Total</small>
                    <h2 class="mb-0"><?= (int) ($kpis['total'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="card bg-success text-white h-100">
                <div class="card-body p-3">
                    <small class="text-white-50">Abiertos</small>
                    <h2 class="mb-0"><?= (int) ($kpis['open'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="card bg-warning text-white h-100">
                <div class="card-body p-3">
                    <small class="text-dark">Activos</small>
                    <h2 class="mb-0"><?= (int) ($kpis['active'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="card bg-info text-white h-100">
                <div class="card-body p-3">
                    <small class="text-white-50">Cerrados</small>
                    <h2 class="mb-0"><?= (int) ($kpis['closed'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="card bg-danger text-white h-100">
                <div class="card-body p-3">
                    <small class="text-white-50">Cancelados</small>
                    <h2 class="mb-0"><?= (int) ($kpis['canceled'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Title Bar with Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="mb-1"><?= Html::encode((string) ($config['title'] ?? 'Documentos')) ?></h3>
            <div class="text-muted">Listado operativo con acceso a captura, consulta rapida y edicion.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="min-width: 260px;">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" class="form-control" id="doc-tire-search" placeholder="Buscar por folio, comentarios o referencia">
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="doc-tire-refresh">
                <i class="fa-solid fa-rotate"></i> Actualizar
            </button>
            <button type="button" class="btn btn-success btn-sm" id="doc-tire-create">
                <i class="fa-solid fa-circle-plus"></i> Nuevo documento
            </button>
            <button type="button" class="btn btn-outline-info btn-sm" id="doc-tire-toggle-calendar">
                <i class="fa-solid fa-calendar"></i> Calendario
            </button>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">

            <!-- Status Filter Toggles & Calendar -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="btn-group btn-group-sm" id="doc-tire-status-filters" role="group">
                    <button type="button" class="btn btn-outline-secondary active" data-status="">Todos</button>
                    <button type="button" class="btn btn-outline-info" data-status="PLAN">Planeados</button>
                    <button type="button" class="btn btn-outline-secondary" data-status="LIB">Liberados</button>
                    <button type="button" class="btn btn-outline-dark" data-status="TALLER">Taller</button>
                    <button type="button" class="btn btn-outline-primary" data-status="EXEC">Ejecutados</button>
                    <button type="button" class="btn btn-outline-warning" data-status="VAL">Validados</button>
                    <button type="button" class="btn btn-outline-success" data-status="CLOSE">Cerrados</button>
                    <button type="button" class="btn btn-outline-danger" data-status="CANCELLED">Cancelados</button>
                </div>
            </div>

            <!-- Calendar Container -->
            <?php require_once('_calendar.php'); ?>

            <!-- Table Container -->
            <div id="doc-tire-table-container" class="table-responsive" style="overflow:visible;">
                <table class="table table-sm table-hover align-middle" id="doc-tire-table">
                    <thead class="table-light">
                        <tr>
                            <?php foreach (($config['listColumns'] ?? []) as $column): ?>
                                <th><?= Html::encode((string) ($column['label'] ?? $column['field'] ?? '')) ?></th>
                            <?php endforeach; ?>
                            <th class="text-center m-0 p-0" style="width:150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="<?= count($config['listColumns'] ?? []) + 1 ?>" class="text-center text-muted py-4">Cargando documentos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="doc-tire-pagination" class="d-flex justify-content-center mt-3"></div>
        </div>
    </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="doc-tire-quick-view-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title">Vista rapida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="doc-tire-quick-view-body"></div>
        </div>
    </div>
</div>

<!-- Send Mail Modal -->
<div class="modal fade" id="doc-tire-send-mail-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title">Enviar documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="doc-tire-send-mail-body"></div>
        </div>
    </div>
</div>

<!-- KPI Modal -->
<div class="modal fade" id="doc-tire-kpi-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title">Detalle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="doc-tire-kpi-modal-body"></div>
        </div>
    </div>
</div>

<!-- Workflow Action Modal -->
<div class="modal fade" id="mdl-mnt-workflow" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="mdl-mnt-workflow-title">Transición de estado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mdl-mnt-wf-docentry">
                <input type="hidden" id="mdl-mnt-wf-action">
                <!-- Info header -->
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="border rounded p-2 bg-light">
                            <small class="text-muted">Folio</small>
                            <div class="fw-semibold" id="mdl-mnt-wf-docnum">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-2 bg-light">
                            <small class="text-muted" id="mdl-mnt-wf-from-label">De</small>
                            <div class="fw-semibold" id="mdl-mnt-wf-from-status">—</div>
                        </div>
                    </div>
                </div>
                <!-- Optional fields per action -->
                <div id="mdl-mnt-wf-tech-row" class="row g-2 mb-3 d-none">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Técnico asignado</label>
                        <select class="form-select form-select-sm" id="mdl-mnt-wf-technician">
                            <option value="">Seleccionar técnico...</option>
                        </select>
                    </div>
                </div>
                <div id="mdl-mnt-wf-validator-row" class="row g-2 mb-3 d-none">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Validado por</label>
                        <select class="form-select form-select-sm" id="mdl-mnt-wf-validator">
                            <option value="">Seleccionar validador...</option>
                        </select>
                    </div>
                </div>
                <div id="mdl-mnt-wf-rejection-row" class="row g-2 mb-3 d-none">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Motivo de rechazo</label>
                        <textarea class="form-control form-control-sm" id="mdl-mnt-wf-rejection-notes" rows="2" placeholder="Describa el motivo del rechazo..."></textarea>
                    </div>
                </div>
                <!-- Comments -->
                <div class="row g-2 mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Comentario</label>
                        <textarea class="form-control form-control-sm" id="mdl-mnt-wf-comments" rows="2" placeholder="Comentario opcional..."></textarea>
                    </div>
                </div>
                <!-- Timeline -->
                <div id="mdl-mnt-wf-timeline" class="d-none">
                    <h6 class="fw-semibold border-bottom pb-2">Bitácora de estados</h6>
                    <div class="timeline" id="mdl-mnt-wf-timeline-body"></div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm" id="mdl-mnt-wf-confirm" disabled>
                    <i class="fa-solid fa-check"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Ensure dropdown menus render above table-responsive overflow */
#doc-tire-table-container {
    overflow: visible;
}
#doc-tire-table-container .dropdown-menu {
    z-index: 1050;
}
</style>

<script>
window.DocTireModuleConfig = <?= Json::htmlEncode($moduleConfig) ?>;
</script>