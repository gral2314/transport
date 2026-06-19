<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap4\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\Url;

$this->title = 'Tekno Step';
//$this->params['breadcrumbs'][] = $this->title;
?>


<div class="site-login">
<div class="row justify-content-md-center align-items-center" >
    <div class="card col-md-6 d-flex allow-content-center">
    
        <div class="card-body login-card-body">
            <img class="img-fluid" src="https://tekno-step.com/wp-content/uploads/2022/01/logo-tekno-step.svg" alt="logo">
            <!--<h1><?php  //Html::encode($this->title) ?></h1>-->

            <p>Ingresa para inciar sesion:</p>

            
            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'layout' => 'horizontal',
                'action' => '/Intranet/web/site/login',
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'labelOptions' => ['class' => 'col-lg-3 col-form-label mr-lg-3'],
                    'inputOptions' => ['class' => 'col-lg-4 form-control', 'autocomplete' => 'off'],
                    'errorOptions' => ['class' => 'col-lg-3 invalid-feedback'],
                ],
            ]); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'password')->passwordInput() ?>

                <?= $form->field($model, 'rememberMe')->checkbox([
                    'template' => "<div class=\"offset-lg-1 col-lg-3 custom-control custom-checkbox\">{input} {label}</div>\n<div class=\"col-lg-5\">{error}</div>",
                ]) ?>

                <div class="form-group">
                    <div class="offset-lg-1 col-lg-5">
                        <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                    </div>
                </div>

            <?php ActiveForm::end(); ?>

            <p class="mb-1">
                <a id='newpass' name='newpass' href="#">Olvide la contraseña</a>
            </p>

            <div class="offset-lg-1" style="color:#999;">
               <!-- You may login with <strong>admin/admin</strong> or <strong>demo/demo</strong>.<br>
                To modify the username/password, please check out the code <code>app\models\User::$users</code>. -->

            </div>
        </div>
    </div>
    </div>
</div>
<?php include('mod_changepass.php') ?>
