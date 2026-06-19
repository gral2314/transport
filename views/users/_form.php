<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\system\User $model */

use app\models\system\UserGroup;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

// Opciones de grupo
$groupOptions = UserGroup::find()
    ->where(['active' => 1])
    ->select(['name', 'id'])
    ->indexBy('id')
    ->column();
?>

<?php $form = ActiveForm::begin([
    'id'          => 'user-form',
    'fieldConfig' => [
        'labelOptions'  => ['class' => 'form-label fw-semibold small'],
        'inputOptions'  => ['class' => 'form-control'],
        'errorOptions'  => ['class' => 'invalid-feedback', 'tag' => 'div'],
    ],
]) ?>

<div class="row g-3">

    <!-- Username -->
    <div class="col-md-4">
        <?= $form->field($model, 'username', [
            'inputOptions' => ['class' => 'form-control', 'placeholder' => 'nombre.usuario'],
        ])->textInput(['maxlength' => true])->label('Usuario') ?>
    </div>

    <!-- Código -->
    <div class="col-md-4">
        <?= $form->field($model, 'code', [
            'inputOptions' => ['class' => 'form-control', 'placeholder' => 'USR-0001'],
        ])->textInput(['maxlength' => true])->label('Código') ?>
    </div>

    <!-- Grupo -->
    <div class="col-md-4">
        <?= $form->field($model, 'group_id')->dropDownList(
            $groupOptions,
            ['prompt' => '— Sin grupo —', 'class' => 'form-select']
        )->label('Grupo') ?>
    </div>

    <!-- Nombre -->
    <div class="col-md-6">
        <?= $form->field($model, 'name', [
            'inputOptions' => ['class' => 'form-control', 'placeholder' => 'Nombre(s)'],
        ])->textInput(['maxlength' => true])->label('Nombre') ?>
    </div>

    <!-- Apellido -->
    <div class="col-md-6">
        <?= $form->field($model, 'last_name', [
            'inputOptions' => ['class' => 'form-control', 'placeholder' => 'Apellido(s)'],
        ])->textInput(['maxlength' => true])->label('Apellido') ?>
    </div>

    <!-- Email -->
    <div class="col-md-6">
        <?= $form->field($model, 'email', [
            'inputOptions' => ['class' => 'form-control', 'placeholder' => 'correo@ejemplo.com'],
        ])->textInput(['maxlength' => true])->label('Correo electrónico') ?>
    </div>

    <!-- Teléfono -->
    <div class="col-md-6">
        <?= $form->field($model, 'phone', [
            'inputOptions' => ['class' => 'form-control', 'placeholder' => '+52 (000) 000-0000'],
        ])->textInput(['maxlength' => true])->label('Teléfono') ?>
    </div>

    <!-- Contraseña -->
    <div class="col-md-6">
        <?= $form->field($model, 'password', [
            'inputOptions' => [
                'class'       => 'form-control',
                'placeholder' => $model->isNewRecord ? 'Mínimo 6 caracteres' : 'Dejar vacío para no cambiar',
                'autocomplete'=> 'new-password',
            ],
        ])->passwordInput()->label($model->isNewRecord ? 'Contraseña' : 'Nueva contraseña') ?>
    </div>

    <!-- Activo -->
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
            <?= Html::activeCheckbox($model, 'active', [
                'class'   => 'form-check-input',
                'role'    => 'switch',
                'id'      => 'user-active',
            ]) ?>
            <label class="form-check-label fw-semibold small" for="user-active">Usuario activo</label>
        </div>
    </div>

</div>

<hr class="my-4">

<div class="d-flex gap-2">
    <?= Html::submitButton(
        '<i class="ph ph-floppy-disk me-1"></i>' . ($model->isNewRecord ? 'Crear usuario' : 'Guardar cambios'),
        ['class' => 'btn btn-primary']
    ) ?>
    <?= Html::a('<i class="ph ph-x me-1"></i>Cancelar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end() ?>
