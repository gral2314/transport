<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $kpis */

use yii\helpers\Html;

$this->registerCss(<<<CSS
/* Evitar que los dropdowns dentro de la tabla se recorten */
.doc-tire-index .table-responsive td .dropdown {
    position: static;
}
.doc-tire-index .table-responsive td .dropdown-menu {
    z-index: 1050;
    position: absolute !important;
}
CSS);

?>
<div class="doc-tire-index container-fluid">

    <div class="row">
        <div class="col-md-3 col-sd-6">
            <div class="card bg-primary text-white kpi-card">
                <div class="card-body p-1">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-auto">
                            <i class="fa-solid fa-folder-open fa-2x"></i>
                        </div>
                        <div class="col">
                            <h2 class="text-white f-w-300" id="kpi-total-tires"><?= (int) ($kpis['total'] ?? 0) ?></h2>
                            <h5 class="text-white">Total</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sd-6">
            <div class="card bg-warning text-white kpi-card">
                <div class="card-body p-1">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-auto">
                            <i class="fa-solid fa-unlock fa-2x"></i>
                        </div>
                        <div class="col">
                            <h2 class="text-white f-w-300" id="kpi-total-tires"><?= (int) ($kpis['open'] ?? 0) ?></h2>
                            <h5 class="text-white">Abiertos</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sd-6">
            <div class="card bg-info text-white kpi-card">
                <div class="card-body p-1">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-auto">
                            <i class="fa-solid fa-lock fa-2x"></i>
                        </div>
                        <div class="col">
                            <h2 class="text-white f-w-300" id="kpi-total-tires"><?= (int) ($kpis['closed'] ?? 0) ?></h2>
                            <h5 class="text-white">Cerrados</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sd-6">
            <div class="card bg-danger text-white kpi-card">
                <div class="card-body p-1">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-auto">
                            <i class="fa-solid fa-ban fa-2x"></i>
                        </div>
                        <div class="col">
                            <h2 class="text-white f-w-300" id="kpi-total-tires"><?= (int) ($kpis['canceled'] ?? 0) ?></h2>
                            <h5 class="text-white">Cancelados</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="mb-1">Asignacion de LLantas</h3>
            <div class="text-muted">Listado operativo con acceso a captura, consulta rapida, impresion y distribucion.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="min-width: 300px;">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" class="form-control" id="doc-tire-search" placeholder="Buscar por folio, comentarios o referencia">
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="doc-tire-refresh">
                <i class="fa-solid fa-rotate"></i> Actualizar
            </button>
            <button type="button" class="btn btn-success btn-sm" id="doc-tire-create" onclick="window.location.href='<?= \yii\helpers\Url::to(['doc-tire-assignment/create']) ?>'">
                <i class="fa-solid fa-circle-plus"></i> Nuevo documento
            </button>
            <button type="button" class="btn btn-outline-info btn-sm" id="doc-tire-toggle-calendar">
                <i class="fa-solid fa-calendar-alt"></i> <span id="doc-tire-toggle-label">Calendario</span>
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm" id="doc-tire-table-card">
        <div class="card-body p-3" style="min-height: 550px; overflow: visible;">

            <!-- Status Toggle Filters -->
            <div class="d-flex flex-wrap gap-2 mb-3" id="doc-tire-status-toggles">
                <button type="button" class="btn btn-sm btn-outline-secondary active" data-status="">
                    <i class="fa-solid fa-list"></i> Todos
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" data-status="PLANNED">
                    <i class="fa-solid fa-clipboard-list"></i> Planeadas
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" data-status="RELEASED">
                    <i class="fa-solid fa-paper-plane"></i> Liberadas
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning" data-status="IN_PROGRESS">
                    <i class="fa-solid fa-wrench"></i> En Proceso
                </button>
                <button type="button" class="btn btn-sm btn-outline-dark" data-status="PENDING_VALIDATION">
                    <i class="fa-solid fa-check-double"></i> Pendientes Validar
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" data-status="CLOSED">
                    <i class="fa-solid fa-check-circle"></i> Cerradas
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-status="CANCELLED">
                    <i class="fa-solid fa-ban"></i> Canceladas
                </button>
            </div>

            <div class="table-responsive" style="overflow: visible;">
                <table class="table table-sm table-hover align-middle" id="doc-tire-table">
                    <thead class="table-light">
                        <tr>
                            <?php foreach ($config['listColumns'] ?? [] as $col): ?>
                                <th><?= Html::encode($col['label'] ?? '') ?></th>
                            <?php endforeach; ?>
                            <th class="text-center m-0 p-0" style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="<?= (count($config['listColumns'] ?? []) + 1) ?>" class="text-center text-muted py-4">
                                Cargando...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="doc-tire-pagination" class="mt-3"></div>
        </div>
    </div>
</div>

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

<!-- Calendar View Partial -->
<?php require_once(__DIR__ . '/_calendar.php') ?>

<script>
window.DocTireUrls = {
    list: '<?= \yii\helpers\Url::to(['doc-tire-assignment/list']) ?>',
    get: '<?= \yii\helpers\Url::to(['doc-tire-assignment/get']) ?>',
    create: '<?= \yii\helpers\Url::to(['doc-tire-assignment/create']) ?>',
    updateBase: '<?= \yii\helpers\Url::to(['doc-tire-assignment/update']) ?>',
    save: '<?= \yii\helpers\Url::to(['doc-tire-assignment/save']) ?>',
    quickViewBase: '<?= \yii\helpers\Url::to(['doc-tire-assignment/quick-view']) ?>',
    previewBase: '<?= \yii\helpers\Url::to(['doc-tire-assignment/preview']) ?>',
    pdfBase: '<?= \yii\helpers\Url::to(['doc-tire-assignment/pdf']) ?>',
    printBase: '<?= \yii\helpers\Url::to(['doc-tire-assignment/print']) ?>',
    sendMailBase: '<?= \yii\helpers\Url::to(['doc-tire-assignment/send-mail']) ?>',
    close: '<?= \yii\helpers\Url::to(['doc-tire-assignment/close']) ?>',
    cancel: '<?= \yii\helpers\Url::to(['doc-tire-assignment/cancel']) ?>',
    release: '<?= \yii\helpers\Url::to(['doc-tire-assignment/release']) ?>',
    getFormOptions: '<?= \yii\helpers\Url::to(['doc-tire-assignment/get-form-options']) ?>',
    getNextNumber: '<?= \yii\helpers\Url::to(['series/get-next-number']) ?>',
    peekNextNumber: '<?= \yii\helpers\Url::to(['series/peek-next-number']) ?>',
    vehicleLayout: '<?= \yii\helpers\Url::to(['doc-tire-assignment/vehicle-layout']) ?>'
};
</script>