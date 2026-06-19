<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\system\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\system\User;
use app\models\system\UserGroup;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Usuarios';
$this->params['breadcrumbs'][] = $this->title;

$canCreate = Yii::$app->user->can('users.create');
$canUpdate = Yii::$app->user->can('users.update');
$canDelete = Yii::$app->user->can('users.delete');

$groups = UserGroup::find()->where(['active' => 1])->select(['name', 'id'])->indexBy('id')->column();
?>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="mb-0"><i class="ph ph-users me-2"></i><?= Html::encode($this->title) ?></h5>
        <div class="d-flex gap-2 align-items-center">
            <!-- Búsqueda rápida -->
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => ['users/index'],
                'fieldConfig' => ['template' => '{input}'],
                'options' => ['class' => 'd-flex gap-2'],
            ]) ?>
                <?= $form->field($searchModel, 'search', [
                    'inputOptions' => [
                        'class' => 'form-control form-control-sm',
                        'placeholder' => 'Buscar usuario...',
                        'style' => 'min-width:200px',
                    ],
                ]) ?>
                <?= $form->field($searchModel, 'active', [
                    'inputOptions' => ['class' => 'form-select form-select-sm'],
                ])->dropDownList([''=>'Todos', '1'=>'Activos', '0'=>'Inactivos']) ?>
                <?= Html::submitButton('<i class="ph ph-magnifying-glass"></i>', ['class' => 'btn btn-outline-secondary btn-sm']) ?>
            <?php ActiveForm::end() ?>

            <?php if ($canCreate): ?>
                <?= Html::a('<i class="ph ph-plus me-1"></i>Nuevo Usuario', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
            <?php endif ?>
        </div>
    </div>

    <div class="card-body p-0">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-hover table-sm mb-0'],
            'layout'       => "{items}\n{pager}",
            'emptyText'    => '<div class="text-center text-muted p-4"><i class="ph ph-users f-30 d-block mb-2 opacity-50"></i>Sin usuarios</div>',
            'columns'      => [
                [
                    'attribute'     => 'id',
                    'headerOptions' => ['style' => 'width:60px'],
                    'contentOptions'=> ['class' => 'text-muted small ps-3'],
                    'header'        => '#',
                ],
                [
                    'attribute' => 'username',
                    'label'     => 'Usuario',
                    'value'     => function (User $m) {
                        return $m->username;
                    },
                ],
                [
                    'label' => 'Nombre completo',
                    'value' => fn(User $m) => trim($m->name . ' ' . $m->last_name),
                ],
                [
                    'attribute' => 'email',
                    'value'     => fn(User $m) => $m->email ?? '—',
                ],
                [
                    'attribute' => 'group_id',
                    'label'     => 'Grupo',
                    'value'     => fn(User $m) => $m->group?->name ?? '—',
                    'filter'    => Html::activeDropDownList(
                        $searchModel,
                        'group_id',
                        $groups,
                        ['prompt' => 'Todos', 'class' => 'form-select form-select-sm']
                    ),
                ],
                [
                    'attribute'     => 'active',
                    'label'         => 'Estado',
                    'format'        => 'raw',
                    'headerOptions' => ['style' => 'width:90px; text-align:center'],
                    'contentOptions'=> ['class' => 'text-center'],
                    'value'         => fn(User $m) => $m->active
                        ? '<span class="badge bg-success-subtle text-success">Activo</span>'
                        : '<span class="badge bg-danger-subtle text-danger">Inactivo</span>',
                    'filter'        => Html::activeDropDownList(
                        $searchModel,
                        'active',
                        ['1' => 'Activo', '0' => 'Inactivo'],
                        ['prompt' => 'Todos', 'class' => 'form-select form-select-sm']
                    ),
                ],
                [
                    'attribute'     => 'last_date_connection',
                    'label'         => 'Último acceso',
                    'value'         => fn(User $m) => $m->last_date_connection ?? '—',
                    'contentOptions'=> ['class' => 'text-muted small'],
                ],
                [
                    'class'         => ActionColumn::class,
                    'header'        => 'Acciones',
                    'headerOptions' => ['style' => 'width:120px; text-align:center'],
                    'contentOptions'=> ['class' => 'text-center pe-3'],
                    'template'      => '{view}' . ($canUpdate ? ' {update}' : '') . ($canDelete ? ' {delete}' : ''),
                    'buttons'       => [
                        'view'   => fn($url, User $m) => Html::a(
                            '<i class="ph ph-eye"></i>',
                            ['view', 'id' => $m->id],
                            ['class' => 'btn btn-sm btn-outline-info', 'title' => 'Ver']
                        ),
                        'update' => fn($url, User $m) => Html::a(
                            '<i class="ph ph-pencil"></i>',
                            ['update', 'id' => $m->id],
                            ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Editar']
                        ),
                        'delete' => fn($url, User $m) => ($m->id === (int) Yii::$app->user->id)
                            ? ''
                            : Html::a(
                                '<i class="ph ph-trash"></i>',
                                ['delete', 'id' => $m->id],
                                [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'title' => 'Eliminar',
                                    'data-confirm' => "¿Eliminar al usuario '{$m->username}'?",
                                    'data-method'  => 'post',
                                ]
                            ),
                    ],
                ],
            ],
        ]) ?>
    </div>
</div>
