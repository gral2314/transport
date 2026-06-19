<?php
declare(strict_types=1);

/**
 * @var yii\web\View $this
 * @var array $config
 * @var array $document
 * @var array $formOptions
 * @var bool $isNewRecord
 */

use app\assets\AdmindeskAsset;
use app\assets\DynamicAssetBundle;
use yii\web\View;

$this->title = ($isNewRecord ? 'Nueva ' : 'Editar ') . ($config['titleSingular'] ?? 'asignacion');
DynamicAssetBundle::register($this);

// Registrar JS por responsabilidad (cada archivo maneja una responsabilidad específica)
$this->registerJsFile('@web/scripts/doc-tire-assignment/create/doc-tire-assignment-state.js', ['depends' => [AdmindeskAsset::class], 'position' => View::POS_END]);
$this->registerJsFile('@web/scripts/doc-tire-assignment/create/doc-tire-assignment-modal.js', ['depends' => [AdmindeskAsset::class], 'position' => View::POS_END]);
$this->registerJsFile('@web/scripts/doc-tire-assignment/create/doc-tire-assignment-vehicle.js', ['depends' => [AdmindeskAsset::class], 'position' => View::POS_END]);
$this->registerJsFile('@web/scripts/doc-tire-assignment/create/doc-tire-assignment-chassis.js', ['depends' => [AdmindeskAsset::class], 'position' => View::POS_END]);
$this->registerJsFile('@web/scripts/doc-tire-assignment/create/doc-tire-assignment-dragdrop.js', ['depends' => [AdmindeskAsset::class], 'position' => View::POS_END]);
$this->registerJsFile('@web/scripts/doc-tire-assignment/create/doc-tire-assignment-tire-warehouse.js', ['depends' => [AdmindeskAsset::class], 'position' => View::POS_END]);
$this->registerJsFile('@web/scripts/doc-tire-assignment/create/doc-tire-assignment-form.js', ['depends' => [AdmindeskAsset::class], 'position' => View::POS_END]);
$this->registerJsFile('@web/scripts/doc-tire-assignment/create/doc-tire-assignment-summary.js', ['depends' => [AdmindeskAsset::class], 'position' => View::POS_END]);

echo $this->render('create/index', compact('config', 'document', 'formOptions', 'isNewRecord'));