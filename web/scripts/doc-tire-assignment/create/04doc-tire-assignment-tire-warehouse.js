/**
 * 04doc-tire-assignment-tire-warehouse.js
 * 
 * Responsabilidad: Gestión del almacén lateral de llantas disponibles.
 * Renderiza objetos visuales .tyre-object dentro de #available-tires-container.
 * Maneja búsqueda y filtrado de llantas.
 * Sincroniza con el estado global de llantas seleccionadas.
 * NO usa tablas — solo DIVs con clase .tyre-object.
 */

(function(module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;

    /**
     * Referencias a elementos del DOM
     */
    var warehouseContainer = null;
    var searchInput = null;
    var addDetailBtn = null;

    /**
     * Todas las llantas disponibles (caché para filtrado)
     */
    var allAvailableTires = [];

    /**
     * Inicializa el módulo del almacén de llantas
     */
    function init() {
        warehouseContainer = document.getElementById('available-tires-container');

        if (!warehouseContainer) {
            console.warn('[tire-warehouse] Elemento #available-tires-container no encontrado.');
            return;
        }

        searchInput = document.getElementById('search-available-tires');
        addDetailBtn = document.getElementById('add-detail-row');

        // Mostrar placeholder inicial
        renderEmptyPlaceholder();

        // Inicializar búsqueda
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                filterTires(e.target.value);
            });

            searchInput.addEventListener('search', function(e) {
                filterTires(e.target.value);
            });
        }

        // Escuchar eventos de llantas seleccionadas desde el modal
        Events.on('tires:selected', function(llantas) {
            if (!llantas || llantas.length === 0) return;

            var addedCount = 0;
            llantas.forEach(function(llanta) {
                var exists = allAvailableTires.some(function(t) {
                    return t.tire_code === llanta.tire_code;
                });
                if (!exists) {
                    allAvailableTires.push({
                        tire_code: llanta.tire_code,
                        tire_name: llanta.tire_name || llanta.tire_code,
                        tire_size: llanta.tire_size || '-',
                        tread_design: llanta.tread_design || '-',
                        tire_status: 'AV'
                    });
                    addedCount++;
                }
            });

            if (addedCount > 0) {
                renderWarehouse(allAvailableTires);
                if (typeof toastr !== 'undefined') {
                    toastr.success(addedCount + ' llanta(s) agregada(s) al almacén. Arrástrelas al chasis para asignarlas.', 'Almacén actualizado');
                }
            }
        });

        // Escuchar eventos de asignación/retiro
        Events.on('tire:assigned', function(data) {
            markTireAsAssigned(data.tireCode);
        });

        Events.on('tire:removed', function(data) {
            showTireInWarehouse(data.tireCode);
        });

        Events.on('tire:replaced', function(data) {
            showTireInWarehouse(data.oldTire);
            markTireAsAssigned(data.newTire);
        });

        Events.on('tire:moved', function() {
            // No afecta al almacén directamente
        });

        // Escuchar destino asignado desde staging (REASIGNAR o MAIN)
        Events.on('tire:destino:asignado', function(data) {
            if (data.destino === 'REASIGNAR' || data.destino === 'MAIN') {
                console.log('[tire-warehouse] destino asignado, re-agregando al almacén:', data);
                // Re-renderizar el warehouse completo para que la llanta aparezca
                renderWarehouse(allAvailableTires);
            }
        });
    }

    /**
     * Renderiza placeholder de vacío
     */
    function renderEmptyPlaceholder() {
        if (!warehouseContainer) return;
        warehouseContainer.innerHTML =
            '<div class="text-center text-muted py-4 small">' +
            '<i class="fa-solid fa-box-open fa-2x mb-2 opacity-25"></i><br>' +
            'Sin llantas cargadas.<br>Use "<strong>+ Nueva</strong>" para agregar.' +
            '</div>';
    }

    /**
     * Renderiza los objetos visuales .tyre-object en #available-tires-container
     */
    function renderWarehouse(tires) {
        if (!warehouseContainer) {
            console.warn('[tire-warehouse] warehouseContainer es null.');
            return;
        }

        if (!tires || tires.length === 0) {
            console.log('[tire-warehouse] renderWarehouse: sin llantas, mostrando placeholder');
            renderEmptyPlaceholder();
            Events.emit('tire:warehouse:rendered', []);
            return;
        }

        var mountedCodes = getMountedTireCodes();
        console.log('[tire-warehouse] renderWarehouse:', { total: tires.length, disponibles: tires.filter(function(t) { return mountedCodes.indexOf(t.tire_code) === -1 && !isTireInStateAsRemoved(t.tire_code); }).length });

        // Filtrar llantas montadas o removidas
        var availableTires = tires.filter(function(t) {
            return mountedCodes.indexOf(t.tire_code) === -1 &&
                   !isTireInStateAsRemoved(t.tire_code);
        });

        if (availableTires.length === 0) {
            warehouseContainer.innerHTML =
                '<div class="text-center text-muted py-4 small">' +
                '<i class="fa-solid fa-check-circle fa-2x mb-2 opacity-25 text-success"></i><br>' +
                'Todas las llantas están asignadas.' +
                '</div>';
            Events.emit('tire:warehouse:rendered', []);
            return;
        }

        var html = '';
        availableTires.forEach(function(tire) {
            var code = tire.tire_code || '';
            var name = tire.tire_name || code;
            var size = tire.tire_size || '-';

            html += '<div class="tyre-object" draggable="true" ' +
                    'data-tire-code="' + escapeHtml(code) + '" ' +
                    'data-tire-name="' + escapeHtml(name) + '" ' +
                    'data-tire-size="' + escapeHtml(size) + '" ' +
                    'data-status="available">';
            html += '  <span class="tyre-object-code">' + escapeHtml(code) + '</span>';
            html += '  <span class="tyre-object-size">' + escapeHtml(size) + '</span>';
            html += '</div>';
        });

        warehouseContainer.innerHTML = html;

        // Emitir evento para que dragdrop.js inicialice los drags
        Events.emit('tire:warehouse:rendered', availableTires);
    }

    /**
     * Filtra las llantas por texto de búsqueda (oculta/muestra .tyre-object)
     */
    function filterTires(searchText) {
        if (!warehouseContainer) return;

        var term = (searchText || '').toLowerCase().trim();
        console.log('[tire-warehouse] filterTires:', { term: term || '(vacío)' });

        var objects = warehouseContainer.querySelectorAll('.tyre-object');
        objects.forEach(function(obj) {
            var code = (obj.getAttribute('data-tire-code') || '').toLowerCase();
            var name = (obj.getAttribute('data-tire-name') || '').toLowerCase();
            var size = (obj.getAttribute('data-tire-size') || '').toLowerCase();

            if (!term ||
                code.indexOf(term) !== -1 ||
                name.indexOf(term) !== -1 ||
                size.indexOf(term) !== -1) {
                obj.classList.remove('tire-filtered-out');
            } else {
                obj.classList.add('tire-filtered-out');
            }
        });
    }

    /**
     * Marca una llanta como asignada (agrega clase .tire-assigned)
     */
    function markTireAsAssigned(tireCode) {
        if (!warehouseContainer) return;
        console.log('[tire-warehouse] markTireAsAssigned:', { tireCode });
        var tyre = warehouseContainer.querySelector('.tyre-object[data-tire-code="' + tireCode + '"]');
        if (tyre) {
            tyre.classList.add('tire-assigned');
        } else {
            console.warn('[tire-warehouse] markTireAsAssigned: llanta no encontrada en DOM', { tireCode });
        }
    }

    /**
     * Muestra una llanta de vuelta en el almacén (remueve .tire-assigned)
     */
    function showTireInWarehouse(tireCode) {
        if (!warehouseContainer) return;
        console.log('[tire-warehouse] showTireInWarehouse:', { tireCode });
        var tyre = warehouseContainer.querySelector('.tyre-object[data-tire-code="' + tireCode + '"]');
        if (tyre) {
            tyre.classList.remove('tire-assigned');
        } else {
            // Si no está en el DOM (fue filtrada), re-renderizar
            console.log('[tire-warehouse] showTireInWarehouse: no encontrada, re-renderizando');
            renderWarehouse(allAvailableTires);
        }
    }

    /**
     * Obtiene los códigos de llantas montadas en el chasis
     */
    function getMountedTireCodes() {
        var mountedCodes = [];

        var chassisModule = window.DocTireAssignment.Chassis;
        if (chassisModule && chassisModule.getAllPositions) {
            var positions = chassisModule.getAllPositions();
            positions.forEach(function(p) {
                if (p.hasTire && p.tireCode) {
                    mountedCodes.push(p.tireCode);
                }
            });
        }

        State.llantasSeleccionadas.forEach(function(l) {
            if (l.action_type === 'ASSIGN' || l.action_type === 'TRANSFER') {
                if (l.tire_code && mountedCodes.indexOf(l.tire_code) === -1) {
                    mountedCodes.push(l.tire_code);
                }
            }
        });

        return mountedCodes;
    }

    /**
     * Verifica si una llanta está marcada como retirada/baja en el estado
     */
    function isTireInStateAsRemoved(tireCode) {
        return State.llantasSeleccionadas.some(function(l) {
            return l.tire_code === tireCode &&
                   (l.action_type === 'REMOVE' || l.action_type === 'SCRAP');
        });
    }

    /**
     * Recarga el almacén
     */
    function reloadWarehouse() {
        allAvailableTires = [];
        renderEmptyPlaceholder();
    }

    /**
     * Agrega una llanta al almacén visual (desde modal)
     */
    function addTireToWarehouseFromModal(tireCode, tireName, tireSize, treadDesign) {
        var exists = allAvailableTires.some(function(t) {
            return t.tire_code === tireCode;
        });

        if (!exists) {
            allAvailableTires.push({
                tire_code: tireCode,
                tire_name: tireName || tireCode,
                tire_size: tireSize || '-',
                tread_design: treadDesign || '-',
                tire_status: 'AVAILABLE'
            });
        }

        renderWarehouse(allAvailableTires);
    }

    /**
     * Obtiene todas las llantas actualmente visibles en el almacén
     */
    function getVisibleWarehouseTires() {
        if (!warehouseContainer) return [];

        var tires = [];
        var objects = warehouseContainer.querySelectorAll('.tyre-object:not(.tire-assigned):not(.tire-filtered-out)');
        objects.forEach(function(obj) {
            tires.push({
                tireCode: obj.getAttribute('data-tire-code'),
                tireName: obj.getAttribute('data-tire-name'),
                tireSize: obj.getAttribute('data-tire-size'),
                element: obj
            });
        });
        return tires;
    }

    /**
     * Oculta completamente una llanta del almacén (remueve del DOM)
     * Diferencia de markTireAsAssigned: esta sí remueve el nodo, no solo lo deshabilita.
     * @param {string} tireCode - Código de la llanta a ocultar
     */
    function hideTire(tireCode) {
        if (!warehouseContainer) return;
        console.log('[tire-warehouse] hideTire:', { tireCode });
        var tyre = warehouseContainer.querySelector('.tyre-object[data-tire-code="' + tireCode + '"]');
        if (tyre) {
            tyre.remove();
        }
        // Si ya no quedan llantas visibles, mostrar placeholder
        var remaining = warehouseContainer.querySelectorAll('.tyre-object:not(.tire-assigned):not(.tire-filtered-out)');
        if (remaining.length === 0) {
            warehouseContainer.innerHTML =
                '<div class="text-center text-muted py-4 small">' +
                '<i class="fa-solid fa-check-circle fa-2x mb-2 opacity-25 text-success"></i><br>' +
                'Todas las llantas están asignadas.' +
                '</div>';
        }
    }

    /**
     * Obtiene los códigos de todas las llantas en el almacén
     */
    function getWarehouseTireCodes() {
        if (!warehouseContainer) return [];
        var codes = [];
        var objects = warehouseContainer.querySelectorAll('.tyre-object');
        objects.forEach(function(obj) {
            codes.push(obj.getAttribute('data-tire-code'));
        });
        return codes;
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

    /**
     * Retorna el arreglo completo de llantas disponibles (caché allAvailableTires)
     */
    function getAllAvailableTires() {
        return allAvailableTires;
    }

    // =========================================================================
    // INICIALIZACIÓN
    // =========================================================================

    module.initTireWarehouse = function() {
        init();
    };

    module.TireWarehouse = {
        getAllAvailableTires: getAllAvailableTires,
        getWarehouseTireCodes: getWarehouseTireCodes,
        getVisibleWarehouseTires: getVisibleWarehouseTires
    };

    module.reloadWarehouse = function() {
        reloadWarehouse();
    };

    module.addTireToWarehouseFromModal = function(tireCode, tireName, tireSize, treadDesign) {
        addTireToWarehouseFromModal(tireCode, tireName, tireSize, treadDesign);
    };

    module.getVisibleWarehouseTires = function() {
        return getVisibleWarehouseTires();
    };

    module.getWarehouseTireCodes = function() {
        return getWarehouseTireCodes();
    };

    module.filterTires = function(searchText) {
        filterTires(searchText);
    };

    module.markTireAsAssigned = function(tireCode) {
        markTireAsAssigned(tireCode);
    };

    module.showTireInWarehouse = function(tireCode) {
        showTireInWarehouse(tireCode);
    };

    module.hideTire = function(tireCode) {
        hideTire(tireCode);
    };

})(window.DocTireAssignment);