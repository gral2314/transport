<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\system\User $model */

use yii\helpers\Html;

$this->title = 'Editar: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="ph ph-pencil me-2"></i><?= Html::encode($this->title) ?></h5>
    </div>
    <div class="card-body">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>
