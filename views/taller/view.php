<?php
/**
 * Vista de ejecución de orden de taller — Mobile-First
 * 
 * Diseñada para:
 *   - Móvil: layout vertical con cards, botones en zona de pulgar (abajo)
 *   - Desktop: layout normal con tablas
 * 
 * @var yii\web\View $this
 * @var array $document
 */

use app\models\tables\DocTireMovement;
use yii\helpers\Url;

// ── URLs de endpoints para el JS ──────────────────────────────────────────
$urlGet    = Url::to(['taller/get']);
$urlStart  = Url::to(['taller/start']);
$urlFinish = Url::to(['taller/finish']);
$urlCancel = Url::to(['taller/cancel']);
$urlGetFormOptions = Url::to(['taller/get-form-options']);
$urlIndex = Url::to(['taller/index']);

$this->registerJs(<<<JS
window.TallerViewUrls = {
    get: '{$urlGet}',
    start: '{$urlStart}',
    finish: '{$urlFinish}',
    cancel: '{$urlCancel}',
    getFormOptions: '{$urlGetFormOptions}',
    index: '{$urlIndex}'
};
JS, \yii\web\View::POS_BEGIN);

$statusLabels = [
    DocTireMovement::STATUS_PLANNED              => 'Planeada',
    DocTireMovement::STATUS_RELEASED             => 'Liberada',
    DocTireMovement::STATUS_IN_PROGRESS          => 'En Proceso',
    DocTireMovement::STATUS_PENDING_VALIDATION   => 'Pendiente Validar',
    DocTireMovement::STATUS_CLOSED               => 'Cerrada',
    DocTireMovement::STATUS_CANCELLED            => 'Cancelada',
];
$statusBadge = [
    DocTireMovement::STATUS_PLANNED              => 'badge-info',
    DocTireMovement::STATUS_RELEASED             => 'badge-primary',
    DocTireMovement::STATUS_IN_PROGRESS          => 'badge-warning',
    DocTireMovement::STATUS_PENDING_VALIDATION   => 'badge-secondary',
    DocTireMovement::STATUS_CLOSED               => 'badge-success',
    DocTireMovement::STATUS_CANCELLED            => 'badge-danger',
];
$status = $document['status'] ?? '';
$label  = $statusLabels[$status] ?? $status;
$badge  = $statusBadge[$status] ?? 'badge-secondary';
$docentry = $document['docentry'] ?? 0;
?>

