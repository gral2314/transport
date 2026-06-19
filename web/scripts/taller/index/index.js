/**
 * Taller Dashboard — index (mobile-first)
 * Cards en lugar de DataTable, filtrado por técnico logueado.
 */
var currentStatusFilter = 'RELEASED';
var currentPage = 1;
var currentPagination = null;
var allOrdenes = [];

/**
 * Escapa HTML para evitar XSS.
 */
function escHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(String(str)));
    return div.innerHTML;
}

/**
 * Renderiza badge de estado.
 */
function renderStatusBadge(status) {
    var map = {
        'PLANNED': ['badge-info', 'Planeada'],
        'RELEASED': ['badge-primary', 'Liberada'],
        'IN_PROGRESS': ['badge-warning', 'En Proceso'],
        'PENDING_VALIDATION': ['badge-secondary', 'Pend. Validar'],
        'CLOSED': ['badge-success', 'Cerrada'],
        'CANCELLED': ['badge-danger', 'Cancelada']
    };
    var m = map[status] || ['badge-secondary', status];
    return '<span class="badge ' + m[0] + '">' + m[1] + '</span>';
}

/**
 * Renderiza una card de orden.
 */
function renderCard(orden) {
    var docentry = orden.docentry || 0;
    var status = orden.status || '';
    var docnum = escHtml(orden.docnum || '');
    var docdate = escHtml(orden.doc_date || '');
    var origin = escHtml(orden.origin_type || '');
    var comments = escHtml(orden.comments || '');
    var technician = escHtml(orden.technician_username || '');
    var priority = escHtml(orden.priority || '');
    var statusBadge = renderStatusBadge(status);

    var showStart = (status === 'RELEASED') ? '' : ' style="display:none;"';
    var showWork = (status === 'IN_PROGRESS') ? '' : ' style="display:none;"';
    var showValidate = (status === 'PENDING_VALIDATION') ? '' : ' style="display:none;"';

    var html = '<div class="col-12 col-sm-6 col-lg-4 mb-3">';
    html += '<div class="card card-orden h-100 shadow-sm">';
    html += '<div class="card-body p-3">';

    // Header: folio + status
    html += '<div class="d-flex justify-content-between align-items-start mb-2">';
    html += '<h6 class="card-title mb-0 font-weight-bold">' + docnum + '</h6>';
    html += statusBadge;
    html += '</div>';

    // Info rows
    html += '<div class="small text-muted">';
    html += '<div class="mb-1"><i class="fa-regular fa-calendar mr-1"></i> ' + docdate + '</div>';
    html += '<div class="mb-1"><i class="fa-solid fa-tag mr-1"></i> ' + origin + '</div>';
    html += '<div class="mb-1"><i class="fa-solid fa-user mr-1"></i> ' + technician + '</div>';
    if (priority) {
        html += '<div class="mb-1"><i class="fa-solid fa-flag mr-1"></i> ' + priority + '</div>';
    }
    if (comments) {
        html += '<div class="mb-1 text-truncate"><i class="fa-regular fa-comment mr-1"></i> ' + comments + '</div>';
    }
    html += '</div>';

    // Actions
    html += '<div class="mt-3 d-flex gap-2">';
    html += '<button class="btn btn-sm btn-outline-info flex-fill" onclick="openMovementDetail(' + docentry + ')" title="Vista rápida">';
    html += '<i class="fa-solid fa-eye"></i> Ver detalle</button>';
    html += '<button class="btn btn-sm btn-success flex-fill btn-start-order"' + showStart + ' data-docentry="' + docentry + '" title="Iniciar orden">';
    html += '<i class="fa-solid fa-play"></i> Iniciar</button>';
    html += '<a class="btn btn-sm btn-warning flex-fill"' + showWork + ' href="' + window.TallerUrls.workOrder + '?id=' + docentry + '" title="Trabajar">';
    html += '<i class="fa-solid fa-wrench"></i> Trabajar</a>';
    html += '<button class="btn btn-sm btn-success flex-fill btn-validate-order"' + showValidate + ' data-docentry="' + docentry + '" title="Validar orden">';
    html += '<i class="fa-solid fa-check-double"></i> Validar</button>';
    html += '</div>';

    html += '</div></div></div>';
    return html;
}

/**
 * Carga y renderiza las cards.
 */
