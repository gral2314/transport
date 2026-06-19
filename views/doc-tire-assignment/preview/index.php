<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $document */
/** @var bool $autoPrint */
/** @var string $renderMode */

use app\assets\DynamicAssetBundle;

$this->title = ($config['titleSingular'] ?? 'Documento') . ' #' . ($document['docnum'] ?? '');

if ($renderMode === 'html') {
    DynamicAssetBundle::register($this);
}

require_once('preview_content.php');