<!-- ============================================================ -->
<!-- ENCABEZADO MOBILE-FIRST                                        -->
<!-- ============================================================ -->
<div class="col-12" id="tv-main-container">

    <!-- Spinner de carga inicial (se oculta via JS cuando todo listo) -->
    <div id="tv-loading-spinner" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
        <p class="text-muted mt-2">Cargando orden...</p>
    </div>

    <!-- Contenido principal (oculto hasta carga completa) -->
    <div id="tv-content" style="display:none;">

        <!-- ── Header ─────────────────────────────────────────── -->
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h5 class="mb-1">
                    <i class="fa-solid fa-wrench"></i> Orden
                    <small class="text-muted">#<?= htmlspecialchars((string)($document['docnum'] ?? '')) ?></small>
                </h5>
                <span class="badge <?= $badge ?>"><?= htmlspecialchars($label) ?></span>
            </div>
            <a href="<?= Url::to(['taller/index']) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>

        <!-- ── Card: Información general ──────────────────────── -->
        <div class="card card-outline card-info mb-2">
            <div class="card-body py-2">
                <div class="row small">
                    <div class="col-6"><strong>Folio:</strong> <?= htmlspecialchars((string)($document['docnum'] ?? '-')) ?></div>
                    <div class="col-6"><strong>Fecha:</strong> <?= htmlspecialchars((string)($document['docdate'] ?? '-')) ?></div>
                    <div class="col-6 mt-1"><strong>Prioridad:</strong> <?= htmlspecialchars((string)($document['priority'] ?? '-')) ?></div>
                    <div class="col-6 mt-1"><strong>Origen:</strong> <?= htmlspecialchars((string)($document['origin'] ?? '-')) ?></div>
                    <div class="col-6 mt-1"><strong>Técnico:</strong> <span id="tv-assigned-user"><?= htmlspecialchars((string)($document['technicianUser']['username'] ?? '-')) ?></span></div>
                    <div class="col-6 mt-1"><strong>Validó:</strong> <span id="tv-validated-user"><?= htmlspecialchars((string)($document['validatedByUser']['username'] ?? '-')) ?></span></div>
                </div>
                <?php if (!empty($document['comments'])): ?>
                    <div class="mt-1 small">
                        <strong>Comentarios:</strong>
                        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($document['comments'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Card: Timeline compacto ────────────────────────── -->
        <div class="card card-outline card-secondary mb-2">
            <div class="card-body py-2">
                <div class="row small text-center">
                    <div class="col-4"><strong>Liberado</strong><br><span class="text-muted" id="tv-released-at"><?= htmlspecialchars((string)($document['released_at'] ?? '-')) ?></span></div>
                    <div class="col-4"><strong>Iniciado</strong><br><span class="text-muted" id="tv-started-at"><?= htmlspecialchars((string)($document['started_at'] ?? '-')) ?></span></div>
                    <div class="col-4"><strong>Finalizado</strong><br><span class="text-muted" id="tv-completed-at"><?= htmlspecialchars((string)($document['completed_at'] ?? '-')) ?></span></div>
                </div>
            </div>
        </div>

        <!-- ── Card: Odómetro (editable solo en IN_PROGRESS) ──── -->
        <div class="card card-outline card-primary mb-2">
            <div class="card-body py-2">
                <label for="tv-odometer" class="small mb-1"><strong><i class="fa-solid fa-tachometer-alt"></i> Odómetro (km)</strong></label>
                <input type="number" class="form-control form-control-lg" id="tv-odometer"
                       value="<?= htmlspecialchars((string)($document['odometer_final'] ?? $document['odometer_initial'] ?? '')) ?>"
                       placeholder="Ingresa el odómetro actual"
                       <?= $status !== DocTireMovement::STATUS_IN_PROGRESS ? 'readonly' : '' ?>>
                <?php if (!empty($document['odometer_initial'])): ?>
                    <small class="text-muted">Odómetro inicial: <?= htmlspecialchars((string)$document['odometer_initial']) ?> km</small>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Card: Checklist de movimientos ─────────────────── -->
        <div class="card card-outline card-success mb-2">
            <div class="card-header py-1 d-flex justify-content-between align-items-center">
                <h6 class="card-title small mb-0"><i class="fa-solid fa-list-check"></i> Movimientos</h6>
                <?php if ($status === DocTireMovement::STATUS_IN_PROGRESS): ?>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="tv-check-all">
                    <label class="custom-control-label small" for="tv-check-all">Marcar todos</label>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="tv-checklist">
                    <?php if (!empty($document['details'])): ?>
                        <?php foreach ($document['details'] as $i => $d): 
                            $tireCode = htmlspecialchars((string)($d['tire_code'] ?? $d['tire_id'] ?? '-'));
                            $movType  = htmlspecialchars((string)($d['movement_type'] ?? '-'));
                            $origin   = htmlspecialchars((string)($d['origin'] ?? '-'));
                            $dest     = htmlspecialchars((string)($d['destination'] ?? '-'));
                            $done     = ($d['completed'] ?? 'N') === 'Y';
                        ?>
                        <li class="list-group-item d-flex align-items-center py-2 px-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input tv-move-check"
                                       id="tv-move-<?= $i ?>" data-idx="<?= $i ?>"
                                       <?= $done ? 'checked disabled' : '' ?>
                                       <?= $status !== DocTireMovement::STATUS_IN_PROGRESS ? 'disabled' : '' ?>>
                                <label class="custom-control-label" for="tv-move-<?= $i ?>">
                                    <strong><?= $tireCode ?></strong>
                                    <span class="d-block small text-muted"><?= $movType ?> · <?= $origin ?> → <?= $dest ?></span>
                                </label>
                            </div>
                            <?php if ($done): ?>
                                <span class="badge badge-success ml-auto"><i class="fa-solid fa-check"></i></span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted small">Sin movimientos</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- ── Card: Evidencia fotográfica (mobile-first) ─────── -->
        <div class="card card-outline card-warning mb-2">
            <div class="card-header py-1 d-flex justify-content-between align-items-center">
                <h6 class="card-title small mb-0"><i class="fa-solid fa-camera"></i> Evidencia</h6>
                <?php if ($status === DocTireMovement::STATUS_IN_PROGRESS): ?>
                <label for="tv-camera-input" class="btn btn-sm btn-outline-primary mb-0" id="tv-btn-camera-label">
                    <i class="fa-solid fa-camera"></i> Capturar
                </label>
                <?php endif; ?>
            </div>
            <div class="card-body py-2">
                <!-- Input oculto para cámara/galería -->
                <input type="file" id="tv-camera-input" accept="image/*" multiple
                       style="display:none;"
                       <?= $status !== DocTireMovement::STATUS_IN_PROGRESS ? 'disabled' : '' ?>>

                <!-- Grid de previsualizaciones -->
                <div class="row" id="tv-ev-preview"></div>

                <!-- Notas de evidencia -->
                <div class="mt-2">
                    <textarea class="form-control form-control-sm" id="tv-ev-notes" rows="2"
                              placeholder="Notas sobre la evidencia..."
                              <?= $status !== DocTireMovement::STATUS_IN_PROGRESS ? 'readonly' : '' ?>></textarea>
                </div>
            </div>
        </div>

        <!-- ── Timeline extendido (solo desktop) ──────────────── -->
        <div class="card card-outline card-secondary mb-2 d-none d-md-block">
            <div class="card-body py-2 small">
                <div class="row text-center">
                    <div class="col"><strong>Liberado</strong><br><span class="text-muted"><?= htmlspecialchars((string)($document['released_at'] ?? '-')) ?></span></div>
                    <div class="col"><strong>Iniciado</strong><br><span class="text-muted"><?= htmlspecialchars((string)($document['started_at'] ?? '-')) ?></span></div>
                    <div class="col"><strong>Finalizado</strong><br><span class="text-muted"><?= htmlspecialchars((string)($document['completed_at'] ?? '-')) ?></span></div>
                    <div class="col"><strong>Validado</strong><br><span class="text-muted"><?= htmlspecialchars((string)($document['validated_at'] ?? '-')) ?></span></div>
                    <div class="col"><strong>Cancelado</strong><br><span class="text-muted"><?= htmlspecialchars((string)($document['cancelled_at'] ?? '-')) ?></span></div>
                </div>
            </div>
        </div>

        <!-- ── Tabla de unidades (solo desktop) ───────────────── -->
        <div class="card card-outline card-secondary mb-2 d-none d-md-block">
            <div class="card-header py-1"><h6 class="card-title small mb-0"><i class="fa-solid fa-truck"></i> Unidades</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Unidad</th></tr></thead>
                    <tbody>
                        <?php if (!empty($document['vehicles'])): ?>
                            <?php foreach ($document['vehicles'] as $i => $v): ?>
                            <tr><td><?= $i + 1 ?></td><td><?= htmlspecialchars((string)($v['vehicle_name'] ?? $v['vehicle_id'] ?? '-')) ?></td></tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="2" class="text-muted">Sin unidades</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Tabla de detalle (solo desktop) ────────────────── -->
        <div class="card card-outline card-secondary mb-2 d-none d-md-block">
            <div class="card-header py-1"><h6 class="card-title small mb-0"><i class="fa-solid fa-list"></i> Detalle de llantas</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>#</th><th>Llanta</th><th>Tipo</th><th>Origen</th><th>Destino</th></tr></thead>
                    <tbody>
                        <?php if (!empty($document['details'])): ?>
                            <?php foreach ($document['details'] as $i => $d): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars((string)($d['tire_code'] ?? $d['tire_id'] ?? '-')) ?></td>
                                <td><?= htmlspecialchars((string)($d['movement_type'] ?? '-')) ?></td>
                                <td><?= htmlspecialchars((string)($d['origin'] ?? '-')) ?></td>
                                <td><?= htmlspecialchars((string)($d['destination'] ?? '-')) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-muted">Sin detalle</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── ZONA DE ACCIÓN (thumb zone — siempre visible abajo) ── -->
        <div class="row mt-3 mb-5" id="tv-action-zone">
            <div class="col-12">
                <div class="btn-group btn-group-lg d-flex" role="group">
                    <!-- Botón Iniciar: visible solo en RELEASED -->
                    <button type="button" class="btn btn-primary d-none" id="tv-btn-start"
                            data-docentry="<?= $docentry ?>">
                        <i class="fa-solid fa-play"></i> Iniciar
                    </button>

                    <!-- Botón Finalizar: visible solo en IN_PROGRESS -->
                    <button type="button" class="btn btn-warning d-none" id="tv-btn-finish"
                            data-docentry="<?= $docentry ?>">
                        <i class="fa-solid fa-check"></i> Finalizar
                    </button>

                    <!-- Botón Cancelar: visible excepto CLOSED/CANCELLED -->
                    <button type="button" class="btn btn-outline-danger d-none" id="tv-btn-cancel"
                            data-docentry="<?= $docentry ?>">
                        <i class="fa-solid fa-ban"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>

    </div><!-- /tv-content -->
</div><!-- /tv-main-container -->

<?php
// ── Mostrar botones según estado ──────────────────────────────────────────
$jsShow = '';
if ($status === DocTireMovement::STATUS_RELEASED) {
    $jsShow = 'document.getElementById("tv-btn-start").classList.remove("d-none");';
} elseif ($status === DocTireMovement::STATUS_IN_PROGRESS) {
    $jsShow = 'document.getElementById("tv-btn-finish").classList.remove("d-none");';
}
if (!in_array($status, [DocTireMovement::STATUS_CLOSED, DocTireMovement::STATUS_CANCELLED], true)) {
    $jsShow .= 'document.getElementById("tv-btn-cancel").classList.remove("d-none");';
}
// Ocultar spinner, mostrar contenido
$jsShow .= 'document.getElementById("tv-loading-spinner").style.display="none";';
$jsShow .= 'document.getElementById("tv-content").style.display="block";';

if ($jsShow !== '') {
    $this->registerJs("(function(){ {$jsShow} })();");
}

// ── Checkbox "Marcar todos" ──────────────────────────────────────────────
$this->registerJs(<<<JS
$(document).on('change', '#tv-check-all', function() {
    var isChecked = $(this).is(':checked');
    $('.tv-move-check:not(:disabled)').prop('checked', isChecked);
});
$(document).on('change', '.tv-move-check', function() {
    var total = $('.tv-move-check').length;
    var checked = $('.tv-move-check:checked').length;
    $('#tv-check-all').prop('checked', total > 0 && checked === total);
});
JS, \yii\web\View::POS_END);

// ── Toast global (SweetAlert2 mixin) ─────────────────────────────────────
$this->registerJs(<<<JS
window.Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: function(toast) {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});
JS, \yii\web\View::POS_END);

// ── Botones de acción: Iniciar / Finalizar / Cancelar ────────────────────
$this->registerJs(<<<JS
// ── Iniciar orden ────────────────────────────────────────────────────────
$(document).on('click', '#tv-btn-start', function() {
    var docentry = $(this).data('docentry');
    if (!docentry) return;

    Swal.fire({
        title: '¿Iniciar orden?',
        text: 'La orden pasará a "En Proceso" y podrás comenzar a registrar movimientos.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, iniciar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: window.TallerViewUrls.start,
            type: 'POST',
            data: { docentry: docentry },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.Success === 'Ok') {
                Toast.fire({ icon: 'success', title: response.Msg || 'Orden iniciada.' });
                setTimeout(function() { location.reload(); }, 800);
            } else {
                Toast.fire({ icon: 'error', title: response.Msg || 'Error al iniciar.' });
                setTimeout(function() { window.location.href = window.TallerViewUrls.index; }, 1500);
            }
        })
        .fail(function(jqXHR) {
            var msg = 'Error de conexión.';
            try { var r = JSON.parse(jqXHR.responseText); if (r.Msg) msg = r.Msg; } catch(e) {}
            Toast.fire({ icon: 'error', title: msg });
            setTimeout(function() { window.location.href = window.TallerViewUrls.index; }, 1500);
        });
    });
});

