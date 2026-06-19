<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $kpis */

use app\assets\AdmindeskAsset;
use app\assets\DynamicAssetBundle;
use yii\web\View;

$this->title = (string) ($config['title'] ?? 'Bajas de llantas');
DynamicAssetBundle::register($this);
$this->registerJsFile('@web/scripts/doc-tire-disposal/index/doc-tire-disposal-index.js', ['depends' => [AdmindeskAsset::class], 'position' => View::POS_END]);

echo $this->render('index/index_content', compact('config', 'kpis'));