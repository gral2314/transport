<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $kpis */

use yii\helpers\Html;
use yii\helpers\Json;

$moduleConfig = [
    'config' => $config,
    'kpis' => $kpis,
];
?>
<div class="doc-tire-index container-fluid">
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body p-3">
                    <small class="text-white-50">Total</small>
                    <h2 class="mb-0"><?= (int) ($kpis['total'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body p-3">
                    <small class="text-white-50">Abiertos</small>
                    <h2 class="mb-0"><?= (int) ($kpis['open'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body p-3">
                    <small class="text-white-50">Cerrados</small>
                    <h2 class="mb-0"><?= (int) ($kpis['closed'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body p-3">
                    <small class="text-white-50">Cancelados</small>
                    <h2 class="mb-0"><?= (int) ($kpis['canceled'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 " style="min-height: 550px; overflow: visible;">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h3 class="mb-1"><?= Html::encode((string) ($config['title'] ?? 'Documentos')) ?></h3>
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
                    <button type="button" class="btn btn-success btn-sm" id="doc-tire-create">
                        <i class="fa-solid fa-circle-plus"></i> Nuevo documento
                    </button>
                </div>
            </div>

            <div class="table-responsive" style="overflow: visible;">
                <table class="table table-sm table-hover align-middle" id="doc-tire-table">
                    <thead class="table-light">
                        <tr>
                            <?php foreach (($config['listColumns'] ?? []) as $column): ?>
                                <th><?= Html::encode((string) ($column['label'] ?? $column['field'] ?? '')) ?></th>
                            <?php endforeach; ?>
                            <th class="text-center m-0 p-0" style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="<?= count($config['listColumns'] ?? []) + 1 ?>" class="text-center text-muted py-4">Cargando documentos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
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

<script>
window.DocTireModuleConfig = <?= Json::htmlEncode($moduleConfig) ?>;
</script>