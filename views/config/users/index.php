<?php

use app\assets\DynamicAssetBundle;
use yii\helpers\Url;

DynamicAssetBundle::register($this);

$this->title = 'Administracion de Usuarios y RBAC';

$canRbacManage = Yii::$app->user->can('rbac.manage');
?>

<div class="container-fluid users-admin-page">
    <div class="row g-2 mb-2">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Usuarios activos</small>
                    <h4 class="mb-0" id="kpi-users-active">0</h4>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Usuarios totales</small>
                    <h4 class="mb-0" id="kpi-users-total">0</h4>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Grupos activos</small>
                    <h4 class="mb-0" id="kpi-groups-active">0</h4>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Items RBAC</small>
                    <h4 class="mb-0" id="kpi-rbac-items">0</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white p-0">
            <ul class="nav nav-tabs nav-fill bg-gray-300" id="users-admin-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-users-btn" data-bs-toggle="tab" data-bs-target="#tab-users" type="button" role="tab">Usuarios</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-groups-btn" data-bs-toggle="tab" data-bs-target="#tab-groups" type="button" role="tab">Grupos</button>
                </li>
            </ul>
        </div>
        <div class="card-body tab-content">
            <div class="tab-pane fade show active" id="tab-users" role="tabpanel" aria-labelledby="tab-users-btn">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Usuarios del sistema</h6>
                    <button class="btn btn-sm btn-success" id="btn-add-user"><i class="ti ti-user-plus"></i> Nuevo usuario</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover table-sm align-middle" id="tbl-admin-users" aria-describedby="Usuarios"></table>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-groups" role="tabpanel" aria-labelledby="tab-groups-btn">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Grupos de usuarios</h6>
                    <button class="btn btn-sm btn-primary" id="btn-add-group"><i class="ti ti-users-plus"></i> Nuevo grupo</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover table-sm align-middle" id="tbl-admin-groups" aria-describedby="Grupos"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mdl-user-admin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="mdl-user-title">Nuevo usuario</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="user-id">
                <ul class="nav nav-tabs mb-2" role="tablist">
                    <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#user-tab-general" type="button">General</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#user-tab-rbac" type="button">RBAC usuario</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="user-tab-general">
                        <div class="row g-2">
                            <div class="col-md-3"><label class="form-label">Codigo</label><input class="form-control form-control-sm" id="user-code"></div>
                            <div class="col-md-3"><label class="form-label">Usuario</label><input class="form-control form-control-sm" id="user-username"></div>
                            <div class="col-md-3"><label class="form-label">Nombre</label><input class="form-control form-control-sm" id="user-name"></div>
                            <div class="col-md-3"><label class="form-label">Apellidos</label><input class="form-control form-control-sm" id="user-last-name"></div>
                            <div class="col-md-4"><label class="form-label">Email</label><input class="form-control form-control-sm" id="user-email"></div>
                            <div class="col-md-3"><label class="form-label">Telefono</label><input class="form-control form-control-sm" id="user-phone"></div>
                            <div class="col-md-3"><label class="form-label">Grupo</label><select class="form-select form-select-sm" id="user-group-id"></select></div>
                            <div class="col-md-2"><label class="form-label">Activo</label><select class="form-select form-select-sm" id="user-active"><option value="1">Si</option><option value="0">No</option></select></div>
                            <div class="col-md-4"><label class="form-label">Contraseña (solo al crear o cambiar)</label><input type="password" class="form-control form-control-sm" id="user-password"></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="user-tab-rbac">
                        <div class="alert alert-light border mb-2 p-2">
                            <small>Asigna roles y permisos directos al usuario. Esta configuración sobrescribe la herencia del grupo para el mismo item.</small>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Roles</label>
                                <input class="form-control form-control-sm mb-1" id="user-role-search" placeholder="Filtrar roles...">
                                <div class="border rounded p-2" style="max-height:250px; overflow:auto;" id="user-roles-list"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Permisos</label>
                                <input class="form-control form-control-sm mb-1" id="user-perm-search" placeholder="Filtrar permisos...">
                                <div class="border rounded p-2" style="max-height:250px; overflow:auto;" id="user-perms-list"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btn-save-user-admin">Guardar usuario</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mdl-group-admin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="mdl-group-title">Nuevo grupo</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="group-id">
                <ul class="nav nav-tabs mb-2" role="tablist">
                    <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#group-tab-general" type="button">General</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#group-tab-rbac" type="button">RBAC grupo</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="group-tab-general">
                        <div class="row g-2">
                            <div class="col-md-4"><label class="form-label">Nombre</label><input class="form-control form-control-sm" id="group-name"></div>
                            <div class="col-md-6"><label class="form-label">Descripcion</label><input class="form-control form-control-sm" id="group-description"></div>
                            <div class="col-md-2"><label class="form-label">Activo</label><select class="form-select form-select-sm" id="group-active"><option value="1">Si</option><option value="0">No</option></select></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="group-tab-rbac">
                        <div class="alert alert-light border mb-2 p-2">
                            <small>Al guardar RBAC de grupo, se sincroniza con todos los usuarios asociados actualmente al grupo.</small>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Roles</label>
                                <input class="form-control form-control-sm mb-1" id="group-role-search" placeholder="Filtrar roles...">
                                <div class="border rounded p-2" style="max-height:250px; overflow:auto;" id="group-roles-list"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Permisos</label>
                                <input class="form-control form-control-sm mb-1" id="group-perm-search" placeholder="Filtrar permisos...">
                                <div class="border rounded p-2" style="max-height:250px; overflow:auto;" id="group-perms-list"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-save-group-admin">Guardar grupo</button>
            </div>
        </div>
    </div>
</div>

<script>
window.usersAdminConfig = {
    listUsers: '<?= Url::to(['users/list-ajax']) ?>',
    getUser: '<?= Url::to(['users/get-ajax']) ?>',
    saveUser: '<?= Url::to(['users/save-ajax']) ?>',
    deleteUser: '<?= Url::to(['users/delete-ajax']) ?>',
    listGroups: '<?= Url::to(['users/groups-list-ajax']) ?>',
    getGroup: '<?= Url::to(['users/group-get-ajax']) ?>',
    saveGroup: '<?= Url::to(['users/group-save-ajax']) ?>',
    deleteGroup: '<?= Url::to(['users/group-delete-ajax']) ?>',
    rbacCatalog: '<?= Url::to(['users/rbac-catalog-ajax']) ?>',
    assignments: '<?= Url::to(['users/assignments-ajax']) ?>',
    saveAssignments: '<?= Url::to(['users/save-assignments-ajax']) ?>',
    canRbacManage: <?= $canRbacManage ? 'true' : 'false' ?>
};
</script>
