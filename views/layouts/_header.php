<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\system\User;

/** @var User|null $identity */
$identity  = Yii::$app->user->isGuest ? null : Yii::$app->user->identity;
$fullName  = $identity ? Html::encode($identity->usercode) : '';
$emailDisp = $identity ? Html::encode($identity->email ?? $identity->username ) : '';
$groupName = $identity?->group?->name ?? '';
$assetBase = $this->params['admindeskAssetBaseUrl'] ?? Yii::getAlias('@web') . '/admindesk/assets';
?>
<style>
body {font-size: 12px;!important;}
.table-responsive {font-size: 12px;!important;}
.table-responsive thead tr th{font-size: 12px !important; padding: 0.25rem !important;}
</style>
<!-- Admindesk Header -->
<header class="pc-header">
    <div class="header-wrapper">
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                        <i class="ph ph-list"></i>
                    </a>
                </li>
                <!-- <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                        <i class="ph ph-list"></i>
                    </a>
                </li>
                <li class="pc-h-item">
                    <a class="pc-head-link m-0" href="#" data-bs-toggle="modal" data-bs-target="#commandPalette" title="Search (⌘K)" style="width: auto; overflow: visible; padding: 0 10px; gap: 6px;">
                        <i class="ph ph-magnifying-glass"></i>
                        <kbd class="d-none d-md-inline-block" style="position: relative; z-index: 5; font-size: 0.625rem; padding: 1px 5px; background: var(--bs-body-bg); border: 1px solid var(--bs-border-color); border-radius: 3px; color: var(--bs-secondary); font-family: inherit; line-height: 1.4;">⌘K</kbd>
                    </a>
                </li> -->
            </ul>
        </div>
        <div class="ms-auto">
            <ul class="list-unstyled">
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ph ph-sun-dim"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                        <a href="#" class="dropdown-item" onclick="layout_change('dark')">
                            <i class="ph ph-moon"></i>
                            <span>Dark</span>
                        </a>
                        <a href="#" class="dropdown-item" onclick="layout_change('light')">
                            <i class="ph ph-sun"></i>
                            <span>Light</span>
                        </a>
                        <a href="#" class="dropdown-item" onclick="layout_change_default()">
                            <i class="ph ph-cpu"></i>
                            <span>Default</span>
                        </a>
                    </div>
                </li>
                
                <!-- <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ph ph-bell"></i>
                        <span class="badge bg-success pc-h-badge">5</span>
                    </a>
                    <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header d-flex align-items-center justify-content-between">
                            <h5 class="m-0">Notifications</h5>
                            <a href="#" class="btn btn-link btn-sm">Mark all read</a>
                        </div>
                        <div class="dropdown-body text-wrap header-notification-scroll position-relative" style="max-height: calc(100vh - 215px)">
                            <p class="text-span">Today</p>
                            <div class="card bg-transparent mb-2 border-0">
                                <div class="card-body p-3 rounded" style="background: rgba(var(--bs-light-rgb), 0.3);">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="ph ph-credit-card text-success" style="font-size: 16px;"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <span class="float-end text-sm text-muted">2 min ago</span>
                                            <h5 class="text-body mb-2">Payment Received</h5>
                                            <p class="mb-0">$2,499.00 payment received for Pro Plan subscription from Acme Corp</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center py-2"><a href="#" class="link-danger">Clear all Notifications</a></div>
                    </div>
                </li> -->
                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="ph ph-user-circle"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header">
                            <h6 class="mb-0"><?= $fullName ?></h6>
                            <small class="text-muted"><?= $emailDisp ?></small>
                            <?php if ($groupName): ?>
                                <span class="badge bg-primary-subtle text-primary mt-1"><?= Html::encode($groupName) ?></span>
                            <?php endif ?>
                        </div>
                        <?php // Html::a('<i class="ph ph-user-circle"></i><span>Mi Perfil</span>', ['site/dashboard'], ['class' => 'dropdown-item']) ?>
                        <div class="dropdown-divider"></div>
                            <?= Html::beginForm(['/site/logout'], 'post') ?>

                            <button type="submit" class="btn btn-outline-danger btn-sm col-12">
                                <i class="fas fa-sign-out-alt"></i> Salir
                            </button>

                            <?= Html::endForm() ?>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    </header>

<!-- Command Palette (modal) -->
<div class="modal fade" id="commandPalette" tabindex="-1" aria-label="Command palette" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable command-palette-dialog">
        <div class="modal-content command-palette-content">
            <div class="command-palette-header">
                <div class="command-palette-input-wrapper">
                    <i class="ti ti-search command-palette-search-icon"></i>
                    <input type="text" class="command-palette-input" id="commandPaletteInput" placeholder="Search pages, actions..." autocomplete="off" />
                    <kbd class="command-palette-kbd">ESC</kbd>
                </div>
            </div>
            <div class="command-palette-body" id="commandPaletteResults">
                <!-- Results populated by JS -->
            </div>
            <div class="command-palette-footer">
                <div class="d-flex align-items-center gap-3">
                    <span><kbd>&uarr;</kbd><kbd>&darr;</kbd> Navigate</span>
                    <span><kbd>&crarr;</kbd> Open</span>
                    <span><kbd>ESC</kbd> Close</span>
                </div>
            </div>
        </div>
    </div>
</div>
