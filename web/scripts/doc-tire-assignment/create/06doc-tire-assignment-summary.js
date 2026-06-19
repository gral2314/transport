/**
 * doc-tire-assignment-summary.js
 * 
 * Responsabilidad: Renderiza la lista detallada de movimientos en el panel lateral.
 * - Muestra cada llanta con su tipo de movimiento, origen y destino
 * - Permite eliminación individual de movimientos
 * - Se actualiza reactivamente ante cambios en el estado global
 */

(function(module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;

    /**
     * Referencias a elementos del DOM
     */
    var summaryList = null;
    var emptyMessage = null;

    /**
     * Inicializa el módulo de resumen de movimientos
     */
    function init() {
        summaryList = document.getElementById('summary-movement-list');
        emptyMessage = document.getElementById('summary-empty-message');

        if (!summaryList) {
            console.warn('[doc-tire-assignment-summary] Elemento #summary-movement-list no encontrado.');
            summaryList = document.querySelector('.summary-movements-list');
        }

        // Escuchar eventos que afectan el resumen
        Events.on('tire:assigned', function() { refreshSummary(); });
        Events.on('tire:moved', function() { refreshSummary(); });
        Events.on('tire:swapped', function() { refreshSummary(); });
        Events.on('tire:replaced', function() { refreshSummary(); });
        Events.on('tire:removed', function() { refreshSummary(); });
        Events.on('tires:selected', function() { refreshSummary(); });
        Events.on('units:changed', function() { refreshSummary(); });

        // Render inicial
        refreshSummary();
    }

    /**
     * Refresca la lista de movimientos
     */
    function refreshSummary() {
        renderMovementList();
        updateEmptyState();
    }

    /**
     * Renderiza la lista detallada de movimientos
     */
    function renderMovementList() {
        if (!summaryList) return;

        var llantas = State.llantasSeleccionadas;

        if (!llantas || llantas.length === 0) {
            summaryList.innerHTML = '';
            return;
        }

        var html = '';
        llantas.forEach(function(llanta, index) {
            var actionBadge = getActionBadge(llanta.action_type);
            var fromInfo = getFromInfo(llanta);
            var toInfo = getToInfo(llanta);
            var bgClass = index % 2 === 0 ? 'bg-light' : '';

            html += '<div class="summary-movement-item ' + bgClass + ' border-bottom py-2 px-2 d-flex align-items-center" ' +
                    'data-index="' + index + '" data-tire-code="' + escapeHtml(llanta.tire_code || '') + '">';

            // Indicador de tipo de movimiento (icono)
            html += '  <div class="me-2 text-center" style="width: 24px;">';
            html += getActionIcon(llanta.action_type);
            html += '  </div>';

            // Información de la llanta y movimiento
            html += '  <div class="flex-grow-1 min-width-0">';
            html += '    <div class="d-flex align-items-center gap-1 flex-wrap">';
            html += '      <strong class="text-nowrap">' + escapeHtml(llanta.tire_code || 'Sin código') + '</strong>';
            html += '      ' + actionBadge;
            html += '    </div>';
            html += '    <div class="text-xxs text-muted mt-1">';
            
            // Mostrar origen y destino según el tipo de movimiento
            if (fromInfo) {
                html += '    <span class="text-warning-emphasis">' + escapeHtml(fromInfo) + '</span>';
            }
            if (fromInfo && toInfo) {
                html += '    <svg class="svg-inline--fa fa-arrow-right mx-1" viewBox="0 0 448 512" style="width:8px;"><path fill="currentColor" d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"></path></svg>';
            }
            if (toInfo) {
                html += '    <span class="text-primary-emphasis">' + escapeHtml(toInfo) + '</span>';
            }

            html += '    </div>';

            // Mostrar comentarios si existen
            if (llanta.comments) {
                html += '    <div class="text-xxxs text-muted mt-1">';
                html += '      <em>' + escapeHtml(llanta.comments) + '</em>';
                html += '    </div>';
            }

            html += '  </div>';

            // Botón de eliminar movimiento
            html += '  <div class="ms-2" style="flex-shrink: 0;">';
            html += '    <button type="button" class="btn btn-outline-danger btn-xs btn-remove-movement" ' +
                     'data-tire-code="' + escapeHtml(llanta.tire_code || '') + '" ' +
                     'data-index="' + index + '" ' +
                     'title="Eliminar movimiento">';
            html += '      <svg class="svg-inline--fa fa-trash-can" viewBox="0 0 448 512" style="width:10px;"><path fill="currentColor" d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>';
            html += '    </button>';
            html += '  </div>';

            html += '</div>';
        });

        summaryList.innerHTML = html;

        // Vincular eventos a los botones de eliminar
        bindRemoveButtons();
    }

    /**
     * Obtiene la representación textual de la posición origen
     */
    function getFromInfo(llanta) {
        var action = (llanta.action_type || '').toUpperCase();
        
        if (action === 'ASSIGN') {
            if (llanta.warehouse_code_from) {
                return 'Almacén: ' + llanta.warehouse_code_from;
            }
            return 'Almacén';
        }
        
        if (action === 'ROTATE' || action === 'TRANSFER' || action === 'REMOVE' || action === 'SCRAP') {
            if (llanta.vehicle_code_from && llanta.position_from) {
                return llanta.vehicle_code_from + ' - ' + llanta.position_from;
            }
            if (llanta.vehicle_code_from) {
                return llanta.vehicle_code_from;
            }
            if (llanta.position_from) {
                return llanta.position_from;
            }
            return 'Almacén';
        }
        
        return '';
    }

    /**
     * Obtiene la representación textual de la posición destino
     */
    function getToInfo(llanta) {
        var action = (llanta.action_type || '').toUpperCase();
        
        if (action === 'REMOVE' || action === 'SCRAP') {
            if (llanta.warehouse_code_to === 'TALLER') {
                return 'Taller';
            }
            if (llanta.warehouse_code_to === 'MAIN') {
                return 'Almacén';
            }
            if (llanta.comments && llanta.comments.indexOf('REUBICAR') !== -1) {
                return 'Reubicar en chasis';
            }
            return 'Baja / Almacén de retiro';
        }
        
        if (action === 'ASSIGN' || action === 'TRANSFER' || action === 'ROTATE') {
            if (llanta.vehicle_code_to && llanta.position_to) {
                return llanta.vehicle_code_to + ' - ' + llanta.position_to;
            }
            if (llanta.vehicle_code_to) {
                return llanta.vehicle_code_to;
            }
            if (llanta.position_to) {
                return 'Posición: ' + llanta.position_to;
            }
            return 'Sin posición destino';
        }
        
        return 'Sin destino';
    }

    /**
     * Obtiene el badge HTML del tipo de movimiento
     */
    function getActionBadge(actionType) {
        var action = (actionType || '').toUpperCase();
        var badges = {
            'ASSIGN': '<span class="badge bg-primary-subtile text-primary border border-primary text-xxxs" style="font-size:9px;">ASIGN</span>',
            'ROTATE': '<span class="badge bg-info-subtile text-info border border-info text-xxxs" style="font-size:9px;">ROTAC</span>',
            'REMOVE': '<span class="badge bg-warning-subtile text-warning border border-warning text-xxxs" style="font-size:9px;">RETIRO</span>',
            'TRANSFER': '<span class="badge bg-secondary-subtile text-secondary border border-secondary text-xxxs" style="font-size:9px;">TRASL</span>',
            'SCRAP': '<span class="badge bg-danger-subtile text-danger border border-danger text-xxxs" style="font-size:9px;">BAJA</span>',
            'REPAIR_SEND': '<span class="badge bg-warning-subtile text-warning border border-warning text-xxxs" style="font-size:9px;">REP ENV</span>',
            'REPAIR_RETURN': '<span class="badge bg-success-subtile text-success border border-success text-xxxs" style="font-size:9px;">REP RET</span>'
        };
        return badges[action] || '<span class="badge bg-light text-dark border text-xxxs" style="font-size:9px;">' + escapeHtml(action) + '</span>';
    }

    /**
     * Obtiene el icono SVG del tipo de movimiento
     */
    function getActionIcon(actionType) {
        var action = (actionType || '').toUpperCase();
        var icons = {
            'ASSIGN': '<svg class="svg-inline--fa fa-arrow-right-to-bracket text-primary" viewBox="0 0 512 512" style="width:12px;"><path fill="currentColor" d="M217.9 105.9L340.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L217.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1L32 320c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM352 416l64 0c17.7 0 32-14.3 32-32l0-256c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32-14.3-32-32s14.3-32 32-32l64 0c53 0 96 43 96 96l0 256c0 53-43 96-96 96l-64 0c-17.7 0-32-14.3-32-32s14.3-32 32-32z"></path></svg>',
            'ROTATE': '<svg class="svg-inline--fa fa-rotate text-info" viewBox="0 0 512 512" style="width:12px;"><path fill="currentColor" d="M142.9 142.9c-17.2 17.2-28.9 38.1-35.4 60.8c-3.7 12.8-16 20.2-28.8 16.5s-20.2-16-16.5-28.8c8.4-29 23.2-55.9 44-76.7C136.5 84.7 178.3 64 224 64c72.6 0 134.8 36.9 170.3 92.1l-25.4 25.4c-9.3 9.3-24.1 2.9-24.1-10.3l0-50.9c0-17.8-14.4-32.2-32.2-32.2l-50.9 0c-13.2 0-19.6 14.8-10.3 24.1l19.5 19.5C244.9 132.8 210.4 128 179.2 128c-17.5 0-34.3 3.4-50.2 9.6c-11.6 4.5-23.9 9.9-35.4 18.5zM64 256l.1 8.9c0 5.3 1 10.6 3 15.6L89.3 339c2.2 5.5 6.2 10.1 11.1 13.1c7.2 4.4 15.9 5.9 24.2 4.2s15.4-6.1 19.8-13.3c4.4-7.2 5.9-15.9 4.2-24.2s-6.1-15.4-13.3-19.8l-16.7-10.3c2-8.9 3-18.1 3-27.5l0-8.9c0-10.7-2-21-5.9-30.5l-3.9-9.5c-2.2-5.5-6.2-10.1-11.1-13.1c-7.2-4.4-15.9-5.9-24.2-4.2s-15.4 6.1-19.8 13.3c-4.4 7.2-5.9 15.9-4.2 24.2s6.1 15.4 13.3 19.8L80.1 286c-1.1 4.3-1.8 8.7-2 13.3L64 256zm64 112c0-8.8 7.2-16 16-16l160 0c8.8 0 16 7.2 16 16l0 128 32 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-80 0c-13.3 0-24-10.7-24-24l0-56-104 0c-8.8 0-16-7.2-16-16l0-96z"></path></svg>',
            'REMOVE': '<svg class="svg-inline--fa fa-arrow-right-from-bracket text-warning" viewBox="0 0 512 512" style="width:12px;"><path fill="currentColor" d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 192 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l210.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128zM160 96c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 32C60.7 32 32 60.7 32 96l0 320c0 35.3 28.7 64 64 64l64 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-64 0 0-320 64 0z"></path></svg>',
            'TRANSFER': '<svg class="svg-inline--fa fa-truck-ramp-box text-secondary" viewBox="0 0 640 512" style="width:12px;"><path fill="currentColor" d="M640 0V400c0 61.9-50.1 112-112 112c-61 0-110.5-48.7-112-109.3L48.4 502.9c-17.1 4.6-34.7-5.4-39.3-22.5s5.4-34.7 22.5-39.3L352 353.8V64c0-35.3 28.7-64 64-64H640zM576 400a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM544 96V208H416V96H544z"></path></svg>',
            'SCRAP': '<svg class="svg-inline--fa fa-trash-can text-danger" viewBox="0 0 448 512" style="width:12px;"><path fill="currentColor" d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>',
            'REPAIR_SEND': '<svg class="svg-inline--fa fa-wrench text-warning" viewBox="0 0 512 512" style="width:12px;"><path fill="currentColor" d="M352 320c88.4 0 160-71.6 160-160c0-15.3-2.2-30.1-6.2-44.2c-3.1-10.8-16.4-13.2-24.3-5.3l-76.8 76.8c-3 3-7.1 4.7-11.3 4.7H336c-8.8 0-16-7.2-16-16V117.6c0-4.2 1.7-8.3 4.7-11.3l76.8-76.8c7.9-7.9 5.4-21.2-5.3-24.3C382.1 2.2 367.3 0 352 0C263.6 0 192 71.6 192 160c0 19.1 3.4 37.5 9.5 54.5L19.9 396.1C7.2 408.8 0 426.1 0 444.1C0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L297.5 310.5c17 6.2 35.4 9.5 54.5 9.5zM80 408a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"></path></svg>',
            'REPAIR_RETURN': '<svg class="svg-inline--fa fa-wrench text-success" viewBox="0 0 512 512" style="width:12px;"><path fill="currentColor" d="M352 320c88.4 0 160-71.6 160-160c0-15.3-2.2-30.1-6.2-44.2c-3.1-10.8-16.4-13.2-24.3-5.3l-76.8 76.8c-3 3-7.1 4.7-11.3 4.7H336c-8.8 0-16-7.2-16-16V117.6c0-4.2 1.7-8.3 4.7-11.3l76.8-76.8c7.9-7.9 5.4-21.2-5.3-24.3C382.1 2.2 367.3 0 352 0C263.6 0 192 71.6 192 160c0 19.1 3.4 37.5 9.5 54.5L19.9 396.1C7.2 408.8 0 426.1 0 444.1C0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L297.5 310.5c17 6.2 35.4 9.5 54.5 9.5zM80 408a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"></path></svg>'
        };
        return icons[action] || '<svg class="svg-inline--fa fa-circle text-muted" viewBox="0 0 512 512" style="width:12px;"><path fill="currentColor" d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512z"></path></svg>';
    }

    /**
     * Actualiza el estado vacío del resumen
     */
    function updateEmptyState() {
        if (!summaryList) return;
        
        var hasItems = State.llantasSeleccionadas.length > 0;
        
        if (emptyMessage) {
            emptyMessage.style.display = hasItems ? 'none' : 'block';
        }

        if (!hasItems && summaryList) {
            summaryList.innerHTML = '';
        }
    }

    /**
     * Vincula eventos a los botones de eliminar movimiento
     */
    function bindRemoveButtons() {
        if (!summaryList) return;

        var removeButtons = summaryList.querySelectorAll('.btn-remove-movement');
        removeButtons.forEach(function(btn) {
            btn.removeEventListener('click', handleRemoveMovement);
            btn.addEventListener('click', handleRemoveMovement);
        });
    }

    /**
     * Maneja la eliminación de un movimiento desde el resumen
     */
    function handleRemoveMovement(e) {
        var btn = e.currentTarget;
        var tireCode = btn.getAttribute('data-tire-code');
        var index = parseInt(btn.getAttribute('data-index'), 10);

        if (!tireCode && isNaN(index)) return;

        // Confirmar eliminación
        if (!confirm('¿Está seguro de eliminar el movimiento de la llanta ' + (tireCode || 'desconocida') + '?')) {
            return;
        }

        // Encontrar la llanta en el estado
        var llantaIndex = -1;
        if (tireCode) {
            for (var i = 0; i < State.llantasSeleccionadas.length; i++) {
                if (State.llantasSeleccionadas[i].tire_code === tireCode) {
                    llantaIndex = i;
                    break;
                }
            }
        } else if (!isNaN(index)) {
            llantaIndex = index;
        }

        if (llantaIndex === -1) {
            console.warn('[doc-tire-assignment-summary] No se encontró el movimiento a eliminar.');
            return;
        }

        var llanta = State.llantasSeleccionadas[llantaIndex];
        var action = (llanta.action_type || '').toUpperCase();

        // Si la llanta estaba asignada a una posición, liberar esa posición en el chasis
        if (action === 'ASSIGN' || action === 'TRANSFER' || action === 'ROTATE') {
            if (llanta.vehicle_code_to && llanta.position_to) {
                var chassisModule = window.DocTireAssignment.Chassis;
                if (chassisModule && chassisModule.updatePosition) {
                    chassisModule.updatePosition(llanta.vehicle_code_to, llanta.position_to, '', '', '');
                }
            }
        }

        // Si la llanta estaba asignada desde el almacén, mostrarla de nuevo
        if (action === 'ASSIGN' || action === 'TRANSFER') {
            var warehouseModule = window.DocTireAssignment.TireWarehouse;
            if (warehouseModule && warehouseModule.showTireInWarehouse) {
                warehouseModule.showTireInWarehouse(tireCode);
            }
        }

        // Si la llanta fue retirada y eliminamos el movimiento, restaurarla en su posición original
        if (action === 'REMOVE' || action === 'SCRAP') {
            if (llanta.vehicle_code_from && llanta.position_from) {
                var chassisModule = window.DocTireAssignment.Chassis;
                if (chassisModule && chassisModule.updatePosition) {
                    chassisModule.updatePosition(llanta.vehicle_code_from, llanta.position_from, tireCode, llanta.tire_name || tireCode, '');
                }
            }
        }

        // Eliminar del estado global
        State.llantasSeleccionadas.splice(llantaIndex, 1);
        State.markDirty();

        // Refrescar resumen
        refreshSummary();

        // Emitir evento
        Events.emit('movement:removed', {
            tireCode: tireCode,
            actionType: action
        });
    }

    /**
     * Escapa HTML para evitar XSS
     */
    function escapeHtml(str) {
        if (typeof str !== 'string') return String(str || '');
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }

    // =========================================================================
    // EXPOSICIÓN PÚBLICA
    // =========================================================================

    /**
     * Inicializa el módulo de resumen de movimientos
     */
    module.initSummary = function() {
        init();
    };

    /**
     * Expone refresh para uso externo
     */
    module.refreshSummary = function() {
        refreshSummary();
    };

})(window.DocTireAssignment);
