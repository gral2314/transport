<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */

use app\assets\FullCalendarAsset;

FullCalendarAsset::register($this);
$providerOptions = $config['providerOptions'] ?? [];
?>
<div id="doc-tire-calendar-container" style="display:none;">
    <div class="row mb-2">
        <div class="col-md-3">
            <select id="doc-tire-calendar-provider-filter" class="form-select form-select-sm">
                <option value="">Todos los proveedores</option>
                <?php foreach ($providerOptions as $code => $name): ?>
                    <option value="<?= \yii\helpers\Html::encode($code) ?>"><?= \yii\helpers\Html::encode($name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-9 d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="doc-tire-calendar-today">
                <i class="fa-solid fa-calendar-day"></i> Hoy
            </button>
        </div>
    </div>
    <div id="doc-tire-calendar"></div>
    <div id="doc-tire-calendar-tooltip" class="d-none" style="position:absolute;z-index:1070;"></div>
</div>
