<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $document */

echo $this->render('_preview_content', [
    'config' => $config,
    'document' => $document,
    'autoPrint' => false,
    'renderMode' => 'quick',
]);