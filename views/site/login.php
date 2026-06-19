<?php

/** @var yii\web\View $this */

use yii\helpers\Url;

$this->title = ' Login';
//$this->params['breadcrumbs'][] = $this->title;
?>
<!--<div class="flex-grow-1 bg-login-image" style="background-image: url(&quot;<?php echo Url::base();?>/assets/img/image2.jpeg&quot;);"></div>-->
<style>
body {
    background-image: url('<?php echo Url::base();?>/assets/img/backgrond_login.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    height: 100vh;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-card {
    background-color: rgba(255, 255, 255, 0.9);
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
    border-radius: 10px;
    max-width: 400px;
    padding: 30px;
    text-align: center;
}
</style>

<div class="login-box">
    <!-- /.login-logo -->
    <div class="card login-card p-1">
        <div class="card-body login-card-body">
            <div class="login-logo">
                <a href="#"><b>PMO Solution</b></a>
            </div>
            <?php if($msgError!=''){?><p class="text-danger m-0"> <?= $msgError?> </p> <?php } ?>
            <p class="login-box-msg">Inicia sesión para comenzar tu sesión</p>

            <form action="<?= Yii::$app->urlManager->createUrl(['site/login']) ?>" method="post">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="usercode" name="usercode" autocomplete="off" placeholder="Codigo de usuario" require>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" require>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Recuérdame</label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-6">
                        <button type="submit" class="btn btn-block btn-outline-primary">Iniciar sesión</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
        </div>
        <!-- /.login-card-body -->
    </div>
</div>

<?php //include('mod_changepass.php') ?>