function loadOrdenes() {
    $('#loading-ordenes').show();
    $('#ordenes-cards-container').hide();

    $.ajax({
        url: window.TallerUrls.list,
        type: 'GET',
        data: {
            status: currentStatusFilter,
            search: $('#filter-search').val(),
            page: currentPage,
            per_page: 12
        },
        dataType: 'json'
    }).done(function (response) {
        if (response.Success !== 'Ok') {
            Toast.fire({ icon: 'error', title: response.Msg || 'Error al cargar órdenes.' });
            allOrdenes = [];
            currentPagination = null;
        } else {
            if (response.Data && Array.isArray(response.Data.items)) {
                allOrdenes = response.Data.items;
                currentPagination = response.Data.pagination || null;
            } else {
                allOrdenes = response.Data || [];
                currentPagination = null;
            }
        }
        renderOrdenes();
    }).fail(function () {
        Toast.fire({ icon: 'error', title: 'Error de conexión al cargar órdenes.' });
        allOrdenes = [];
        currentPagination = null;
        renderOrdenes();
    });
}

/**
 * Renderiza las cards en el contenedor.
 */
function renderOrdenes() {
    $('#loading-ordenes').hide();

    if (allOrdenes.length === 0) {
        $('#ordenes-cards').empty();
        $('#sin-ordenes').show();
        $('#ordenes-cards-container').show();
        $('#orden-count').text('0');
        $('#taller-pagination').empty();
        return;
    }

    $('#sin-ordenes').hide();
    $('#orden-count').text(currentPagination ? currentPagination.totalCount : allOrdenes.length);

    var html = '';
    $.each(allOrdenes, function (i, orden) {
        html += renderCard(orden);
    });
    $('#ordenes-cards').html(html);
    $('#ordenes-cards-container').show();
    renderTallerPagination();
}

/**
 * Abre modal con detalle de la orden (vista rápida).
 */
function openMovementDetail(docentry) {
    if (!docentry) return;

    // Limpiar modal
    $('#mov-docnum').text('-');
    $('#mov-status-badge').text('').removeClass().addClass('badge');
    $('#mov-priority').text('-');
    $('#mov-doc-date').text('-');
    $('#mov-technician').text('-');
    $('#mov-origin').text('-');
    $('#mov-comments').text('-');
    $('#mov-released-at').text('-');
    $('#mov-started-at').text('-');
    $('#mov-completed-at').text('-');
    $('#mov-validated-at').text('-');
    $('#mov-cancelled-at').text('-');
    $('#tbl-mov-vehicles tbody').empty();
    $('#tbl-mov-details tbody').empty();
    $('#btn-mov-start, #btn-mov-finish, #btn-mov-validate, #btn-mov-cancel').addClass('d-none');

    $.ajax({
        url: window.TallerUrls.get,
        type: 'GET',
        data: { docentry: docentry },
        dataType: 'json'
    })
    .done(function (response) {
        if (response.Success !== 'Ok') {
            return Toast.fire({ icon: 'error', title: response.Msg || 'Error al cargar detalle.' });
        }
        var d = response.Data || {};

        // Cabecera
        $('#mov-docnum').text(escHtml(d.docnum));
        $('#mov-status-badge').html(renderStatusBadge(d.status));
        $('#mov-priority').text(escHtml(d.priority));
        $('#mov-doc-date').text(escHtml(d.doc_date));
        $('#mov-technician').text(d.technicianUser ? escHtml(d.technicianUser.username) : '-');
        $('#mov-origin').text(escHtml(d.origin_type));
        $('#mov-comments').text(escHtml(d.comments));

        // Timeline
        $('#mov-released-at').text(escHtml(d.released_at) || '-');
        $('#mov-started-at').text(escHtml(d.started_at) || '-');
        $('#mov-completed-at').text(escHtml(d.completed_at) || '-');
        $('#mov-validated-at').text(escHtml(d.validated_at) || '-');
        $('#mov-cancelled-at').text(escHtml(d.cancelled_at) || '-');

        // Unidades
        var vehicles = d.vehicles || [];
        var vhtml = '';
        $.each(vehicles, function (i, v) {
            vhtml += '<tr><td>' + (i + 1) + '</td><td>' + escHtml(v.vehicle_name || v.vehicle_id) + '</td></tr>';
        });
        $('#tbl-mov-vehicles tbody').html(vhtml || '<tr><td colspan="2" class="text-muted">Sin unidades</td></tr>');

        // Detalle
        var details = d.details || [];
        var dhtml = '';
        $.each(details, function (i, det) {
            dhtml += '<tr><td>' + (i + 1) + '</td>' +
                '<td>' + escHtml(det.tire_code || det.tire_id) + '</td>' +
                '<td>' + escHtml(det.movement_type) + '</td>' +
                '<td>' + escHtml(det.origin_type) + '</td>' +
                '<td>' + escHtml(det.destination) + '</td></tr>';
        });
        $('#tbl-mov-details tbody').html(dhtml || '<tr><td colspan="5" class="text-muted">Sin detalle</td></tr>');

        // Botones de acción según estado
        var status = d.status || '';
        if (status === 'RELEASED') {
            $('#btn-mov-start').removeClass('d-none').data('docentry', docentry);
            $('#btn-mov-cancel').removeClass('d-none').data('docentry', docentry);
        } else if (status === 'IN_PROGRESS') {
            $('#btn-mov-finish').removeClass('d-none').data('docentry', docentry);
            $('#btn-mov-cancel').removeClass('d-none').data('docentry', docentry);
        } else if (status === 'PENDING_VALIDATION') {
            $('#btn-mov-validate').removeClass('d-none').data('docentry', docentry);
            $('#btn-mov-cancel').removeClass('d-none').data('docentry', docentry);
        }

        // Mostrar modal
        $('#mdl-taller-movement').modal('show');
    })
    .fail(function () {
        Toast.fire({ icon: 'error', title: 'Error de conexión al cargar detalle.' });
    });
}

