<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $document */
/** @var array $formOptions */
/** @var bool $isNewRecord */

use app\assets\AdmindeskAsset;
use app\assets\DynamicAssetBundle;
use yii\helpers\Json;
use yii\web\View;

$this->title = ($isNewRecord ? 'Nuevo ' : 'Editar ') . (string) ($config['titleSingular'] ?? 'mantenimiento');
//DynamicAssetBundle::register($this);
// JS loaded automatically by DynamicAssetBundle from scripts/doc-tire-maintenance/create/

// URLs para el formulario y flujo
$docTireFormUrls = [
    'save'              => $config['routes']['save'] ?? '',
    'getNextDocnum'     => $config['routes']['getNextDocnum'] ?? '',
    'getAvailableTires' => $config['routes']['getAvailableTires'] ?? '',
    'getFormOptions'    => $config['routes']['getFormOptions'] ?? '',
    'previewBase'       => $config['routes']['previewBase'] ?? '',
    'release'           => $config['routes']['release'] ?? '',
    'start'             => $config['routes']['start'] ?? '',
    'execute'           => $config['routes']['execute'] ?? '',
    'validate'          => $config['routes']['validate'] ?? '',
    'reject'            => $config['routes']['reject'] ?? '',
    'close'             => $config['routes']['close'] ?? '',
    'cancel'            => $config['routes']['cancel'] ?? '',
];
$this->registerJs('window.DocTireFormUrls = ' . Json::encode($docTireFormUrls) . ';', View::POS_HEAD, 'doc-tire-form-urls');

echo $this->render('form/form_content', compact('config', 'document', 'formOptions', 'isNewRecord'));