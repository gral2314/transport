/**
 * doc-tire-assignment-modal.js
 * 
 * Responsabilidad: Gestión de modales (unidades y llantas).
 * Carga vía AJAX, autoselect, persistencia de selección.
 * Sincronización inversa: contrasta registros con el estado global.
 */

(function(module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;

    /**
     * Referencias a instancias de modales y DataTables
     */
    var unitModalInstance = null;
    var unitDataTable = null;
    
    var tireModalInstance = null;
    var tireDataTable = null;

    // =========================================================================
    // MODAL DE UNIDADES
    // =========================================================================

    function initUnitModal() {
        var modalElement = document.getElementById('mdl-units');
        if (!modalElement) return;

        unitModalInstance = new bootstrap.Modal(modalElement);

        // Cargar datos al abrir
        modalElement.addEventListener('show.bs.modal', function() {
            loadUnitsIntoModal();
        });

        // Limpiar al cerrar
        modalElement.addEventListener('hidden.bs.modal', function() {
            if (unitDataTable) {
                try { unitDataTable.destroy(); } catch(e) {}
                unitDataTable = null;
            }
        });

        // Botón Vincular
        var btnVincular = modalElement.querySelector('#btn-vincular-unidades');
        if (btnVincular) {
            btnVincular.addEventListener('click', processUnitSelection);
        }
    }

    function loadUnitsIntoModal() {
        var tableBody = document.querySelector('#mdl-tbl-units tbody');
        if (!tableBody) return;

        tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Cargando...</td></tr>';

        var vehicleOptions = (State.config.formOptions && State.config.formOptions.vehicle_options) || [];
        
        // Si hay opciones locales, usarlas. Si no, intentar AJAX si fuera necesario (aquí simplificamos a local/AJAX mixto)
        if (vehicleOptions.length > 0) {
            renderUnitTable(vehicleOptions, tableBody);
        } else {
            // Fallback a AJAX si no hay formOptions
            var routes = State.config.routes || {};
            if (routes.getFormOptions) {
                fetch(routes.getFormOptions)
                    .then(r => r.json())
                    .then(res => {
                        if (res.Success === 'Ok' && res.Data && res.Data.vehicle_options) {
                            renderUnitTable(res.Data.vehicle_options, tableBody);
                        }
                    })
                    .catch(err => console.error(err));
            }
        }
    }

    function renderUnitTable(vehicles, tableBody) {
        var selectedCodes = State.unidadesSeleccionadas.map(u => u.vehicle_code);
        var currentCount = State.unidadesSeleccionadas.length;
        var maxUnits = 2;
        console.log('[doc-tire-assignment-modal] Renderizando tabla de unidades. Total opciones:', vehicles.length, 'Unidades seleccionadas:', currentCount);
        var html = '';
        vehicles.forEach(function(v) {
            var code = v.code || v.vehicle_code || '';
            var name = v.name || v.vehicle_name || code;
            var isSelected = selectedCodes.indexOf(code) !== -1;
            var isDisabled = !isSelected && currentCount >= maxUnits;

            html += '<tr class="' + (isSelected ? 'table-success' : '') + '">';
            html += '  <td class="text-center"><input type="checkbox" class="form-check-input unit-select" value="' + code + '" ' + (isSelected ? 'checked' : '') + (isDisabled ? 'disabled' : '') + '></td>';
            html += '  <td><strong>' + escapeHtml(code) + '</strong></td>';
            html += '  <td>' + escapeHtml(name) + '</td>';
            html += '  <td>' + (isSelected ? '<span class="badge bg-success">Vinculada</span>' : '<span class="badge bg-secondary">Disponible</span>') + '</td>';
            html += '</tr>';
        });

        if (vehicles.length === 0) html = '<tr><td colspan="4" class="text-center text-muted">No hay unidades.</td></tr>';
        
        tableBody.innerHTML = html;

        // Inicializar DataTable si hay muchos registros
        var tableEl = document.getElementById('mdl-tbl-units');
        if (tableEl && typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
            if (unitDataTable) unitDataTable.destroy();
            
            unitDataTable = jQuery('#mdl-tbl-units').DataTable({
                pageLength: 5,
                lengthChange: false,
                searching: true,
                ordering: true,
                info: false,
                drawCallback: function() {
                    attachUnitCheckboxEvents(tableEl);
                }
            });
        } else {
            attachUnitCheckboxEvents(tableEl);
        }
        
        updateUnitLimitsUI();
    }

    function attachUnitCheckboxEvents(container) {
        if (!container) return;
        container.querySelectorAll('.unit-select').forEach(cb => {
            cb.onchange = handleUnitCheckboxChange;
        });
    }

    function handleUnitCheckboxChange(e) {
        var cb = e.target;
        var code = cb.value;
        var row = cb.closest('tr');
        
        if (cb.checked) {
            if (State.unidadesSeleccionadas.length >= 2) {
                cb.checked = false;
                alert('Máximo 2 unidades permitidas.');
                return;
            }
            var name = row ? row.cells[2].textContent.trim() : code;
            State.unidadesSeleccionadas.push({ vehicle_code: code, vehicle_name: name, odometro: 0, comments: '', layout: null });
            row.classList.add('table-success');
            row.querySelector('.badge').className = 'badge bg-success';
            row.querySelector('.badge').textContent = 'Vinculada';
        } else {
            State.unidadesSeleccionadas = State.unidadesSeleccionadas.filter(u => u.vehicle_code !== code);
            row.classList.remove('table-success');
            row.querySelector('.badge').className = 'badge bg-secondary';
            row.querySelector('.badge').textContent = 'Disponible';
        }
        
        State.markDirty();
        updateUnitLimitsUI();
        Events.emit('units:changed', State.unidadesSeleccionadas);
    }

    function updateUnitLimitsUI() {
        var count = State.unidadesSeleccionadas.length;
        var counterEl = document.getElementById('mdl-unit-counter');
        if (counterEl) counterEl.textContent = count + ' / 2 unidades';
        
        // Actualizar estados de checkboxes deshabilitados
        document.querySelectorAll('#mdl-tbl-units .unit-select:not(:checked)').forEach(cb => {
            cb.disabled = (count >= 2);
        });
    }

    function processUnitSelection() {
        if (unitModalInstance) unitModalInstance.hide();
        Events.emit('units:selected', State.unidadesSeleccionadas);
    }

    // =========================================================================
    // MODAL DE LLANTAS
    // =========================================================================

    function initTireModal() {
        var modalElement = document.getElementById('mdl-tires');
        if (!modalElement) return;

        tireModalInstance = new bootstrap.Modal(modalElement);

        modalElement.addEventListener('show.bs.modal', function() {
            loadTiresIntoModal();
        });

        // CORRECCIÓN CRÍTICA: Destruir DataTable antes de que Bootstrap elimine el DOM
        modalElement.addEventListener('hidden.bs.modal', function() {
            if (tireDataTable) {
                try { 
                    tireDataTable.destroy(); 
                } catch(e) { 
                    console.warn('Error limpiando DataTable:', e); 
                }
                tireDataTable = null;
            }
        });

        // Event Delegation para el botón Agregar
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'btn-add-tires') {
                processTireSelection();
            }
        });
    }

    function loadTiresIntoModal() {
        var tableBody = document.getElementById('mdl-tbl-tires-body');
        if (!tableBody) return;

        tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Cargando...</td></tr>';

        var routes = State.config.routes || {};
        var url = routes.availableTires;

        if (url) {
            fetch(url)
                .then(r => r.json())
                .then(result => {
                    if (result.Success === 'Ok' && Array.isArray(result.Data)) {
                        renderTireTable(result.Data, tableBody);
                    } else {
                        renderTireTable([], tableBody);
                    }
                })
                .catch(err => {
                    console.error(err);
                    renderTireTable([], tableBody);
                });
        } else {
            // Fallback a formOptions
            var tires = (State.config.formOptions && State.config.formOptions.tire_options) || [];
            // Mapear a formato esperado si es necesario
            var mappedTires = tires.map(t => ({
                tire_code: t.code, tire_name: t.name, tire_size: t.size, tread_design: t.tread || '-'
            }));
            renderTireTable(mappedTires, tableBody);
        }
    }

    function renderTireTable(tires, tableBody) {
        // Obtener llantas ya en el almacén para marcarlas como seleccionadas
        var warehouseModule = window.DocTireAssignment.TireWarehouse;
        var warehouseCodes = (warehouseModule && warehouseModule.getWarehouseTireCodes) ? warehouseModule.getWarehouseTireCodes() : [];

        var html = '';
        tires.forEach(function(t) {
            var code = t.tire_code || t.code || '';
            var name = t.tire_name || t.name || code;
            var size = t.tire_size || '-';
            var tread = t.tread_design || '-';
            var isSelected = warehouseCodes.indexOf(code) !== -1;

            html += '<tr class="' + (isSelected ? 'table-info' : '') + '">';
            html += '  <td class="text-center"><input type="checkbox" class="form-check-input tire-select" value="' + code + '" data-name="' + escapeHtml(name) + '" ' + (isSelected ? 'checked' : '') + '></td>';
            html += '  <td><strong>' + escapeHtml(code) + '</strong></td>';
            html += '  <td>' + escapeHtml(name) + '</td>';
            html += '  <td>' + escapeHtml(size) + '</td>';
            html += '  <td>' + escapeHtml(tread) + '</td>';
            html += '</tr>';
        });

        if (tires.length === 0) html = '<tr><td colspan="5" class="text-center text-muted">No hay llantas disponibles.</td></tr>';
        
        tableBody.innerHTML = html;

        // Inicializar DataTable con jQuery
        var tableEl = document.getElementById('mdl-tbl-tires');
        if (tableEl && typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
            if (tireDataTable) tireDataTable.destroy();
            
            tireDataTable = jQuery('#mdl-tbl-tires').DataTable({
                pageLength: 10,
                lengthChange: false,
                searching: true,
                ordering: true,
                info: false,
                drawCallback: function() {
                    // Re-asignar eventos si es necesario
                }
            });
        }
    }

    function processTireSelection() {
        var checkboxes = document.querySelectorAll('#mdl-tbl-tires .tire-select:checked');
        var selectedTires = [];
        
        checkboxes.forEach(function(cb) {
            var row = cb.closest('tr');
            var cells = row.querySelectorAll('td');
            selectedTires.push({
                tire_code: cb.value,
                tire_name: cb.getAttribute('data-name') || cells[1].textContent.trim(),
                tire_size: cells[3] ? cells[3].textContent.trim() : '-',
                tread_design: cells[4] ? cells[4].textContent.trim() : '-'
            });
        });

        if (tireModalInstance) tireModalInstance.hide();

        if (selectedTires.length > 0) {
            Events.emit('tires:selected', selectedTires);
            actualizarWarehouseDirectamente(selectedTires);
        }
    }

    function actualizarWarehouseDirectamente(llantas) {
        var warehouseBody = document.getElementById('doc-details-body');
        if (!warehouseBody) return;

        // Obtener códigos existentes para no duplicar
        var existingRows = warehouseBody.querySelectorAll('tr[data-tire-code]');
        var existingCodes = Array.from(existingRows).map(r => r.getAttribute('data-tire-code'));

        llantas.forEach(function(llanta) {
            if (existingCodes.indexOf(llanta.tire_code) === -1) {
                var tr = document.createElement('tr');
                tr.setAttribute('draggable', 'true');
                tr.setAttribute('data-tire-code', llanta.tire_code);
                tr.setAttribute('data-tire-name', llanta.tire_name);
                tr.setAttribute('data-tire-size', llanta.tire_size);
                tr.className = 'warehouse-tire-row';
                
                tr.innerHTML = 
                    '<td class="text-nowrap"><strong>' + escapeHtml(llanta.tire_code) + '</strong></td>' +
                    '<td>' + escapeHtml(llanta.tire_name) + '</td>' +
                    '<td class="text-nowrap">' + escapeHtml(llanta.tire_size) + '</td>' +
                    '<td class="text-nowrap"><span class="badge bg-light text-dark border text-xxs">' + escapeHtml(llanta.tread_design) + '</span></td>';
                
                warehouseBody.appendChild(tr);
            }
        });

        // Eliminar mensaje de "vacio" si existe
        var emptyMsg = warehouseBody.querySelector('td[colspan]');
        if (emptyMsg && emptyMsg.parentElement.children.length > 1) {
            emptyMsg.parentElement.remove();
        }

        Events.emit('tire:warehouse:rendered', []);
    }

    function escapeHtml(str) {
        if (typeof str !== 'string') return String(str || '');
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // =========================================================================
    // INICIALIZACIÓN
    // =========================================================================

    module.initModals = function() {
        initUnitModal();
        initTireModal();
        console.log('[doc-tire-assignment-modal] Modales inicializados correctamente.');
    };

    module.openUnitModal = function() { if (unitModalInstance) unitModalInstance.show(); };
    module.openTireModal = function() { 
        if (tireModalInstance) tireModalInstance.show(); 
        else {
            var el = document.getElementById('mdl-tires');
            if(el) { tireModalInstance = new bootstrap.Modal(el); tireModalInstance.show(); }
        }
    };

})(window.DocTireAssignment);