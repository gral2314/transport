<?php

declare(strict_types=1);

/** @var yii\web\View $this */


use app\assets\AppAsset;
use app\assets\AdmindeskAsset;
use app\assets\DynamicAssetBundle;

/**
 * ORDEN DE CARGA (automático vía $depends):
 * 1. AdmindeskAsset (jQuery + DataTables + Bootstrap)
 * 2. AppAsset (FontAwesome) - depende de AdmindeskAsset
 * 3. DynamicAssetBundle (scripts por controller/action) - depende de AdmindeskAsset
 * 
 * Solo registramos AppAsset - las dependencias se resuelven automáticamente
 */
AppAsset::register($this);
DynamicAssetBundle::register($this);

// Guardar baseUrl de AdmindeskAsset para uso en vistas
$admindesk = AdmindeskAsset::register($this);
$this->params['admindeskAssetBaseUrl'] = $admindesk->baseUrl;


$this->registerCsrfMetaTags();
$this->registerMetaTag(
    ['charset' => Yii::$app->charset],
    'charset',
);
$this->registerMetaTag(
    [
        'name' => 'viewport',
        'content' => 'width=device-width, initial-scale=1',
    ],
);
if (!empty($this->params['meta_description'])) {
    $this->registerMetaTag(
        [
            'name' => 'description',
            'content' => $this->params['meta_description'],
        ],
    );
}
if (!empty($this->params['meta_keywords'])) {
    $this->registerMetaTag(
        [
            'name' => 'keywords',
            'content' => $this->params['meta_keywords'],
        ],
    );
}
$this->registerLinkTag(
    [
        'rel' => 'icon',
        'type' => 'image/x-icon',
        'href' => Yii::getAlias('@web/favicon.ico'),
    ],
);
