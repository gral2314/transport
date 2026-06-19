(function () {
    'use strict';

    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    var cfg = window.usersAdminConfig || {};

    var state = {
        usersTable: null,
        groupsTable: null,
        rbacCatalog: { roles: [], permissions: [] },
        userAssigned: [],
        groupAssigned: [],
    };

    function notify(msg, type) {
        type = type || 'info';
        if (typeof toastr !== 'undefined') toastr[type](msg);
        else alert(msg);
    }

    function readCsrf() {
        var csrfParam = $('meta[name="csrf-param"]').attr('content');
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var out = {};
        if (csrfParam && csrfToken) out[csrfParam] = csrfToken;
        return out;
    }

    function post(url, data) {
        var payload = $.extend({}, readCsrf(), data || {});
        return $.ajax({ url: url, type: 'POST', dataType: 'json', data: payload });
    }

    function get(url, data) {
        return $.ajax({ url: url, type: 'GET', dataType: 'json', data: data || {} });
    }

    function countKpis() {
        var users = state.usersTable ? state.usersTable.rows().data().toArray() : [];
        var groups = state.groupsTable ? state.groupsTable.rows().data().toArray() : [];
        var activeUsers = users.filter(function (u) { return String(u.active) === '1'; }).length;
        var activeGroups = groups.filter(function (g) { return String(g.active) === '1'; }).length;

        $('#kpi-users-total').text(users.length);
        $('#kpi-users-active').text(activeUsers);
        $('#kpi-groups-active').text(activeGroups);
        $('#kpi-rbac-items').text((state.rbacCatalog.roles || []).length + (state.rbacCatalog.permissions || []).length);
    }

    function initUsersTable() {
        state.usersTable = $('#tbl-admin-users').DataTable({
            language: { url: Urlhome + 'plugins/datatablesnet/i18n/Spanish.json' },
            dom: 'frtip',
            paging: true,
            pageLength: 10,
            ajax: {
                url: cfg.listUsers,
                dataSrc: function (resp) { return resp && resp.Success === 'Ok' ? (resp.Data || []) : []; }
            },
            columns: [
                { data: 'id', title: '#', width: '60px', className: 'text-center' },
                { data: 'username', title: 'Usuario' },
                { data: 'name', title: 'Nombre' },
                { data: 'email', title: 'Email' },
                { data: 'group_name', title: 'Grupo', defaultContent: '-' },
                {
                    data: 'active',
                    title: 'Activo',
                    className: 'text-center',
                    render: function (d) { return String(d) === '1' ? '<span class="badge bg-success">Si</span>' : '<span class="badge bg-danger">No</span>'; }
                },
                {
                    data: null,
                    title: 'Acciones',
                    className: 'text-center',
                    orderable: false,
                    render: function (_, __, row) {
                        return '' +
                            '<button class="btn btn-sm btn-outline-primary me-1 btn-user-edit" data-id="' + row.id + '" title="Editar"><i class="ti ti-pencil"></i></button>' +
                            '<button class="btn btn-sm btn-outline-danger btn-user-delete" data-id="' + row.id + '" title="Eliminar"><i class="ti ti-trash"></i></button>';
                    }
                }
            ],
            drawCallback: countKpis,
        });
    }

    function initGroupsTable() {
        state.groupsTable = $('#tbl-admin-groups').DataTable({
            language: { url: Urlhome + 'plugins/datatablesnet/i18n/Spanish.json' },
            dom: 'frtip',
            paging: true,
            pageLength: 10,
            ajax: {
                url: cfg.listGroups,
                dataSrc: function (resp) { return resp && resp.Success === 'Ok' ? (resp.Data || []) : []; }
            },
            columns: [
                { data: 'id', title: '#', width: '60px', className: 'text-center' },
                { data: 'name', title: 'Grupo' },
                { data: 'description', title: 'Descripcion', defaultContent: '-' },
                { data: 'users_count', title: 'Usuarios', className: 'text-center', width: '90px' },
                {
                    data: 'active',
                    title: 'Activo',
                    className: 'text-center',
                    render: function (d) { return String(d) === '1' ? '<span class="badge bg-success">Si</span>' : '<span class="badge bg-danger">No</span>'; }
                },
                {
                    data: null,
                    title: 'Acciones',
                    className: 'text-center',
                    orderable: false,
                    render: function (_, __, row) {
                        return '' +
                            '<button class="btn btn-sm btn-outline-primary me-1 btn-group-edit" data-id="' + row.id + '" title="Editar"><i class="ti ti-pencil"></i></button>' +
                            '<button class="btn btn-sm btn-outline-danger btn-group-delete" data-id="' + row.id + '" title="Eliminar"><i class="ti ti-trash"></i></button>';
                    }
                }
            ],
            drawCallback: countKpis,
        });
    }

    function loadGroupOptions(selected) {
        get(cfg.listGroups).done(function (resp) {
            var rows = resp && resp.Success === 'Ok' ? (resp.Data || []) : [];
            var $sel = $('#user-group-id');
            $sel.empty();
            $sel.append($('<option>').val('').text('-- Sin grupo --'));
            rows.forEach(function (g) {
                if (String(g.active) === '1') {
                    $sel.append($('<option>').val(g.id).text(g.name));
                }
            });
            if (selected !== undefined && selected !== null) {
                $sel.val(String(selected));
            }
        });
    }

    function loadRbacCatalog() {
        return get(cfg.rbacCatalog).done(function (resp) {
            if (resp && resp.Success === 'Ok') {
                state.rbacCatalog = resp.Data || { roles: [], permissions: [] };
            }
            countKpis();
        });
    }

    function renderCheckList(containerId, rows, selectedSet, searchTerm) {
        var term = (searchTerm || '').toLowerCase();
        var html = '';
        rows.forEach(function (r) {
            var label = String(r.name || '');
            var desc = String(r.description || '');
            if (term && label.toLowerCase().indexOf(term) === -1 && desc.toLowerCase().indexOf(term) === -1) return;
            var checked = selectedSet.has(label) ? 'checked' : '';
            html += '<div class="form-check mb-1">' +
                '<input class="form-check-input rbac-item-check" type="checkbox" value="' + label + '" id="rb-' + containerId + '-' + label.replace(/[^a-zA-Z0-9_-]/g, '-') + '" ' + checked + '>' +
                '<label class="form-check-label" for="rb-' + containerId + '-' + label.replace(/[^a-zA-Z0-9_-]/g, '-') + '">' +
                '<strong>' + label + '</strong>' + (desc ? '<br><small class="text-muted">' + desc + '</small>' : '') +
                '</label></div>';
        });
        $('#' + containerId).html(html || '<small class="text-muted">Sin resultados</small>');
        $('#' + containerId + ' .rbac-item-check').prop('disabled', !cfg.canRbacManage);
    }

    function renderUserRbacLists() {
        var selected = new Set(state.userAssigned || []);
        renderCheckList('user-roles-list', state.rbacCatalog.roles || [], selected, $('#user-role-search').val());
        renderCheckList('user-perms-list', state.rbacCatalog.permissions || [], selected, $('#user-perm-search').val());
    }

    function renderGroupRbacLists() {
        var selected = new Set(state.groupAssigned || []);
        renderCheckList('group-roles-list', state.rbacCatalog.roles || [], selected, $('#group-role-search').val());
        renderCheckList('group-perms-list', state.rbacCatalog.permissions || [], selected, $('#group-perm-search').val());
    }

    function collectSelectedRbac(scope) {
        var out = [];
        $('#' + scope + '-roles-list .rbac-item-check:checked').each(function () { out.push($(this).val()); });
        $('#' + scope + '-perms-list .rbac-item-check:checked').each(function () { out.push($(this).val()); });
        return Array.from(new Set(out));
    }

    function clearUserModal() {
        $('#user-id').val('');
        $('#user-code').val('');
        $('#user-username').val('');
        $('#user-name').val('');
        $('#user-last-name').val('');
        $('#user-email').val('');
        $('#user-phone').val('');
        $('#user-active').val('1');
        $('#user-password').val('');
        state.userAssigned = [];
        loadGroupOptions('');
        renderUserRbacLists();
        $('#mdl-user-title').text('Nuevo usuario');
    }

    function clearGroupModal() {
        $('#group-id').val('');
        $('#group-name').val('');
        $('#group-description').val('');
        $('#group-active').val('1');
        state.groupAssigned = [];
        renderGroupRbacLists();
        $('#mdl-group-title').text('Nuevo grupo');
    }

    function openUserModal(id) {
        clearUserModal();
        if (!id) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('mdl-user-admin')).show();
            return;
        }

        get(cfg.getUser, { id: id }).done(function (resp) {
            if (!resp || resp.Success !== 'Ok') {
                notify(resp && resp.Msg ? resp.Msg : 'No se pudo cargar usuario', 'error');
                return;
            }
            var d = resp.Data || {};
            $('#user-id').val(d.id || '');
            $('#user-code').val(d.code || '');
            $('#user-username').val(d.username || '');
            $('#user-name').val(d.name || '');
            $('#user-last-name').val(d.last_name || '');
            $('#user-email').val(d.email || '');
            $('#user-phone').val(d.phone || '');
            $('#user-active').val(String(d.active || '1'));
            loadGroupOptions(d.group_id || '');
            $('#mdl-user-title').text('Editar usuario #' + d.id);

            get(cfg.assignments, { subjectType: 'user', id: d.id }).done(function (aResp) {
                state.userAssigned = aResp && aResp.Success === 'Ok' ? (aResp.Data.items || []) : [];
                renderUserRbacLists();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('mdl-user-admin')).show();
            });
        });
    }

    function openGroupModal(id) {
        clearGroupModal();
        if (!id) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('mdl-group-admin')).show();
            return;
        }

        get(cfg.getGroup, { id: id }).done(function (resp) {
            if (!resp || resp.Success !== 'Ok') {
                notify(resp && resp.Msg ? resp.Msg : 'No se pudo cargar grupo', 'error');
                return;
            }
            var d = resp.Data || {};
            $('#group-id').val(d.id || '');
            $('#group-name').val(d.name || '');
            $('#group-description').val(d.description || '');
            $('#group-active').val(String(d.active || '1'));
            $('#mdl-group-title').text('Editar grupo #' + d.id);

            get(cfg.assignments, { subjectType: 'group', id: d.id }).done(function (aResp) {
                state.groupAssigned = aResp && aResp.Success === 'Ok' ? (aResp.Data.items || []) : [];
                renderGroupRbacLists();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('mdl-group-admin')).show();
            });
        });
    }

    function bindEvents() {
        $('#btn-add-user').on('click', function () { openUserModal(null); });
        $('#btn-add-group').on('click', function () { openGroupModal(null); });

        $('#tbl-admin-users').on('click', '.btn-user-edit', function () {
            openUserModal($(this).data('id'));
        });

        $('#tbl-admin-users').on('click', '.btn-user-delete', function () {
            var id = $(this).data('id');
            if (!confirm('¿Eliminar usuario?')) return;
            post(cfg.deleteUser, { id: id }).done(function (resp) {
                if (resp && resp.Success === 'Ok') {
                    notify(resp.Msg || 'Usuario eliminado', 'success');
                    state.usersTable.ajax.reload(null, false);
                } else {
                    notify(resp && resp.Msg ? resp.Msg : 'Error al eliminar', 'error');
                }
            });
        });

        $('#tbl-admin-groups').on('click', '.btn-group-edit', function () {
            openGroupModal($(this).data('id'));
        });

        $('#tbl-admin-groups').on('click', '.btn-group-delete', function () {
            var id = $(this).data('id');
            if (!confirm('¿Eliminar grupo?')) return;
            post(cfg.deleteGroup, { id: id }).done(function (resp) {
                if (resp && resp.Success === 'Ok') {
                    notify(resp.Msg || 'Grupo eliminado', 'success');
                    state.groupsTable.ajax.reload(null, false);
                    state.usersTable.ajax.reload(null, false);
                } else {
                    notify(resp && resp.Msg ? resp.Msg : 'Error al eliminar', 'error');
                }
            });
        });

        $('#btn-save-user-admin').on('click', function () {
            var id = $('#user-id').val();
            var payload = {
                id: id,
                code: $('#user-code').val(),
                username: $('#user-username').val(),
                name: $('#user-name').val(),
                last_name: $('#user-last-name').val(),
                email: $('#user-email').val(),
                phone: $('#user-phone').val(),
                group_id: $('#user-group-id').val(),
                active: $('#user-active').val(),
                password: $('#user-password').val(),
            };

            post(cfg.saveUser, payload).done(function (resp) {
                if (!resp || resp.Success !== 'Ok') {
                    notify(resp && resp.Msg ? resp.Msg : 'No se pudo guardar usuario', 'error');
                    return;
                }

                var savedId = id || (resp.Data && resp.Data.id ? resp.Data.id : '');
                if (cfg.canRbacManage && savedId) {
                    var items = collectSelectedRbac('user');
                    post(cfg.saveAssignments, { subjectType: 'user', id: savedId, items: items }).done(function (ar) {
                        if (ar && ar.Success === 'Ok') {
                            notify('Usuario y RBAC guardados', 'success');
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('mdl-user-admin')).hide();
                            state.usersTable.ajax.reload(null, false);
                        } else {
                            notify(ar && ar.Msg ? ar.Msg : 'Usuario guardado, error en RBAC', 'warning');
                        }
                    });
                } else {
                    notify(resp.Msg || 'Usuario guardado', 'success');
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('mdl-user-admin')).hide();
                    state.usersTable.ajax.reload(null, false);
                }
            });
        });

        $('#btn-save-group-admin').on('click', function () {
            var id = $('#group-id').val();
            var payload = {
                id: id,
                name: $('#group-name').val(),
                description: $('#group-description').val(),
                active: $('#group-active').val(),
            };

            post(cfg.saveGroup, payload).done(function (resp) {
                if (!resp || resp.Success !== 'Ok') {
                    notify(resp && resp.Msg ? resp.Msg : 'No se pudo guardar grupo', 'error');
                    return;
                }

                var savedId = id || (resp.Data && resp.Data.id ? resp.Data.id : '');
                if (cfg.canRbacManage && savedId) {
                    var items = collectSelectedRbac('group');
                    post(cfg.saveAssignments, { subjectType: 'group', id: savedId, items: items }).done(function (ar) {
                        if (ar && ar.Success === 'Ok') {
                            notify('Grupo y RBAC guardados', 'success');
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('mdl-group-admin')).hide();
                            state.groupsTable.ajax.reload(null, false);
                            state.usersTable.ajax.reload(null, false);
                        } else {
                            notify(ar && ar.Msg ? ar.Msg : 'Grupo guardado, error en RBAC', 'warning');
                        }
                    });
                } else {
                    notify(resp.Msg || 'Grupo guardado', 'success');
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('mdl-group-admin')).hide();
                    state.groupsTable.ajax.reload(null, false);
                }
            });
        });

        $('#user-role-search, #user-perm-search').on('input', renderUserRbacLists);
        $('#group-role-search, #group-perm-search').on('input', renderGroupRbacLists);
    }

    $(function () {
        $.when(loadRbacCatalog()).always(function () {
            initUsersTable();
            initGroupsTable();
            bindEvents();
        });
    });
})();
