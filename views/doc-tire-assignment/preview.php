<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $document */
/** @var bool $autoPrint */
/** @var string $renderMode */

use app\assets\AdmindeskAsset;
use app\assets\DynamicAssetBundle;
use yii\web\View;

$this->title = 'Preview ' . (string) ($config['titleSingular'] ?? 'documento');
DynamicAssetBundle::register($this);
if ($renderMode === 'html') {
    $this->registerJsFile('@web/scripts/doc-tire-assignment/preview/doc-tire-assignment-preview.js', ['depends' => [AdmindeskAsset::class], 'position' => View::POS_END]);
}

echo $this->render('preview/preview_content', compact('config', 'document', 'autoPrint', 'renderMode'));