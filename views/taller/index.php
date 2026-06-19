<?php
/** @var yii\web\View $this */

use app\models\tables\DocTireMovement;
use yii\helpers\Url;

// ── URLs de endpoints para el JS ──────────────────────────────────────────
$urlList   = Url::to(['taller/list']);
$urlGet    = Url::to(['taller/get']);
$urlStart  = Url::to(['taller/start']);
$urlFinish = Url::to(['taller/finish']);
$urlCancel = Url::to(['taller/cancel']);
$urlWorkOrder = Url::to(['taller/view']);
$urlValidate = Url::to(['taller/validate']);

$currentUserId = Yii::$app->user->isGuest ? 0 : Yii::$app->user->id;

// ── URLs (se registran al inicio, no dependen de librerías) ──────────────
$this->registerJs(<<<JS
window.currentUserId = {$currentUserId};
window.TallerUrls = {
    list: '{$urlList}',
    get: '{$urlGet}',
    start: '{$urlStart}',
    finish: '{$urlFinish}',
    cancel: '{$urlCancel}',
    validate: '{$urlValidate}',
    workOrder: '{$urlWorkOrder}'
};
JS, \yii\web\View::POS_BEGIN);

// ── Toast global (SweetAlert2 mixin) — va al final, DESPUÉS de que Swal esté cargado ──
$this->registerJs(<<<JS
window.Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});
JS, \yii\web\View::POS_END);
?>
<hr>
<div class="col-12">

    <!-- Encabezado mobile-first -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fa-solid fa-wrench"></i> Mis Órdenes de Taller</h5>
        <span class="badge badge-info" id="orden-count">0</span>
    </div>

    <!-- Filtros responsive: col-12 en móvil, col-md-6 en desktop -->
    <div class="row mb-3">
        <div class="col-12 col-md-6 mb-2 mb-md-0">
            <select class="form-control form-control-sm" id="filter-status">
                <option value="">Todos los estados</option>
                <option value="<?= DocTireMovement::STATUS_RELEASED ?>" selected>Liberadas</option>
                <option value="<?= DocTireMovement::STATUS_IN_PROGRESS ?>">En Proceso</option>
                <option value="<?= DocTireMovement::STATUS_PENDING_VALIDATION ?>">Pend. Validar</option>
                <option value="<?= DocTireMovement::STATUS_CLOSED ?>">Cerradas</option>
                <option value="<?= DocTireMovement::STATUS_CANCELLED ?>">Canceladas</option>
            </select>
        </div>
        <div class="col-12 col-md-6">
            <input type="text" class="form-control form-control-sm" id="filter-search" placeholder="Buscar folio o comentarios...">
        </div>
    </div>

    <!-- Loading spinner -->
    <div id="loading-ordenes" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
        <p class="text-muted mt-2">Cargando órdenes...</p>
    </div>

    <!-- Contenedor de cards -->
    <div id="ordenes-cards-container" style="display:none;">
        <div id="ordenes-cards" class="row"></div>
        <div class="text-center py-3" id="sin-ordenes" style="display:none;">
            <i class="fa-solid fa-inbox fa-3x text-muted"></i>
            <p class="text-muted mt-2">No tienes órdenes asignadas</p>
        </div>
        <div id="taller-pagination" class="mt-3"></div>
    </div>

</div>

<?php require_once('_movement_card.php') ?>

<?php
// ── Estilos CSS para las cards del dashboard ─────────────────────────────
$this->registerCss(<<<CSS
/* ── Border-left por prioridad (escaneo visual rápido) ── */
.card-orden.border-left-urgent {
    border-left: 5px solid #dc3545 !important;
}
.card-orden.border-left-high {
    border-left: 5px solid #fd7e14 !important;
}
.card-orden.border-left-medium {
    border-left: 5px solid #ffc107 !important;
}
.card-orden.border-left-low {
    border-left: 5px solid #6c757d !important;
}

/* ── Preview de comentarios: máx 2 líneas con fade-out ── */
.comment-preview {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    position: relative;
    line-height: 1.4;
    max-height: 2.8em;
}

/* ── Transición suave al hover de cards ── */
.card-orden {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.card-orden:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12) !important;
}

/* ── Gap para flex (fallback para navegadores sin soporte nativo) ── */
.gap-2 {
    gap: 0.5rem;
}
CSS, [], 'taller-dashboard-css');
?>
