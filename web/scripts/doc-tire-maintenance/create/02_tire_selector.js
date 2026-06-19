/**
 * 02_tire_selector.js — Modal de selección de llantas
 *
 * Responsabilidad:
 * - Abrir/cerrar el modal #mdl-mnt-tire-selector desde #add-detail-row
 * - Inicializar DataTable en #tbl-mnt-tire-selector (server-side)
 * - Manejar checkboxes de selección múltiple
 * - Botón "Agregar seleccionadas" → disparar evento 'tires:selected'
 */

(function (module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;
    var e = module.e;
    var getModal = module.getModal;

    var modalEl = null;
    var addDetailBtn = null;
    var dataTable = null;
    var selectedTires = {};
    var selectAllCheckbox = null;

    function init() {
        modalEl = document.getElementById('mdl-mnt-tire-selector');
        addDetailBtn = document.getElementById('add-detail-row');
        selectAllCheckbox = document.getElementById('mdl-mnt-tire-select-all');

        if (!modalEl) {
            console.warn('[02_tire_selector] Modal #mdl-mnt-tire-selector no encontrado.');
            return;
        }

        // Abrir modal al hacer clic en "Agregar detalle"
        if (addDetailBtn) {
            addDetailBtn.addEventListener('click', function (e) {
                e.preventDefault();
                openModal();
            });
        }

        // Select all / deselect all
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                var isChecked = this.checked;
                var checkboxes = modalEl.querySelectorAll('.tire-checkbox');
                checkboxes.forEach(function (cb) {
                    cb.checked = isChecked;
                    var tireCode = cb.getAttribute('data-tire-code');
                    if (tireCode) {
                        if (isChecked) {
                            selectedTires[tireCode] = JSON.parse(cb.getAttribute('data-tire-data') || '{}');
                        } else {
                            delete selectedTires[tireCode];
                        }
                    }
                });
                updateSelectedCount();
            });
        }

        // Botón "Agregar seleccionadas"
        var addSelectedBtn = document.getElementById('mdl-mnt-tire-add-selected');
        if (addSelectedBtn) {
            addSelectedBtn.addEventListener('click', function () {
                confirmSelection();
            });
        }

        // Escuchar checkboxes individuales (delegación)
        if (modalEl) {
            modalEl.addEventListener('change', function (e) {
                var cb = e.target.closest('.tire-checkbox');
                if (!cb) return;
                var tireCode = cb.getAttribute('data-tire-code');
                if (!tireCode) return;
                if (cb.checked) {
                    selectedTires[tireCode] = JSON.parse(cb.getAttribute('data-tire-data') || '{}');
                } else {
                    delete selectedTires[tireCode];
                    if (selectAllCheckbox) selectAllCheckbox.checked = false;
                }
                updateSelectedCount();
            });
        }

        // Búsqueda en modal con Enter
        var searchInput = document.getElementById('mdl-mnt-tire-search');
        if (searchInput) {
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (dataTable) dataTable.draw();
                }
            });
        }

        //console.log('[02_tire_selector] Inicializado.');
    }

    function openModal() {
        selectedTires = {};
        if (selectAllCheckbox) selectAllCheckbox.checked = false;
        updateSelectedCount();

        // Inicializar o redibujar DataTable
        initDataTable();

        var modal = getModal('mdl-mnt-tire-selector');
        if (modal) modal.show();
    }

    function initDataTable() {
        var tableEl = document.getElementById('tbl-mnt-tire-selector');
        if (!tableEl) return;

        // Destruir instancia previa
        if ($.fn.DataTable.isDataTable(tableEl)) {
            $(tableEl).DataTable().destroy();
        }

        var routes = window.DocTireFormUrls || {};
        var ajaxUrl = routes.getAvailableTires;
        if (!ajaxUrl) {
            console.error('[02_tire_selector] Ruta getAvailableTires no configurada.');
            return;
        }

        dataTable = $(tableEl).DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            pageLength: 10,
            lengthChange: false,
            ordering: true,
            info: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
            },
            ajax: {
                url: ajaxUrl,
                type: 'GET',
                data: function (d) {
                    d.series_id = document.getElementById('mdl-mnt-tire-series-filter')?.value || '';
                    d.search = document.getElementById('mdl-mnt-tire-search')?.value || '';
                },
                dataSrc: function (response) {
                    if (response.Success !== 'Ok') {
                        module.toast('error', response.Msg || 'Error al cargar llantas.');
                        return [];
                    }
                    return response.Data?.data || response.Data || [];
                }
            },
            columns: [
                {
                    data: 'tire_code',
                    orderable: false,
                    render: function (data, type, row) {
                        if (type === 'display') {
                            var rowJson = JSON.stringify(row).replace(/"/g, '&quot;');
                            return '<input type="checkbox" class="tire-checkbox" data-tire-code="' + e(data) + '" data-tire-data="' + rowJson + '">';
                        }
                        return '';
                    }
                },
                { data: 'tire_code', render: function (d) { return e(d); } },
                { data: 'tire_size', render: function (d) { return e(d); } },
                { data: 'tire_brand', render: function (d) { return e(d); } },
                { data: 'series_name', render: function (d) { return e(d); } },
                { data: 'tire_km', render: function (d) { return e(d); } },
                { data: 'tread_depth', render: function (d) { return d !== null && d !== undefined ? e(d) : '—'; } },
                { data: 'location', render: function (d) { return e(d); } }
            ],
            drawCallback: function () {
                // Re-sincronizar checkboxes con selectedTires después de cada paginación
                var tbody = tableEl.querySelector('tbody');
                if (!tbody) return;
                var checkboxes = tbody.querySelectorAll('.tire-checkbox');
                checkboxes.forEach(function (cb) {
                    var code = cb.getAttribute('data-tire-code');
                    if (code && selectedTires[code]) {
                        cb.checked = true;
                    }
                });
            }
        });

        // Mover paginación a nuestro contenedor personalizado
        var paginationEl = document.getElementById('mdl-mnt-tire-pagination');
        if (paginationEl && tableEl) {
            var dtInfo = document.getElementById(tableEl.id + '_info');
            var dtPaginate = document.getElementById(tableEl.id + '_paginate');
            if (dtInfo && dtPaginate) {
                paginationEl.innerHTML = '';
                paginationEl.appendChild(dtInfo);
                paginationEl.appendChild(dtPaginate);
            }
        }
    }

    function updateSelectedCount() {
        var count = Object.keys(selectedTires).length;
        var countEl = document.getElementById('mdl-mnt-tire-selected-count');
        var addBtn = document.getElementById('mdl-mnt-tire-add-selected');
        if (countEl) countEl.textContent = count;
        if (addBtn) addBtn.disabled = count === 0;
        State.selectedTires = selectedTires;
    }

    function confirmSelection() {
        var tires = Object.values(selectedTires);
        if (tires.length === 0) {
            module.toast('warning', 'Seleccione al menos una llanta.');
            return;
        }

        // Emitir evento para que 03_details agregue las filas
        Events.emit('tires:selected', tires);

        // Cerrar modal
        var modal = getModal('mdl-mnt-tire-selector');
        if (modal) modal.hide();

        // Cambiar a la pestaña de detalles
        var detailsTab = document.querySelector('[data-bs-target="#doc-tab-details"]');
        if (detailsTab) {
            var tab = window.bootstrap.Tab.getOrCreateInstance(detailsTab);
            if (tab) tab.show();
        }

        module.toast('success', tires.length + ' llanta(s) agregada(s).');
    }

    // API pública
    module.TireSelector = {
        open: openModal,
        getSelected: function () { return Object.values(selectedTires); }
    };

    module._tireSelectorInit = init;
})(window.DocTireMntForm);
