/**
 * 06doc-tire-assignment-audit.js
 *
 * Responsabilidad: Auditoría en tiempo real del taller visual.
 * - Inventario activo (#tbl-active-tires-body): muestra llantas montadas actualmente.
 * - Bitácora de movimientos (#tbl-movement-log-body): registro cronológico de cada acción.
 *
 * Flujo de datos:
 *   DragDrop (05) emite eventos → Audit (06) escucha y actualiza ambas tablas.
 *   State.llantasSeleccionadas es la fuente de verdad para refrescos completos.
 *
 * Dependencias: State, Events (desde window.DocTireAssignment)
 * Orden de carga: 06 (después de DragDrop)
 */

(function(module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;

    var activeTiresBody = null;
    var movementLogBody = null;
    var MAX_LOG_ENTRIES = 50;

    /* ====== INIT ====== */

    function init() {
        activeTiresBody = document.getElementById('tbl-active-tires-body');
        movementLogBody = document.getElementById('tbl-movement-log-body');

        if (!activeTiresBody) {
            console.warn('[Audit] #tbl-active-tires-body no encontrado.');
        }
        if (!movementLogBody) {
            console.warn('[Audit] #tbl-movement-log-body no encontrado.');
        }

        if (!activeTiresBody && !movementLogBody) {
            return;
        }

        subscribeEvents();

        // Carga inicial: sincronizar inventario desde el estado
        refreshActiveTires();
    }

    /* ====== SUSCRIPCIONES ====== */

    function subscribeEvents() {
        Events.on('tire:assigned', onTireAssigned);
        Events.on('tire:moved', onTireMoved);
        Events.on('tire:swapped', onTireSwapped);
        Events.on('tire:replaced', onTireReplaced);
        Events.on('tire:removed', onTireRemoved);
        Events.on('tire:destino:asignado', onDestinoAsignado);
        Events.on('movement:removed', onMovementRemoved);
        Events.on('vehicle:removed', onVehicleRemoved);
        Events.on('state:updated', refreshActiveTires);
    }

    /* ====== MANEJADORES DE EVENTOS ====== */

    function onTireAssigned(data) {
        refreshActiveTires();
        addLogEntry('Asignaci\u00f3n', data);
    }

    function onTireMoved(data) {
        refreshActiveTires();
        addLogEntry('Rotaci\u00f3n/Traslado', data);
    }

    function onTireSwapped(data) {
        refreshActiveTires();
        addLogEntry('Intercambio', data);
    }

    function onTireReplaced(data) {
        refreshActiveTires();
        addLogEntry('Reemplazo', data);
    }

    function onTireRemoved(data) {
        refreshActiveTires();
        addLogEntry('Retiro a cuarentena', data);
    }

    function onDestinoAsignado(data) {
        addLogEntry('Destino asignado', data);
    }

    function onMovementRemoved(data) {
        refreshActiveTires();
        addLogEntry('Movimiento eliminado', data);
    }

    function onVehicleRemoved(vehicleCode) {
        var code = typeof vehicleCode === 'object' ? (vehicleCode.vehicleCode || '') : vehicleCode;
        addLogEntry('Unidad desvinculada', { tireCode: code + ' (todas las llantas asociadas)', action_type: 'REMOVE' });
        refreshActiveTires();
    }

    /* ====== INVENTARIO ACTIVO ====== */

    /**
     * Agrega una fila al inventario activo.
     * Busca datos complementarios en State si el evento no trae talla.
     */
    function addActiveTireRow(data) {
        if (!activeTiresBody) return;

        // Quitar placeholder si existe
        removePlaceholder(activeTiresBody);

        var tireCode = data.tireCode || data.tire_code || '';
        var tireSize = data.tireSize || data.tire_size || getTireSizeFromState(tireCode);
        var location = getLocationDescription(data);
        var actionType = data.action_type || 'ASSIGN';

        var tr = document.createElement('tr');
        tr.setAttribute('data-tire-code', tireCode);
        tr.innerHTML =
            '<td>' + escapeHtml(tireCode) + '</td>' +
            '<td>' + escapeHtml(tireSize) + '</td>' +
            '<td>' + escapeHtml(location) + '</td>' +
            '<td>' + getStatusBadge(actionType) + '</td>';
        activeTiresBody.appendChild(tr);
    }

    /**
     * Actualiza ubicación de una fila existente en inventario activo.
     */
    function updateActiveTireRow(data) {
        if (!activeTiresBody) return;
        var tireCode = data.tireCode || data.tire_code || '';
        var row = activeTiresBody.querySelector('tr[data-tire-code="' + tireCode + '"]');
        if (row) {
            row.cells[2].textContent = getLocationDescription(data);
            row.cells[3].innerHTML = getStatusBadge(data.action_type || 'MOVED');
        }
    }

    /**
     * Marca una llanta como removida en el inventario activo.
     */
    function removeActiveTireRow(data) {
        if (!activeTiresBody) return;
        var tireCode = data.tireCode || data.tire_code || '';
        var row = activeTiresBody.querySelector('tr[data-tire-code="' + tireCode + '"]');
        if (row) {
            row.cells[2].textContent = 'Cuarentena';
            row.cells[3].innerHTML = getStatusBadge('REMOVE');
        }
    }

    /**
     * Refresca el inventario activo mostrando TODAS las llantas del documento
     * (disponibles + montadas) con su ubicación actual.
     *
     * Fuentes de datos:
     *   - allAvailableTires (warehouse): llantas disponibles en el almacén
     *   - State.llantasSeleccionadas (movimientos): para saber ubicación de montadas
     *   - Chassis.getAllPositions(): posiciones ocupadas en el chasis
     */
    function refreshActiveTires() {
        if (!activeTiresBody) return;

        // Obtener llantas del warehouse (disponibles)
        var warehouseTires = [];
        if (window.DocTireAssignment.TireWarehouse &&
            typeof window.DocTireAssignment.TireWarehouse.getAllAvailableTires === 'function') {
            warehouseTires = window.DocTireAssignment.TireWarehouse.getAllAvailableTires() || [];
        }

        // Obtener posiciones montadas del chasis
        var mountedPositions = {};
        var getAllPos = window.DocTireAssignment.getAllPositions;
        if (typeof getAllPos === 'function') {
            var positions = getAllPos();
            if (positions) {
                Object.keys(positions).forEach(function(key) {
                    var pos = positions[key];
                    if (pos && pos.tireCode) {
                        mountedPositions[pos.tireCode] = {
                            vehicleCode: pos.vehicleCode || '',
                            positionKey: pos.positionKey || key,
                            tireSize: pos.tireSize || ''
                        };
                    }
                });
            }
        }

        // Construir mapa de ubicaciones desde movimientos (para llantas en cuarentena)
        var movementLocations = {};
        var llantas = State.llantasSeleccionadas || [];
        llantas.forEach(function(l) {
            var code = l.tire_code || l.tireCode || '';
            if (code) {
                movementLocations[code] = l;
            }
        });

        activeTiresBody.innerHTML = '';
        var totalTires = 0;

        // 1) Mostrar llantas montadas en chasis
        Object.keys(mountedPositions).forEach(function(code) {
            var mp = mountedPositions[code];
            var tr = document.createElement('tr');
            tr.setAttribute('data-tire-code', code);
            var location = (mp.vehicleCode ? mp.vehicleCode + ' ' : '') + mp.positionKey;
            tr.innerHTML =
                '<td>' + escapeHtml(code) + '</td>' +
                '<td>' + escapeHtml(mp.tireSize || getTireSizeFromState(code)) + '</td>' +
                '<td>' + escapeHtml(location) + '</td>' +
                '<td>' + getStatusBadge('ASSIGN') + '</td>';
            activeTiresBody.appendChild(tr);
            totalTires++;
        });

        // 2) Mostrar llantas disponibles en almacén (NO montadas)
        warehouseTires.forEach(function(t) {
            var code = t.tire_code || '';
            if (!code || mountedPositions[code]) return;
            var tr = document.createElement('tr');
            tr.setAttribute('data-tire-code', code);
            tr.innerHTML =
                '<td>' + escapeHtml(code) + '</td>' +
                '<td>' + escapeHtml(t.tire_size || '-') + '</td>' +
                '<td>Almacén</td>' +
                '<td>' + getStatusBadge('AVAILABLE') + '</td>';
            activeTiresBody.appendChild(tr);
            totalTires++;
        });

        // 3) Mostrar llantas en cuarentena (removidas)
        llantas.forEach(function(l) {
            var code = l.tire_code || l.tireCode || '';
            if (!code || mountedPositions[code]) return;
            if (l.action_type === 'REMOVE') {
                var tr = document.createElement('tr');
                tr.setAttribute('data-tire-code', code);
                tr.innerHTML =
                    '<td>' + escapeHtml(code) + '</td>' +
                    '<td>' + escapeHtml(l.tire_size || l.tireSize || getTireSizeFromState(code)) + '</td>' +
                    '<td>Cuarentena</td>' +
                    '<td>' + getStatusBadge('REMOVE') + '</td>';
                activeTiresBody.appendChild(tr);
                totalTires++;
            }
        });

        if (totalTires === 0) {
            showActiveTiresPlaceholder();
        }

        console.log('[06-audit] refreshActiveTires:', { totalTires: totalTires, mounted: Object.keys(mountedPositions).length, warehouse: warehouseTires.length });
    }

    /**
     * Muestra placeholder cuando no hay llantas activas.
     */
    function showActiveTiresPlaceholder() {
        if (!activeTiresBody) return;
        activeTiresBody.innerHTML =
            '<tr><td colspan="4" class="text-center text-muted py-3 small">' +
            'No hay llantas activas en el documento.' +
            '</td></tr>';
    }

    /* ====== BITÁCORA DE MOVIMIENTOS ====== */

    /**
     * Agrega una entrada a la bitácora de movimientos.
     * Las entradas nuevas van al inicio (más reciente primero).
     */
    function addLogEntry(actionType, data) {
        if (!movementLogBody) return;

        console.log('[06-audit] addLogEntry:', { actionType, data });
        // Quitar placeholder si existe
        removePlaceholder(movementLogBody);

        var tr = document.createElement('tr');
        var timestamp = getCurrentTimestamp();
        var detail = getActionDescription(data);

        tr.innerHTML =
            '<td class="text-nowrap text-xxs text-muted font-monospace">' + escapeHtml(timestamp) + '</td>' +
            '<td><span class="badge ' + getLogBadgeClass(actionType) + '">' + escapeHtml(actionType) + '</span></td>' +
            '<td class="small">' + escapeHtml(detail) + '</td>';

        movementLogBody.insertBefore(tr, movementLogBody.firstChild);

        // Limitar a MAX_LOG_ENTRIES
        while (movementLogBody.children.length > MAX_LOG_ENTRIES) {
            movementLogBody.removeChild(movementLogBody.lastChild);
        }
    }

    /**
     * Muestra placeholder cuando no hay movimientos.
     */
    function showLogPlaceholder() {
        if (!movementLogBody) return;
        movementLogBody.innerHTML =
            '<tr><td colspan="3" class="text-center text-muted py-3 small">' +
            'Sin movimientos registrados a\u00fan.' +
            '</td></tr>';
    }

    /* ====== HELPERS ====== */

    /**
     * Obtiene la talla de una llanta desde State.llantasSeleccionadas.
     */
    function getTireSizeFromState(tireCode) {
        if (!tireCode) return '';
        var llantas = State.llantasSeleccionadas || [];
        for (var i = 0; i < llantas.length; i++) {
            var l = llantas[i];
            if ((l.tire_code || l.tireCode) === tireCode) {
                return l.tire_size || l.tireSize || '';
            }
        }
        return '';
    }

    /**
     * Quita la fila placeholder (primer hijo si tiene colspan).
     */
    function removePlaceholder(tbody) {
        if (!tbody || !tbody.firstChild) return;
        var firstRow = tbody.firstChild;
        if (firstRow.tagName === 'TR' && firstRow.cells.length === 1 && firstRow.cells[0].hasAttribute('colspan')) {
            tbody.removeChild(firstRow);
        }
    }

    /**
     * Retorna el badge HTML según el tipo de acción.
     */
    function getStatusBadge(actionType) {
        var map = {
            'AVAILABLE': '<span class="badge bg-secondary">Disponible</span>',
            'ASSIGN': '<span class="badge bg-success">Montada</span>',
            'TRANSFER': '<span class="badge bg-info text-white">Transferida</span>',
            'ROTATE': '<span class="badge bg-primary">Rotada</span>',
            'REMOVE': '<span class="badge bg-warning text-dark">Cuarentena</span>',
            'SCRAP': '<span class="badge bg-danger">Baja</span>'
        };
        return map[actionType] || '<span class="badge bg-secondary">' + escapeHtml(actionType) + '</span>';
    }

    /**
     * Retorna la clase CSS del badge para la bitácora.
     */
    function getLogBadgeClass(actionType) {
        var map = {
            'Asignaci\u00f3n': 'bg-success',
            'Rotaci\u00f3n/Traslado': 'bg-primary',
            'Intercambio': 'bg-info text-white',
            'Reemplazo': 'bg-secondary',
            'Retiro a cuarentena': 'bg-warning text-dark',
            'Destino asignado': 'bg-dark',
            'Movimiento eliminado': 'bg-danger'
        };
        return map[actionType] || 'bg-secondary';
    }

    /**
     * Timestamp actual HH:MM:SS.
     */
    function getCurrentTimestamp() {
        return new Date().toLocaleTimeString('es-MX', { hour12: false });
    }

    /**
     * Descripción legible de la acción para la bitácora.
     */
    function getActionDescription(data) {
        if (!data) return 'Sin detalle';
        var parts = [];
        var tireCode = data.tireCode || data.tire_code || '';
        var oldTire = data.oldTire || data.old_tire || '';
        var newTire = data.newTire || data.new_tire || '';
        var sourceTire = data.sourceTire || data.source_tire || '';
        var targetTire = data.targetTire || data.target_tire || '';
        var vehicleCode = data.vehicleCode || data.vehicle_code || '';
        var positionKey = data.positionKey || data.position_key || '';
        var fromPosition = data.fromPosition || data.from_position || '';
        var toPosition = data.toPosition || data.to_position || '';
        var destino = data.destino || '';

        if (tireCode) parts.push(tireCode);
        if (oldTire && newTire) {
            parts.push(oldTire + ' \u2192 ' + newTire);
        } else if (sourceTire && targetTire) {
            parts.push(sourceTire + ' \u2194 ' + targetTire);
        }
        if (fromPosition && toPosition) {
            parts.push(fromPosition + ' \u2192 ' + toPosition);
        } else if (positionKey) {
            parts.push('(' + positionKey + ')');
        }
        if (vehicleCode) parts.push('\u2192 ' + vehicleCode);
        if (destino) parts.push('\u2192 ' + destino);

        return parts.join(' ') || 'Sin detalle';
    }

    /**
     * Descripción de ubicación para el inventario activo.
     */
    function getLocationDescription(llanta) {
        if (!llanta) return 'Desconocida';
        if (llanta.action_type === 'REMOVE') return 'Cuarentena';
        if (llanta.warehouse_code_to === 'TALLER') return 'Taller';
        if (llanta.warehouse_code_to === 'MAIN') return 'Almac\u00e9n';
        var parts = [];
        if (llanta.vehicle_code || llanta.vehicleCode) parts.push(llanta.vehicle_code || llanta.vehicleCode);
        if (llanta.position_key || llanta.positionKey) parts.push(llanta.position_key || llanta.positionKey);
        return parts.join(' ') || 'Montada';
    }

    /**
     * Escapa HTML para prevenir XSS.
     */
    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(String(str)));
        return div.innerHTML;
    }

    /* ====== EXPORT ====== */

    module.initAudit = init;
    module.Audit = {
        refreshActiveTires: refreshActiveTires,
        addLogEntry: addLogEntry,
        showLogPlaceholder: showLogPlaceholder
    };

})(window.DocTireAssignment);
