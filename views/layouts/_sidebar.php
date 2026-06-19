<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use app\components\MenuService;

$assetBase = $this->params['admindeskAssetBaseUrl'] ?? Yii::getAlias('@web') . '/admindesk/assets';
?>

<!-- Admindesk Sidebar -->
<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <a href="#" class="b-brand text-primary">
        <img src="<?= $assetBase ?>/images/logo-white.svg" class="img-fluid logo-lg" alt="logo" />
      </a>
    </div>
    <div class="navbar-content">
      <?= MenuService::renderSidebar() ?>
    </div>
  </div>
</nav>