// ── Finalizar orden ──────────────────────────────────────────────────────
$(document).on('click', '#tv-btn-finish', function() {
    var docentry = $(this).data('docentry');
    if (!docentry) return;

    // Validar odómetro antes de confirmar
    var odometerVal = ($('#tv-odometer').val() || '').trim();
    if (!odometerVal || isNaN(odometerVal) || Number(odometerVal) <= 0) {
        Toast.fire({ icon: 'warning', title: 'Debes ingresar el odómetro actual antes de finalizar.' });
        return;
    }

    Swal.fire({
        title: '¿Finalizar orden?',
        text: 'La orden pasará a "Pendiente de Validación". Asegúrate de haber completado todos los movimientos.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: window.TallerViewUrls.finish,
            type: 'POST',
            data: { docentry: docentry, odometer: odometerVal },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.Success === 'Ok') {
                Toast.fire({ icon: 'success', title: response.Msg || 'Orden finalizada.' });
                setTimeout(function() { window.location.href = window.TallerViewUrls.index; }, 800);
            } else {
                Toast.fire({ icon: 'error', title: response.Msg || 'Error al finalizar.' });
                setTimeout(function() { window.location.href = window.TallerViewUrls.index; }, 1500);
            }
        })
        .fail(function(jqXHR) {
            var msg = 'Error de conexión.';
            try { var r = JSON.parse(jqXHR.responseText); if (r.Msg) msg = r.Msg; } catch(e) {}
            Toast.fire({ icon: 'error', title: msg });
            setTimeout(function() { window.location.href = window.TallerViewUrls.index; }, 1500);
        });
    });
});