// ── Acciones del modal ──────────────────────────────────────────────────

$(document).on('click', '#btn-mov-start', function () {
    var docentry = $(this).data('docentry');
    if (!docentry) return;

    Swal.fire({
        title: '¿Iniciar orden?',
        text: 'La orden pasará a estado "En Proceso".',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, iniciar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: window.TallerUrls.start,
            type: 'POST',
            data: { docentry: docentry },
            dataType: 'json'
        })
        .done(function (response) {
            if (response.Success === 'Ok') {
                Toast.fire({ icon: 'success', title: 'Orden iniciada.' });
                $('#mdl-taller-movement').modal('hide');
                window.location.href = window.TallerUrls.workOrder + '?id=' + docentry;
            } else {
                Toast.fire({ icon: 'error', title: response.Msg || 'Error al iniciar.' });
            }
        })
        .fail(function () {
            Toast.fire({ icon: 'error', title: 'Error de conexión.' });
        });
    });
});

$(document).on('click', '#btn-mov-finish', function () {
    var docentry = $(this).data('docentry');
    if (!docentry) return;

    Swal.fire({
        title: '¿Finalizar orden?',
        text: 'La orden pasará a "Pendiente de Validación".',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: window.TallerUrls.finish,
            type: 'POST',
            data: { docentry: docentry },
            dataType: 'json'
        })
        .done(function (response) {
            if (response.Success === 'Ok') {
                Toast.fire({ icon: 'success', title: 'Orden finalizada.' });
                $('#mdl-taller-movement').modal('hide');
                loadOrdenes();
            } else {
                Toast.fire({ icon: 'error', title: response.Msg || 'Error al finalizar.' });
            }
        })
        .fail(function () {
            Toast.fire({ icon: 'error', title: 'Error de conexión.' });
        });
    });
});

$(document).on('click', '#btn-mov-validate', function () {
    var docentry = $(this).data('docentry');
    if (!docentry) return;

    Swal.fire({
        title: '¿Validar orden?',
        text: 'La orden se cerrará definitivamente.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, validar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: window.TallerUrls.validate,
            type: 'POST',
            data: { docentry: docentry, validated_by_user_id: window.currentUserId || 0 },
            dataType: 'json'
        })
        .done(function (response) {
            if (response.Success === 'Ok') {
                Toast.fire({ icon: 'success', title: 'Orden validada.' });
                $('#mdl-taller-movement').modal('hide');
                loadOrdenes();
            } else {
                Toast.fire({ icon: 'error', title: response.Msg || 'Error al validar.' });
            }
        })
        .fail(function () {
            Toast.fire({ icon: 'error', title: 'Error de conexión.' });
        });
    });
});

$(document).on('click', '#btn-mov-cancel', function () {
    var docentry = $(this).data('docentry');
    if (!docentry) return;

    Swal.fire({
        title: '¿Cancelar orden?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'Volver'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: window.TallerUrls.cancel,
            type: 'POST',
            data: { docentry: docentry },
            dataType: 'json'
        })
        .done(function (response) {
            if (response.Success === 'Ok') {
                Toast.fire({ icon: 'success', title: 'Orden cancelada.' });
                $('#mdl-taller-movement').modal('hide');
                loadOrdenes();
            } else {
                Toast.fire({ icon: 'error', title: response.Msg || 'Error al cancelar.' });
            }
        })
        .fail(function () {
            Toast.fire({ icon: 'error', title: 'Error de conexión.' });
        });
    });
});

/**
 * Acción rápida: cancelar desde card (release no aplica para técnico).
 */
