<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Asset bundle para FullCalendar 6.
 * Carga CSS desde CDN y JS desde archivo local.
 */
class FullCalendarAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css',
    ];

    public $js = [
        'assets/plugins/fullcalendar/index.global.min.js',
    ];

    public $jsOptions = [
        'position' => View::POS_END,
    ];

    // Sin depends para evitar conflicto de posición con AdmindeskAsset (POS_END).
    // FullCalendarAsset se registra manualmente en _calendar.php.
    public $depends = [];
}
