<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\system\User $model */

use yii\helpers\Html;

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$canUpdate = Yii::$app->user->can('users.update');
$canDelete = Yii::$app->user->can('users.delete') && $model->id !== (int) Yii::$app->user->id;
$isSelf    = $model->id === (int) Yii::$app->user->id;

// Roles RBAC del usuario
$roles = Yii::$app->authManager->getRolesByUser($model->id);
$permissions = Yii::$app->authManager->getPermissionsByUser($model->id);
?>

<div class="row g-4">

    <!-- Datos principales -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="ph ph-user me-2"></i>Detalle del usuario</h5>
                <div class="d-flex gap-2">
                    <?php if ($canUpdate): ?>
                        <?= Html::a('<i class="ph ph-pencil me-1"></i>Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    <?php endif ?>
                    <?php if ($canUpdate && !$isSelf): ?>
                        <?= Html::beginForm(['toggle-active', 'id' => $model->id], 'post') ?>
                        <?= Html::submitButton(
                            $model->active
                                ? '<i class="ph ph-toggle-right me-1"></i>Desactivar'
                                : '<i class="ph ph-toggle-left me-1"></i>Activar',
                            ['class' => 'btn btn-sm ' . ($model->active ? 'btn-outline-warning' : 'btn-outline-success')]
                        ) ?>
                        <?= Html::endForm() ?>
                    <?php endif ?>
                    <?php if ($canDelete): ?>
                        <?= Html::a(
                            '<i class="ph ph-trash me-1"></i>Eliminar',
                            ['delete', 'id' => $model->id],
                            [
                                'class'        => 'btn btn-sm btn-outline-danger',
                                'data-confirm' => "¿Eliminar al usuario '{$model->username}'? Esta acción no se puede deshacer.",
                                'data-method'  => 'post',
                            ]
                        ) ?>
                    <?php endif ?>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted small">ID</dt>
                    <dd class="col-sm-8"><?= $model->id ?></dd>

                    <dt class="col-sm-4 text-muted small">Código</dt>
                    <dd class="col-sm-8"><code><?= Html::encode($model->code) ?></code></dd>

                    <dt class="col-sm-4 text-muted small">Usuario</dt>
                    <dd class="col-sm-8"><?= Html::encode($model->username) ?></dd>

                    <dt class="col-sm-4 text-muted small">Nombre</dt>
                    <dd class="col-sm-8"><?= Html::encode($model->name . ' ' . $model->last_name) ?></dd>

                    <dt class="col-sm-4 text-muted small">Email</dt>
                    <dd class="col-sm-8"><?= $model->email ? Html::mailto($model->email) : '—' ?></dd>

                    <dt class="col-sm-4 text-muted small">Teléfono</dt>
                    <dd class="col-sm-8"><?= Html::encode($model->phone ?? '—') ?></dd>

                    <dt class="col-sm-4 text-muted small">Grupo</dt>
                    <dd class="col-sm-8">
                        <?php if ($model->group): ?>
                            <span class="badge bg-primary-subtle text-primary"><?= Html::encode($model->group->name) ?></span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif ?>
                    </dd>

                    <dt class="col-sm-4 text-muted small">Estado</dt>
                    <dd class="col-sm-8">
                        <?= $model->active
                            ? '<span class="badge bg-success-subtle text-success">Activo</span>'
                            : '<span class="badge bg-danger-subtle text-danger">Inactivo</span>'
                        ?>
                    </dd>

                    <dt class="col-sm-4 text-muted small">Creado</dt>
                    <dd class="col-sm-8 text-muted small"><?= Html::encode($model->created_at) ?></dd>

                    <dt class="col-sm-4 text-muted small">Actualizado</dt>
                    <dd class="col-sm-8 text-muted small"><?= Html::encode($model->updated_at ?? '—') ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Actividad + RBAC -->
    <div class="col-lg-4">

        <!-- Última actividad -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ph ph-activity me-2"></i>Actividad</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-6 text-muted">Último acceso</dt>
                    <dd class="col-6"><?= Html::encode($model->last_date_connection ?? '—') ?></dd>
                    <dt class="col-6 text-muted">Última IP</dt>
                    <dd class="col-6"><?= Html::encode($model->last_ip ?? '—') ?></dd>
                    <dt class="col-6 text-muted">En línea</dt>
                    <dd class="col-6">
                        <?= $model->online
                            ? '<span class="badge bg-success-subtle text-success">Sí</span>'
                            : '<span class="badge bg-secondary-subtle text-secondary">No</span>'
                        ?>
                    </dd>
                    <dt class="col-6 text-muted">Intentos fallidos</dt>
                    <dd class="col-6 <?= $model->failed_login_attempts > 0 ? 'text-danger' : '' ?>">
                        <?= $model->failed_login_attempts ?>
                    </dd>
                    <?php if ($model->blocked_until): ?>
                        <dt class="col-6 text-muted">Bloqueado hasta</dt>
                        <dd class="col-6 text-danger small"><?= Html::encode($model->blocked_until) ?></dd>
                    <?php endif ?>
                </dl>
            </div>
        </div>

        <!-- Roles RBAC -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="ph ph-shield-check me-2"></i>Roles RBAC</h6>
            </div>
            <div class="card-body">
                <?php if (empty($roles)): ?>
                    <p class="text-muted small mb-2">Sin roles asignados.</p>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-1 mb-3">
                        <?php foreach ($roles as $role): ?>
                            <span class="badge bg-primary-subtle text-primary">
                                <i class="ph ph-shield me-1"></i><?= Html::encode($role->name) ?>
                            </span>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>

                <?php if (!empty($permissions)): ?>
                    <p class="small text-muted mb-1">Permisos efectivos (<?= count($permissions) ?>):</p>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($permissions as $perm): ?>
                            <span class="badge bg-light text-secondary border small">
                                <?= Html::encode($perm->name) ?>
                            </span>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>
            </div>
        </div>

    </div>

</div>
