/**
 * 03_details.js — Gestión de filas de detalle (llantas a reparar)
 *
 * Responsabilidad:
 * - Escuchar evento 'tires:selected' para crear filas de detalle
 * - Renderizar filas en #doc-details-body según config.detailFields
 * - Permitir eliminar filas individuales
 * - Sincronizar con State.details y actualizar contador
 */

(function (module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;
    var e = module.e;

    var tbody = null;
    var summaryCount = null;
    var detailFields = [];

    function init() {
        tbody = document.getElementById('doc-details-body');
        summaryCount = document.getElementById('summary-detail-count');

        if (!tbody) {
            console.warn('[03_details] #doc-details-body no encontrado.');
            return;
        }

        // Leer definición de campos desde config
        var config = State.config || {};
        detailFields = config.detailFields || [];

        // Si no hay definición, usar campos por defecto
        if (detailFields.length === 0) {
            detailFields = [
                { name: 'tire_code', label: 'Código Llanta', type: 'text', readonly: true },
                { name: 'tire_km', label: 'Km actual', type: 'number', readonly: true },
                { name: 'tread_depth', label: 'Prof. remanente', type: 'text', readonly: true },
                { name: 'repair_type', label: 'Tipo reparación', type: 'select', 'options': {
                    'PUNCTURE': 'Ponchadura',
                    'PATCH': 'Parche',
                    'RETREAD': 'Renovado',
                    'BALANCE': 'Balanceo',
                    'ALIGNMENT': 'Alineacion',
                    'ROTATION': 'Rotacion',
                    'OTHER': 'Otro'
                } },
                { name: 'comments', label: 'Comentarios', type: 'text' }
            ];
        }

        // Escuchar evento: se seleccionaron llantas del modal
        Events.on('tires:selected', function (tires) {
            if (!Array.isArray(tires)) return;
            tires.forEach(function (tire) {
                addDetailRow(tire);
            });
            State.markDirty();
        });

        // Renderizar filas existentes (modo edición)
        renderExistingRows();

        // Delegación: botón eliminar en filas de detalle
        tbody.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-remove-detail');
            if (!btn) return;
            e.preventDefault();
            var index = parseInt(btn.getAttribute('data-detail-index'), 10);
            if (!isNaN(index)) {
                removeDetailRow(index);
            }
        });

        // Delegación: cambios en campos de detalle → sincronizar State
        tbody.addEventListener('change', function (e) {
            var field = e.target.closest('[data-detail-index]');
            if (!field) return;
            var index = parseInt(field.getAttribute('data-detail-index'), 10);
            if (isNaN(index) || !State.details[index]) return;
            var name = field.name;
            if (name) {
                State.details[index][name] = field.type === 'number' ? parseFloat(field.value) || 0 : field.value;
                State.markDirty();
            }
        });

        //console.log('[03_details] Inicializado.');
    }

    function addDetailRow(tireData) {
        State.detailCounter++;
        var linenum = State.detailCounter;

        var row = {
            linenum: linenum,
            tire_code: tireData.tire_code || '',
            tire_km: tireData.tire_km || 0,
            tread_depth: tireData.tread_depth || '',
            repair_type: '',
            comments: ''
        };

        State.details.push(row);
        renderRow(row, State.details.length - 1);
        updateSummary();
    }

    function removeDetailRow(index) {
        if (index < 0 || index >= State.details.length) return;
        State.details.splice(index, 1);
        renderAllRows();
        updateSummary();
        State.markDirty();
    }

    function renderExistingRows() {
        if (State.details.length > 0) {
            renderAllRows();
            updateSummary();
        }
    }

    function renderAllRows() {
        if (!tbody) return;
        tbody.innerHTML = '';
        State.details.forEach(function (row, idx) {
            row.linenum = idx + 1;
            renderRow(row, idx);
        });
    }

    function renderRow(row, index) {
        if (!tbody) return;

        var tr = document.createElement('tr');
        tr.setAttribute('data-detail-index', index);

        detailFields.forEach(function (field) {
            var td = document.createElement('td');
            var value = row[field.name] !== undefined ? row[field.name] : '';
            var fieldType = field.type || 'text';
            var readonly = field.readonly ? ' readonly' : '';

            if (fieldType === 'select') {
                var html = '<select class="form-select form-select-sm detail-field" name="' + e(field.name) + '" data-detail-index="' + index + '"' + readonly + '>';
                var opts = field.options || {};
                if (Array.isArray(opts)) {
                    opts.forEach(function (opt) {
                        var optValue = typeof opt === 'object' ? (opt.id || opt.code || '') : opt;
                        var optLabel = typeof opt === 'object' ? (opt.name || optValue) : opt;
                        var selected = (String(optValue) === String(value)) ? ' selected' : '';
                        html += '<option value="' + e(optValue) + '"' + selected + '>' + e(optLabel) + '</option>';
                    });
                } else {
                    Object.keys(opts).forEach(function (key) {
                        var selected = (String(key) === String(value)) ? ' selected' : '';
                        html += '<option value="' + e(key) + '"' + selected + '>' + e(opts[key]) + '</option>';
                    });
                }
                html += '</select>';
                if (field.readonly) {
                    html += '<input type="hidden" name="' + e(field.name) + '" value="' + e(value) + '">';
                }
                td.innerHTML = html;
            } else if (fieldType === 'textarea') {
                td.innerHTML = '<textarea class="form-control form-control-sm detail-field" name="' + e(field.name) + '" data-detail-index="' + index + '" rows="1"' + readonly + '>' + e(value) + '</textarea>';
            } else if (fieldType === 'number') {
                td.innerHTML = '<input type="number" class="form-control form-control-sm detail-field" name="' + e(field.name) + '" data-detail-index="' + index + '" value="' + e(value) + '" step="0.01"' + readonly + '>';
            } else {
                td.innerHTML = '<input type="text" class="form-control form-control-sm detail-field" name="' + e(field.name) + '" data-detail-index="' + index + '" value="' + e(value) + '"' + readonly + '>';
            }

            tr.appendChild(td);
        });

        // Columna de acciones (eliminar)
        var tdAction = document.createElement('td');
        tdAction.style.width = '40px';
        tdAction.style.textAlign = 'center';
        tdAction.innerHTML = '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-detail" data-detail-index="' + index + '" title="Eliminar"><i class="fa-solid fa-trash-can"></i></button>';
        tr.appendChild(tdAction);

        tbody.appendChild(tr);
    }

    function updateSummary() {
        if (summaryCount) {
            summaryCount.textContent = State.details.length;
        }
    }

    module._detailsInit = init;
})(window.DocTireMntForm);
