<?php

declare(strict_types=1);

/**
 * @var yii\web\View $this
 * @var array $config
 * @var array $document
 * @var array $formOptions
 * @var bool $isNewRecord
 */


use app\assets\AdmindeskAsset;
//use app\assets\DynamicAssetBundle;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;


$this->title = ($isNewRecord ? 'Nueva ' : 'Editar ') . ($config['titleSingular'] ?? 'Asignacion');

//DynamicAssetBundle::register($this);

// Resolver opciones de campos desde formOptions
$resolveOptions = static function (array $field, array $formOptions): array {
    if (isset($field['options']) && is_array($field['options'])) {
        return $field['options'];
    }
    if (!isset($field['optionsFrom'])) {
        return [];
    }
    $source = $formOptions[$field['optionsFrom']] ?? [];
    if (!is_array($source) || $source === []) {
        return [];
    }
    $first = reset($source);
    if (is_array($first)) {
        $mapped = [];
        $optFields = $field['optionFields'] ?? ['code', 'name'];
        $valueKey = $optFields[0] ?? 'code';
        $labelKeys = array_slice($optFields, 1);
        foreach ($source as $row) {
            if (!is_array($row)) continue;
            $value = $row[$valueKey] ?? $row['code'] ?? $row['id'] ?? $row['value'] ?? null;
            if ($value === null) continue;
            if (!empty($labelKeys)) {
                $parts = [];
                foreach ($labelKeys as $k) {
                    if (isset($row[$k]) && $row[$k] !== '') {
                        $parts[] = $row[$k];
                    }
                }
                $label = !empty($parts) ? implode(' ', $parts) : ($row['name'] ?? $row['label'] ?? $value);
            } else {
                $label = $row['name'] ?? $row['label'] ?? $value;
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
            'value'    => (string) $value,
            'selected' => ((string) $selected === (string) $value),
        ]);
    }
    return $html;
};

$moduleConfig = [
    'config'      => $config,
    'document'    => $document,
    'formOptions' => $formOptions,
    'isNewRecord' => $isNewRecord,
    'docentry'    => $document['docentry'] ?? null,
    'urls'        => $config['routes'] ?? [],
];

foreach (($config['headerFields'] ?? []) as $field):
    $value = $document[$field['name']] ?? ''; 
    if($field['name'] == 'docnum'){ 
        $docnum = $value;
        break;
    }
endforeach;

