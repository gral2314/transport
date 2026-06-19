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
    <h6 class="mb-0">Evidencias y adjuntos</h6>
    <button type="button" class="btn btn-outline-success btn-sm" id="add-attachment-row">
        <i class="fa-solid fa-plus"></i> Agregar
    </button>
</div>

<div class="table-responsive">
    <table class="table table-sm align-middle" id="doc-attachments-table">
        <thead class="table-light">
            <tr>
                <?php foreach (($config['attachmentFields'] ?? []) as $field): ?>
                    <th><?= Html::encode($field['label']) ?></th>
                <?php endforeach; ?>
                <th style="width: 60px;"></th>
            </tr>
        </thead>
        <tbody id="doc-attachments-body">
            <tr>
                <td colspan="<?= count($config['attachmentFields'] ?? []) + 1 ?>"
                    class="text-center text-muted py-4">
                    No hay archivos adjuntos.
                </td>
            </tr>
        </tbody>
    </table>
</div>