<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $kpis */

use app\assets\DynamicAssetBundle;
use yii\helpers\Json;

$this->title = $config['title'] ?? 'Asignaciones de llantas';

// Registrar DynamicAssetBundle para auto-cargar JS de web/scripts/doc-tire-assignment/index/
DynamicAssetBundle::register($this);

// Pasar configuración al JS
$moduleConfig = Json::htmlEncode([
    'config' => $config,
    'kpis' => $kpis,
]);
$this->registerJs("window.DocTireModuleConfig = {$moduleConfig};", \yii\web\View::POS_HEAD);

echo $this->render('list/index', [
    'config' => $config,
    'kpis' => $kpis,
]);