?>
<style>
    /* ===== VARIABLES CSS GLOBALES ===== */
    :root {
        --tyre-bg-empty: #f0f0f0;
        --tyre-border-empty: #999999;
        --tyre-border-mounted: #1a7a3a;
        --tyre-border-staging: #ffc107;
    }

    /* ===== ZONA DE DROP EN EL CHASIS (Corregido) ===== */
    .tyre-drop-zone {
        width: 45px;
        height: 85px;
        border-radius: 20px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        cursor: grab;
        position: relative;
        overflow: hidden;
        
        /* ESTADO BASE: VACÍO Y LIMPIO */
        background-color: var(--tyre-bg-empty);
        border: 2px dashed var(--tyre-border-empty);
        box-shadow: none;
    }

    /* Overlay base (solo visible cuando hay llanta) */
    .tyre-drop-zone::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 1;
        background: rgba(0, 0, 0, 0); /* Transparente por defecto */
        transition: background 0.2s ease;
        pointer-events: none;
    }

    /* Asegurar que el texto siempre esté sobre el overlay */
    .tyre-drop-zone > div, 
    .tyre-drop-zone > span {
        position: relative;
        z-index: 2;
        text-align: center;
        width: 100%;
    }

    /* --- ESTADO VACÍO EXPLÍCITO --- */
    .tyre-drop-zone[data-has-tire="false"] {
        background-image: none !important; /* SIN IMAGEN CUANDO ESTÁ VACÍO */
        background-color: var(--tyre-bg-empty);
        border-color: var(--tyre-border-empty);
        border-style: dashed;
    }

    .tyre-drop-zone[data-has-tire="false"]:hover {
        background-color: rgba(13, 110, 253, 0.15);
        transform: scale(1.05);
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.3);
    }

    /* Texto en estado vacío */
    .tyre-drop-zone[data-has-tire="false"] .zone-label {
        color: #6c757d;
        font-size: 7px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .tyre-drop-zone[data-has-tire="false"] .zone-code {
        color: #adb5bd;
        font-size: 11px;
        font-weight: 600;
    }

    /* --- ESTADO OCUPADO (Con Imagen Realista) --- */
    .tyre-drop-zone[data-has-tire="true"] {
        /* AQUÍ SÍ CARGAMOS LA IMAGEN */
        background-image: url('<?= yii::getAlias('@web') ?>/images/tire_backgrond_80.png');
        background-size: 100% 100%; /* Forzar ajuste exacto al contenedor 65x85 */
        background-position: center;
        background-repeat: no-repeat;
        
        border: 2px solid var(--tyre-border-mounted);
        box-shadow: inset 0 0 15px rgba(0,0,0,0.4), 0 4px 6px rgba(0,0,0,0.15);
    }

    /* Overlay oscuro SOLO cuando hay llanta para legibilidad */
    .tyre-drop-zone[data-has-tire="true"]::before {
        background: rgba(0, 0, 0, 0.55);
    }

    .tyre-drop-zone[data-has-tire="true"]:hover::before {
        background: rgba(0, 0, 0, 0.45); /* Se aclara al hover */
    }

    /* Texto en estado ocupado (Blanco brillante) */
    .tyre-drop-zone[data-has-tire="true"] .zone-label {
        color: rgba(255, 255, 255, 0.7);
        font-size: 7px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.8);
    }

    .tyre-drop-zone[data-has-tire="true"] .zone-code {
        color: #ffffff;
        font-size: 12px;
        font-weight: 800;
        text-shadow: 0 2px 4px rgba(0,0,0,0.9);
        letter-spacing: 0.5px;
        line-height: 1.1;
    }

    .tyre-drop-zone[data-has-tire="true"] .zone-size {
        color: rgba(255, 255, 255, 0.8);
        font-size: 8px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.8);
    }

    /* ===== OBJETOS VISUALES DEL ALMACÉN (.tyre-object) ===== */
    /* Misma textura realista que el chasis ocupado para consistencia Enterprise */
    #available-tires-container {
        display: flex;
        flex-wrap: wrap;
        align-content: flex-start;
        gap: 6px;
        padding: 4px;
    }

    .tyre-object {
        width: 45px;
        height: 85px;
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        cursor: grab;
        position: relative;
        overflow: hidden;
        user-select: none;
        flex-shrink: 0;

        /* Misma imagen realista que el chasis */
        background-image: url('<?= yii::getAlias('@web') ?>/images/tire_backgrond_80.png');
        background-size: 100% 100%;
        background-position: center;
        background-repeat: no-repeat;

        border: 2px solid var(--tyre-border-mounted, #1a7a3a);
        box-shadow: inset 0 0 15px rgba(0,0,0,0.4), 0 4px 6px rgba(0,0,0,0.15);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Overlay oscuro idéntico al chasis */
    .tyre-object::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 1;
        background: rgba(0, 0, 0, 0.55);
        transition: background 0.2s ease;
        pointer-events: none;
    }

    .tyre-object:hover::before {
        background: rgba(0, 0, 0, 0.45);
    }

    /* Contenido siempre sobre el overlay */
    .tyre-object > * {
        position: relative;
        z-index: 2;
        text-align: center;
        width: 100%;
    }

    /* Texto blanco con sombra (mismo estilo que chasis ocupado) */
    .tyre-object .tyre-object-code {
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        text-shadow: 0 2px 4px rgba(0,0,0,0.9);
        letter-spacing: 0.3px;
        line-height: 1.1;
        display: block;
        padding: 0 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .tyre-object .tyre-object-size {
        color: rgba(255, 255, 255, 0.8);
        font-size: 8px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.8);
        display: block;
        padding: 0 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Estado asignado (oculto completamente) */
    .tyre-object.tire-assigned {
        display: none !important;
    }

    /* Estado filtrado (oculto) */
    .tyre-object.tire-filtered-out {
        display: none;
    }

    /* Dragging ghost */
    .tyre-object.dragging {
        opacity: 0.4;
        transform: scale(0.95);
    }

    /* ===== OBJETOS VISUALES DE STAGING (.staging-tire-object) ===== */
    /* Misma textura realista que .tyre-object pero con borde ámbar de advertencia */
    .staging-tire-object {
        width: 100%;
        min-height: 60px;
        border-radius: 8px;
        /* display: flex; */
        /* flex-direction: row; */
        /* justify-content: space-between; */
        align-items: center;
        padding: 8px 12px;
        position: relative;
        overflow: hidden;
        user-select: none;
        flex-shrink: 0;
        gap: 8px;

        /* Misma imagen realista de fondo */
        background-image: url('<?= yii::getAlias('@web') ?>/images/tire_backgrond_80.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;

        border: 2px solid var(--tyre-border-staging, #ffc107);
        box-shadow: inset 0 0 15px rgba(0,0,0,0.4), 0 4px 6px rgba(0,0,0,0.15);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Overlay oscuro idéntico al chasis */
    .staging-tire-object::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 1;
        background: rgba(0, 0, 0, 0.55);
        transition: background 0.2s ease;
        pointer-events: none;
    }

    .staging-tire-object:hover::before {
        background: rgba(0, 0, 0, 0.45);
    }

    /* Contenido siempre sobre el overlay */
    .staging-tire-object > * {
        position: relative;
        z-index: 2;
    }

    /* Texto blanco con sombra */
    .staging-tire-object strong {
        color: #ffffff;
        font-size: 13px;
        font-weight: 800;
        text-shadow: 0 2px 4px rgba(0,0,0,0.9);
    }

    .staging-tire-object small {
        color: rgba(255, 255, 255, 0.75) !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.8);
    }

    /* Botones de acción en staging */
    .staging-action-group {
        display: flex;
        gap: 4px;
        flex-shrink: 0;
    }

    .staging-action-btn {
        font-size: 11px;
        padding: 2px 8px;
        white-space: nowrap;
    }

    /* Transición de salida */
    .staging-tire-object.fade-out {
        opacity: 0;
        transform: translateX(20px);
    }

    /* Feedback de Validación durante DragOver */
    .tyre-drop-zone.valid-target {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.4) !important;
        transform: scale(1.08);
    }

    .tyre-drop-zone.invalid-target {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.4) !important;
        cursor: not-allowed;
    }

    /* ===== FULLSCREEN MODE ===== */
    #visual-workspace.fullscreen-mode {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 1050 !important;
        background: #fff !important;
        padding: 15px !important;
        overflow: auto !important;
        margin: 0 !important;
    }

    #visual-workspace.fullscreen-mode .visual-panel-left,
    #visual-workspace.fullscreen-mode .visual-panel-right {
        min-height: calc(100vh - 100px);
    }

    #visual-workspace.fullscreen-mode .visual-panel-center {
        min-height: calc(100vh - 100px);
    }

    #visual-workspace.fullscreen-mode #dynamic-truck-container {
        min-height: calc(100vh - 250px) !important;
    }

    /* Asegurar que el botón de cierre de fullscreen se vea bien */
    #visual-workspace.fullscreen-mode #btn-toggle-fullscreen {
        position: sticky;
        top: 0;
        z-index: 1060;
    }
