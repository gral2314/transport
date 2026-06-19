<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $kpis */

use app\assets\AdmindeskAsset;
use app\assets\DynamicAssetBundle;
use app\assets\FullCalendarAsset;
use yii\helpers\Json;
use yii\web\View;

$this->title = (string) ($config['title'] ?? 'Mantenimientos de llantas');
FullCalendarAsset::register($this);
DynamicAssetBundle::register($this);
// JS loaded automatically by DynamicAssetBundle from scripts/doc-tire-maintenance/index/
$this->registerJsFile('@web/scripts/doc-tire-maintenance/index/00_fullcalendar.js', ['depends' => [FullCalendarAsset::class], 'position' => View::POS_END]);

// URLs para el calendario
$fullCalendarUrls = [
    'calendarEvents' => $config['routes']['calendarEvents'] ?? '',
    'updateDate'     => $config['routes']['updateDate'] ?? '',
    'mechanicOptions' => $config['providerOptionsJson'] ?? '[]',
];
$this->registerJs('window.FullCalendarUrls = ' . Json::encode($fullCalendarUrls) . ';', View::POS_HEAD, 'full-calendar-urls');

// URLs para flujo de trabajo y acciones
$docTireUrls = [
    'list'            => $config['routes']['list'] ?? '',
    'get'             => $config['routes']['get'] ?? '',
    'create'          => $config['routes']['create'] ?? '',
    'updateBase'      => $config['routes']['updateBase'] ?? '',
    'quickViewBase'   => $config['routes']['quickViewBase'] ?? '',
    'sendMailBase'    => $config['routes']['sendMailBase'] ?? '',
    'release'         => $config['routes']['release'] ?? '',
    'start'           => $config['routes']['start'] ?? '',
    'execute'         => $config['routes']['execute'] ?? '',
    'validate'        => $config['routes']['validate'] ?? '',
    'reject'          => $config['routes']['reject'] ?? '',
    'close'           => $config['routes']['close'] ?? '',
    'cancel'          => $config['routes']['cancel'] ?? '',
    'getFormOptions'  => $config['routes']['getFormOptions'] ?? '',
];
$this->registerJs('window.DocTireUrls = ' . Json::encode($docTireUrls) . ';', View::POS_HEAD, 'doc-tire-urls');

echo $this->render('index/index_content', compact('config', 'kpis'));