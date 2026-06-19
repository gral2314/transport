<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */

use yii\helpers\Url;
use app\assets\FullCalendarAsset;

// Registrar FullCalendar ANTES que el JS específico
FullCalendarAsset::register($this);

// Registrar URLs para FullCalendar JS (POS_BEGIN para que estén disponibles)
$this->registerJs(<<<JS
window.FullCalendarUrls = {
    events: '{$config['routes']['calendarEvents']}',
    updateDate: '{$config['routes']['updateDate']}',
    mechanicOptions: {$config['mechanicOptionsJson']},
};
JS
, \yii\web\View::POS_BEGIN);

// Cargar 00_fullcalendar.js manualmente con POS_END.
// Depende de AdmindeskAsset para asegurar que jQuery esté cargado.
// Se registra después de FullCalendarAsset, por lo que se renderiza
// después de index.global.min.js en el orden de jsFiles[POS_END].
// $this->registerJsFile(
//     Url::to('@web/scripts/doc-tire-assignment/index/00_fullcalendar.js'),
//     ['position' => \yii\web\View::POS_END, 'depends' => [\app\assets\AdmindeskAsset::class]]
// );
?>
<div id="doc-tire-calendar-container" class="mt-3" style="display: none;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="mb-0"><i class="fa-solid fa-calendar-alt"></i> Calendario de documentos</h5>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="input-group input-group-sm" style="min-width: 250px;">
                        <span class="input-group-text"><i class="fa-solid fa-user-gear"></i></span>
                        <select class="form-select" id="calendar-employee-filter">
                            <option value="">Todos los mecánicos</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="calendar-today-btn">
                        <i class="fa-solid fa-calendar-day"></i> Hoy
                    </button>
                </div>
            </div>
            <div id="fullcalendar-container"></div>
        </div>
    </div>
</div>

<!-- Tooltip personalizado para eventos -->
<div id="calendar-event-tooltip" class="calendar-event-tooltip" style="display:none; position:fixed; z-index:99999; background:#fff; border:1px solid #ccc; border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,0.15); pointer-events:none;"></div>

<style>
/* Asegurar que eventos del calendario tengan cursor pointer */
.fc-event {
    cursor: pointer !important;
}
/* Tooltip */
.calendar-event-tooltip {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    line-height: 1.5;
    max-width: 320px;
}
/* Responsive para el calendario en móviles */
@media (max-width: 768px) {
    #fullcalendar-container .fc-toolbar {
        flex-direction: column;
        gap: 8px;
    }
    #fullcalendar-container .fc-toolbar .fc-left,
    #fullcalendar-container .fc-toolbar .fc-center,
    #fullcalendar-container .fc-toolbar .fc-right {
        float: none !important;
    }
}
</style>
