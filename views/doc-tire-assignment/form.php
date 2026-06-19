<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $document */
/** @var array $formOptions */
/** @var bool $isNewRecord */

use app\assets\AdmindeskAsset;
use app\assets\DynamicAssetBundle;
use yii\web\View;

$this->title = ($isNewRecord ? 'Nueva ' : 'Editar ') . (string) ($config['titleSingular'] ?? 'Asignacion');
DynamicAssetBundle::register($this);
$this->registerJsFile('@web/scripts/doc-tire-assignment/form/doc-tire-assignment-form.js', ['depends' => [AdmindeskAsset::class], 'position' => View::POS_END]);

echo $this->render('form/form_content', compact('config', 'document', 'formOptions', 'isNewRecord'));