function quickAction(docentry, action) {
    if (!docentry || action !== 'cancel') return;

    Swal.fire({
        title: '¿Cancelar orden?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'Volver'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: window.TallerUrls.cancel,
            type: 'POST',
            data: { docentry: docentry },
            dataType: 'json'
        })
        .done(function (response) {
            if (response.Success === 'Ok') {
                Toast.fire({ icon: 'success', title: 'Orden cancelada.' });
                loadOrdenes();
            } else {
                Toast.fire({ icon: 'error', title: response.Msg || 'Error al cancelar.' });
            }
        })
        .fail(function () {
            Toast.fire({ icon: 'error', title: 'Error de conexión.' });
        });
    });
}

// ── Botón "Iniciar" en cards (delegado) ─────────────────────────────────

$(document).on('click', '.btn-start-order', function () {
    var docentry = $(this).data('docentry');
    if (!docentry) return;

    Swal.fire({
        title: '¿Iniciar orden?',
        text: 'La orden pasará a estado "En Proceso" y serás redirigido.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, iniciar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: window.TallerUrls.start,
            type: 'POST',
            data: { docentry: docentry },
            dataType: 'json'
        })
        .done(function (response) {
            if (response.Success === 'Ok') {
                Toast.fire({ icon: 'success', title: 'Orden iniciada.' });
                window.location.href = window.TallerUrls.workOrder + '?id=' + docentry;
            } else {
                Toast.fire({ icon: 'error', title: response.Msg || 'Error al iniciar.' });
            }
        })
        .fail(function () {
            Toast.fire({ icon: 'error', title: 'Error de conexión.' });
        });
    });
});

// ── Botón "Validar" en cards (delegado) ─────────────────────────────────

$(document).on('click', '.btn-validate-order', function () {
    var docentry = $(this).data('docentry');
    if (!docentry) return;

    Swal.fire({
        title: '¿Validar orden?',
        text: 'La orden se cerrará definitivamente.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, validar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: window.TallerUrls.validate,
            type: 'POST',
            data: { docentry: docentry, validated_by_user_id: window.currentUserId || 0 },
            dataType: 'json'
        })
        .done(function (response) {
            if (response.Success === 'Ok') {
                Toast.fire({ icon: 'success', title: 'Orden validada.' });
                loadOrdenes();
            } else {
                Toast.fire({ icon: 'error', title: response.Msg || 'Error al validar.' });
            }
        })
        .fail(function () {
            Toast.fire({ icon: 'error', title: 'Error de conexión.' });
        });
    });
});

/**
 * Renderiza paginación para las cards del taller.
 */
function renderTallerPagination() {
    var $container = $('#taller-pagination');
    if (!currentPagination || currentPagination.totalPages <= 1) {
        $container.empty();
        return;
    }

    var p = currentPagination;
    var html = '<nav><ul class="pagination pagination-sm justify-content-center mb-0">';

    // Anterior
    html += '<li class="page-item' + (p.page <= 1 ? ' disabled' : '') + '">';
    html += '<a class="page-link" href="#" data-page="' + (p.page - 1) + '">&laquo;</a></li>';

    // Páginas
    var maxVisible = 7;
    var startPage = Math.max(1, p.page - Math.floor(maxVisible / 2));
    var endPage = Math.min(p.totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }

    for (var i = startPage; i <= endPage; i++) {
        html += '<li class="page-item' + (i === p.page ? ' active' : '') + '">';
        html += '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
    }

    // Siguiente
    html += '<li class="page-item' + (p.page >= p.totalPages ? ' disabled' : '') + '">';
    html += '<a class="page-link" href="#" data-page="' + (p.page + 1) + '">&raquo;</a></li>';

    html += '</ul></nav>';
    html += '<small class="text-muted d-block text-center mt-1">' +
        ((p.page - 1) * p.perPage + 1) + '-' + Math.min(p.page * p.perPage, p.totalCount) +
        ' de ' + p.totalCount + ' órdenes</small>';

    $container.html(html);

    // Click en paginación
    $container.find('.page-link').on('click', function (e) {
        e.preventDefault();
        var page = parseInt($(this).data('page'));
        if (page && page >= 1 && page <= p.totalPages && page !== currentPage) {
            currentPage = page;
            loadOrdenes();
            $('html, body').animate({ scrollTop: $('#ordenes-cards-container').offset().top - 80 }, 300);
        }
    });
}

$(document).ready(function () {
    // Carga inicial
    loadOrdenes();

    // Filtro por status
    $('#filter-status').on('change', function () {
        currentStatusFilter = $(this).val();
        currentPage = 1;
        loadOrdenes();
    });

    // Búsqueda con debounce
    var searchTimer;
    $('#filter-search').on('keyup', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            currentPage = 1;
            loadOrdenes();
        }, 400);
    });
});