</style>

<!-- ===== OVERLAY DE CARGA ===== -->
<div id="doc-tire-loading-overlay" style="display:block; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.55); z-index:9999; display:none; align-items:center; justify-content:center; flex-direction:column;">
    <div class="bg-white rounded-3 shadow-lg p-4 text-center" style="min-width:200px;">
        <div class="spinner-border text-primary mb-2" role="status" style="width:3rem;height:3rem;">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <div class="fw-semibold text-dark" id="doc-tire-loading-text">Cargando...</div>
    </div>
</div>

<div class="doc-tire-form container-fluid">

    <!-- ===== ENCABEZADO CON ACCIONES ===== -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="mb-1"><?= Html::encode($config['titleSingular'] ?? 'Documento') ?> <span class="text-muted small" id="docnum-text">&nbsp;&nbsp;<?php echo $docnum; ?></span></h3>
            <div class="text-muted small">Capture encabezado, asigne llantas al chasis y revise el resumen antes de guardar.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= Html::encode($config['routes']['index'] ?? '#') ?>"
               class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Regresar
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm" id="doc-tire-open-preview"
                    title="Vista previa del documento"
                <?= empty($document['docentry']) ? 'disabled' : '' ?>>
                <i class="fa-solid fa-eye"></i> Preview
            </button>
            <button type="button" class="btn btn-success btn-sm" id="doc-tire-save">
                <i class="fa-solid fa-floppy-disk"></i> Guardar
            </button>
        </div>
    </div>

    <!-- ===== CUERPO PRINCIPAL ===== -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">

            <form id="doc-tire-form-shell">

                <!-- Campos ocultos / cabecera -->
                <input type="hidden" name="docentry" id="docentry" value="<?= Html::encode((string) ($document['docentry'] ?? '')) ?>">

                <!-- ===== CAMPOS DE CABECERA ===== -->
                <div class="row g-2">
                    <?php foreach (($config['headerFields'] ?? []) as $field): ?>
                        <?php $value = $document[$field['name']] ?? ''; 
                            if($field['name'] !== 'docnum'){ ?>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">
                                <?= Html::encode($field['label']) ?>
                            </label>

                            <?php if (($field['type'] ?? 'text') === 'select'): ?>
                                <select class="form-select form-select-sm"
                                        name="<?= Html::encode($field['name']) ?>"
                                        id="<?= Html::encode($field['name']) ?>">
                                    <?= $renderOptions($resolveOptions($field, $formOptions), $value) ?>
                                </select>
                            <?php else: ?>
                                <input type="<?= Html::encode($field['type'] ?? 'text') ?>"
                                       class="form-control form-control-sm"
                                       name="<?= Html::encode($field['name']) ?>"
                                       id="<?= Html::encode($field['name']) ?>"
                                       value="<?= Html::encode((string) $value) ?>"
                                    <?= ($field['type'] ?? '') === 'readonly' ? 'readonly' : '' ?>
                                    <?= isset($field['step']) ? 'step="' . Html::encode((string) $field['step']) . '"' : '' ?>
                                >
                            <?php endif; ?>
                        </div>
                    <?php } endforeach; ?>
                </div>

                <!-- ===== TABS (UNIDADES | DETALLES | ADJUNTOS) ===== -->
                <ul class="nav nav-tabs mt-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab"
                                data-bs-target="#doc-tab-vehicles"
                                type="button">Unidades</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#doc-tab-details"
                                type="button">Taller visual &amp; Almacén</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#doc-tab-attachments"
                                type="button">Adjuntos</button>
                    </li>
                </ul>

                <div class="tab-content border border-top-0 p-1 bg-white">

                    <!-- ========== TAB 1: UNIDADES ========== -->
                    <div class="tab-pane fade show active" id="doc-tab-vehicles">
                        <?= $this->render('_unidades', [
                            'config'      => $config,
                            'document'    => $document,
                            'formOptions' => $formOptions,
                            'isNewRecord' => $isNewRecord,
                        ]) ?>
                    </div>

                    <!-- ========== TAB 2: TALLER VISUAL (CHASIS + ALMACÉN) ========== -->
                    <div class="tab-pane fade" id="doc-tab-details">
                        <?= $this->render('_taller_visual', [
                            'config'      => $config,
                            'document'    => $document,
                            'formOptions' => $formOptions,
                            'isNewRecord' => $isNewRecord,
                        ]) ?>
                    </div>

                    <!-- ========== TAB 3: ADJUNTOS ========== -->
                    <div class="tab-pane fade" id="doc-tab-attachments">
                        <?= $this->render('_adjuntos', [
                            'config'      => $config,
                            'document'    => $document,
                            'formOptions' => $formOptions,
                            'isNewRecord' => $isNewRecord,
                        ]) ?>
                    </div>
                </div>

                <!-- ===== PIE: COMENTARIOS ===== -->
                <div class="row g-3 mt-2">
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <label class="form-label fw-semibold">Comentarios generales</label>
                                <textarea class="form-control" rows="4" name="comments" id="comments"
                                ><?= Html::encode((string) ($document['comments'] ?? '')) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL DE VISTA PREVIA ===== -->
<div class="modal fade" id="mdl-doc-preview" tabindex="-1"
     aria-labelledby="mdl-doc-preview-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="mdl-doc-preview-label">
                    <i class="fa-solid fa-eye"></i> Vista previa del documento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="mdl-doc-preview-body">
                <div class="text-center text-muted py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p>Cargando vista previa...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" class="btn btn-outline-primary btn-sm" id="mdl-doc-preview-open-page" target="_blank">
                    <i class="fa-solid fa-external-link-alt"></i> Abrir en página completa
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ===== CONFIGURACIÓN GLOBAL PARA LOS MÓDULOS JS ===== -->
<script>
window.DocTireFormConfig = <?= Json::htmlEncode($moduleConfig) ?>;
</script>