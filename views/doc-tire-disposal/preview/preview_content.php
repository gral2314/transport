<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $document */
/** @var bool $autoPrint */
/** @var string $renderMode */

use yii\helpers\Html;
use yii\helpers\Json;

$resolveLabel = static function (array $field, array $row): string {
    $value = $row[$field['name']] ?? '';

    if (isset($field['displayField']) && isset($row[$field['displayField']]) && $row[$field['displayField']] !== '') {
        return (string) $row[$field['displayField']];
    }

    if (isset($field['options']) && is_array($field['options'])) {
        return (string) ($field['options'][$value] ?? $value);
    }

    if (isset($field['lookup']) && is_array($field['lookup'])) {
        return (string) ($field['lookup'][$value] ?? $value);
    }

    return (string) $value;
};

$previewConfig = [
    'config' => $config,
    'document' => ['docentry' => $document['docentry'] ?? null],
    'autoPrint' => $autoPrint,
];
?>
<div class="doc-tire-preview container-fluid <?= $renderMode === 'pdf' ? 'px-0' : '' ?>">
    <?php if ($renderMode === 'html'): ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
            <div>
                <h3 class="mb-1"><?= Html::encode((string) ($config['titleSingular'] ?? 'Documento')) ?></h3>
                <div class="text-muted">Vista imprimible, exportable a PDF y compartible por correo.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= Html::encode((string) ($config['routes']['index'] ?? '#')) ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Regresar</a>
                <a href="<?= Html::encode((string) (($config['routes']['pdfBase'] ?? '#') . '?docentry=' . (int) ($document['docentry'] ?? 0))) ?>" target="_blank" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                <a href="<?= Html::encode((string) (($config['routes']['printBase'] ?? '#') . '?docentry=' . (int) ($document['docentry'] ?? 0))) ?>" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-print"></i> Imprimir</a>
                <button type="button" class="btn btn-success btn-sm" id="preview-send-mail" data-docentry="<?= (int) ($document['docentry'] ?? 0) ?>"><i class="fa-solid fa-paper-plane"></i> Enviar</button>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 border-bottom pb-3 mb-3">
                <div>
                    <div class="text-uppercase text-muted small"><?= Html::encode((string) ($config['code'] ?? 'DOC')) ?></div>
                    <h2 class="mb-1"><?= Html::encode((string) ($document['docnum'] ?? 'Sin folio')) ?></h2>
                    <div class="text-muted">Fecha documento: <?= Html::encode((string) ($document['doc_date'] ?? '')) ?></div>
                    <div class="text-muted">Fecha compromiso: <?= Html::encode((string) ($document['doc_duedate'] ?? '')) ?></div>
                </div>
                <div class="text-end">
                    <div class="badge bg-<?= ($document['canceled'] ?? 'N') === 'Y' ? 'danger' : (($document['doc_status'] ?? 'O') === 'C' ? 'success' : 'warning text-dark') ?> fs-6">
                        <?= ($document['canceled'] ?? 'N') === 'Y' ? 'Cancelado' : (($document['doc_status'] ?? 'O') === 'C' ? 'Cerrado' : 'Abierto') ?>
                    </div>
                    <div class="mt-2 fw-semibold">Estado operativo: <?= Html::encode((string) ($document['status'] ?? 'PLAN')) ?></div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <?php foreach (($config['headerFields'] ?? []) as $field): ?>
                    <?php if ($field['name'] === 'docnum'): ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <div class="col-md-3">
                        <div class="border rounded p-2 h-100">
                            <div class="small text-muted"><?= Html::encode((string) $field['label']) ?></div>
                            <div class="fw-semibold"><?= Html::encode($resolveLabel($field, $document)) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($document['vehicles'])): ?>
                <div class="mb-3">
                    <h5 class="border-bottom pb-2"><?= Html::encode((string) ($config['vehicleSummaryTitle'] ?? 'Unidades involucradas')) ?></h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <?php foreach (($config['vehicleFields'] ?? []) as $field): ?>
                                        <th><?= Html::encode((string) $field['label']) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($document['vehicles'] as $row): ?>
                                    <tr>
                                        <?php foreach (($config['vehicleFields'] ?? []) as $field): ?>
                                            <td><?= Html::encode($resolveLabel($field, $row)) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <h5 class="border-bottom pb-2">Detalle</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <?php foreach (($config['detailFields'] ?? []) as $field): ?>
                                    <th><?= Html::encode((string) $field['label']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($document['details'] ?? []) as $row): ?>
                                <tr>
                                    <?php foreach (($config['detailFields'] ?? []) as $field): ?>
                                        <td><?= Html::encode($resolveLabel($field, $row)) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!empty($document['attachments'])): ?>
                <div class="mb-3">
                    <h5 class="border-bottom pb-2">Adjuntos</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <?php foreach (($config['attachmentFields'] ?? []) as $field): ?>
                                        <th><?= Html::encode((string) $field['label']) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($document['attachments'] as $row): ?>
                                    <tr>
                                        <?php foreach (($config['attachmentFields'] ?? []) as $field): ?>
                                            <td><?= Html::encode($resolveLabel($field, $row)) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div class="border-top pt-3">
                <div class="small text-muted mb-1">Comentarios</div>
                <div><?= nl2br(Html::encode((string) ($document['comments'] ?? 'Sin comentarios.'))) ?></div>
            </div>
        </div>
    </div>
</div>

<?php if ($renderMode === 'html'): ?>
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
    window.DocTirePreviewConfig = <?= Json::htmlEncode($previewConfig) ?>;
    </script>
<?php endif; ?>