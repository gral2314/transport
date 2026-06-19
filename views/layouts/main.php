<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Url;
use app\widgets\Alert;
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;

$controller = Yii::$app->controller->id;
$action = Yii::$app->controller->action->id;
$this->render('_head');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?= Html::csrfMetaTags() ?>
        <?php $this->head() ?>
        <script>
            const Urlservice = '<?=  Url::home().Html::encode($controller).'/';?>'
            const Urlhome = '<?= Url::home();?>'
        </script>
        <title><?= Html::encode($this->title) ?></title>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
<?php $this->beginBody() ?>

<?= $this->render('_sidebar') ?>
<?= $this->render('_header') ?>
<?php if (isset(Yii::$app->user->identity->username)){
     echo 'Conectado como: '.Yii::$app->user->identity->username;
} else {
    echo 'No conectado';
  }?>

<div class="pc-container">
    <div class="pc-content p-2">
        <main id="main" role="main">
            <div class="container">
                <?php if (!empty($this->params['breadcrumbs'])): ?>
                    <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
                <?php endif ?>
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </main>
    </div>
</div>

<?php //$this->render('_footer') ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
