<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $document */
/** @var array $formOptions */
/** @var bool $isNewRecord */

use yii\helpers\Html;
use yii\helpers\Json;

$resolveOptions = static function (array $field, array $formOptions): array {
    if (isset($field['options']) && is_array($field['options'])) {
        return $field['options'];
    }

    if (!isset($field['optionsFrom'])) {
        return [];
    }

    $source = $formOptions[$field['optionsFrom']] ?? [];
    if (!is_array($source)) {
        return [];
    }

    if ($source === []) {
        return [];
    }

    $first = reset($source);
    if (is_array($first)) {
        $mapped = [];
        foreach ($source as $row) {
            if (!is_array($row)) {
                continue;
            }
            $value = $row['code'] ?? $row['id'] ?? $row['value'] ?? null;
            $label = $row['name'] ?? $row['label'] ?? $value;
            if ($value === null) {
                continue;
            }
            $mapped[(string) $value] = (string) $label;
        }

        return $mapped;
    }

    return $source;
};

$renderOptions = static function (array $options, mixed $selected): string {
    $html = '<option value="">Seleccionar...</option>';
    foreach ($options as $value => $label) {
        $html .= Html::tag('option', Html::encode((string) $label), [
            'value' => (string) $value,
            'selected' => ((string) $selected === (string) $value),
        ]);
    }

    return $html;
};

$moduleConfig = [
    'config' => $config,
    'document' => $document,
    'formOptions' => $formOptions,
    'isNewRecord' => $isNewRecord,
];
?>
<div class="doc-tire-form container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="mb-1"><?= Html::encode((string) ($config['titleSingular'] ?? 'Documento')) ?></h3>
            <div class="text-muted">Captura encabezado, lineas operativas, evidencias y resumen antes de cerrar o cancelar.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= Html::encode((string) ($config['routes']['index'] ?? '#')) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Regresar
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm" id="doc-tire-open-preview" <?= empty($document['docentry']) ? 'disabled' : '' ?>>
                <i class="fa-solid fa-eye"></i> Preview
            </button>
            <button type="button" class="btn btn-success btn-sm" id="doc-tire-save">
                <i class="fa-solid fa-floppy-disk"></i> Guardar
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form id="doc-tire-form-shell">
                <input type="hidden" name="docentry" id="docentry" value="<?= Html::encode((string) ($document['docentry'] ?? '')) ?>">
                <div class="row g-2">
                    <?php foreach (($config['headerFields'] ?? []) as $field): ?>
                        <?php $value = $document[$field['name']] ?? ''; ?>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold"><?= Html::encode((string) $field['label']) ?></label>
                            <?php if (($field['type'] ?? 'text') === 'select'): ?>
                                <select class="form-select form-select-sm" name="<?= Html::encode((string) $field['name']) ?>" id="<?= Html::encode((string) $field['name']) ?>">
                                    <?= $renderOptions($resolveOptions($field, $formOptions), $value) ?>
                                </select>
                            <?php else: ?>
                                <input
                                    type="<?= Html::encode((string) ($field['type'] ?? 'text')) ?>"
                                    class="form-control form-control-sm"
                                    name="<?= Html::encode((string) $field['name']) ?>"
                                    id="<?= Html::encode((string) $field['name']) ?>"
                                    value="<?= Html::encode((string) $value) ?>"
                                    <?= ($field['type'] ?? '') === 'readonly' ? 'readonly' : '' ?>
                                    <?= isset($field['step']) ? 'step="' . Html::encode((string) $field['step']) . '"' : '' ?>
                                >
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <ul class="nav nav-tabs mt-3" role="tablist">
                    <?php if (!empty($config['vehicleFields'])): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#doc-tab-vehicles" type="button">Unidades</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#doc-tab-details" type="button">Detalles</button>
                        </li>
                    <?php else: ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#doc-tab-details" type="button">Detalles</button>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= empty($config['vehicleFields']) ? '' : '' ?>" data-bs-toggle="tab" data-bs-target="#doc-tab-attachments" type="button">Adjuntos</button>
                    </li>
                </ul>

                <div class="tab-content border border-top-0 p-3 bg-white">
                    <?php if (!empty($config['vehicleFields'])): ?>
                        <div class="tab-pane fade show active" id="doc-tab-vehicles">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0"><?= Html::encode((string) ($config['vehicleSummaryTitle'] ?? 'Unidades')) ?></h6>
                                <button type="button" class="btn btn-outline-success btn-sm" id="add-vehicle-row"><i class="fa-solid fa-plus"></i> Agregar</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle" id="doc-vehicles-table">
                                    <thead class="table-light">
                                        <tr>
                                            <?php foreach (($config['vehicleFields'] ?? []) as $field): ?>
                                                <th><?= Html::encode((string) $field['label']) ?></th>
                                            <?php endforeach; ?>
                                            <th style="width: 60px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="doc-vehicles-body"></tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="tab-pane fade <?= empty($config['vehicleFields']) ? 'show active' : '' ?>" id="doc-tab-details">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Detalle operativo</h6>
                            <button type="button" class="btn btn-outline-success btn-sm" id="add-detail-row"><i class="fa-solid fa-plus"></i> Agregar</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle" id="doc-details-table">
                                <thead class="table-light">
                                    <tr>
                                        <?php foreach (($config['detailFields'] ?? []) as $field): ?>
                                            <th><?= Html::encode((string) $field['label']) ?></th>
                                        <?php endforeach; ?>
                                        <th style="width: 60px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="doc-details-body"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="doc-tab-attachments">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Evidencias y adjuntos</h6>
                            <button type="button" class="btn btn-outline-success btn-sm" id="add-attachment-row"><i class="fa-solid fa-plus"></i> Agregar</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle" id="doc-attachments-table">
                                <thead class="table-light">
                                    <tr>
                                        <?php foreach (($config['attachmentFields'] ?? []) as $field): ?>
                                            <th><?= Html::encode((string) $field['label']) ?></th>
                                        <?php endforeach; ?>
                                        <th style="width: 60px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="doc-attachments-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-lg-8">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <label class="form-label fw-semibold">Comentarios generales</label>
                                <textarea class="form-control" rows="4" name="comments" id="comments"><?= Html::encode((string) ($document['comments'] ?? '')) ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6 class="fw-semibold">Resumen</h6>
                                <div class="d-flex justify-content-between small mb-1"><span>Lineas detalle</span><strong id="summary-detail-count">0</strong></div>
                                <div class="d-flex justify-content-between small mb-1"><span>Adjuntos</span><strong id="summary-attachment-count">0</strong></div>
                                <?php if (!empty($config['vehicleFields'])): ?>
                                    <div class="d-flex justify-content-between small mb-1"><span>Unidades</span><strong id="summary-vehicle-count">0</strong></div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between small"><span>Estado actual</span><strong><?= Html::encode((string) ($document['status'] ?? 'PLANNED')) ?></strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.DocTireFormConfig = <?= Json::htmlEncode($moduleConfig) ?>;
</script>