// ── Cancelar orden ───────────────────────────────────────────────────────
$(document).on('click', '#tv-btn-cancel', function() {
    var docentry = $(this).data('docentry');
    if (!docentry) return;

    Swal.fire({
        title: '¿Cancelar orden?',
        text: 'Esta acción no se puede deshacer. La orden será cancelada definitivamente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No cancelar',
        confirmButtonColor: '#dc3545'
    }).then(function(result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: window.TallerViewUrls.cancel,
            type: 'POST',
            data: { docentry: docentry },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.Success === 'Ok') {
                Toast.fire({ icon: 'success', title: response.Msg || 'Orden cancelada.' });
                setTimeout(function() { window.location.href = window.TallerViewUrls.index; }, 800);
            } else {
                Toast.fire({ icon: 'error', title: response.Msg || 'Error al cancelar.' });
                setTimeout(function() { window.location.href = window.TallerViewUrls.index; }, 1500);
            }
        })
        .fail(function(jqXHR) {
            var msg = 'Error de conexión.';
            try { var r = JSON.parse(jqXHR.responseText); if (r.Msg) msg = r.Msg; } catch(e) {}
            Toast.fire({ icon: 'error', title: msg });
            setTimeout(function() { window.location.href = window.TallerViewUrls.index; }, 1500);
        });
    });
});
JS, \yii\web\View::POS_END);
?>