/**
 * staging-manager.js
 * 
 * Responsabilidad: Gestión de la zona de cuarentena (staging).
 * Maneja la creación de cards visuales, botones de destino (Taller/Almacén)
 * y la animación de salida al asignar destino final.
 * 
 * Exporta: module.StagingManager con addToStagingZone(), sendToDest()
 * 
 * Uso:
 *   StagingManager.addToStagingZone(tireCode, vehicleCode, positionKey);
 *   StagingManager.sendToDest('TALLER', tireCode);
 */

(function(module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;

    /**
     * Referencia al contenedor de staging
     */
    var stagingZone = null;

    /**
     * Inicializa el módulo con la referencia al DOM
     */
    function init() {
        stagingZone = document.getElementById('staging-zone');
    }

    /**
     * Agrega una llanta retirada a la zona de cuarentena
     * @param {string} tireCode - Código de la llanta
     * @param {string} vehicleCode - Unidad de origen
     * @param {string} positionKey - Posición de origen
     */
    function addToStagingZone(tireCode, vehicleCode, positionKey) {
        console.log('[04b-staging] addToStagingZone:', { tireCode, vehicleCode, positionKey });
        if (!stagingZone) {
            stagingZone = document.getElementById('staging-zone');
            if (!stagingZone) {
                console.warn('[04b-staging] #staging-zone no encontrado');
                return;
            }
        }

        var emptyMsg = document.getElementById('staging-empty-msg');
        if (emptyMsg) emptyMsg.style.display = 'none';

        var card = document.createElement('div');
        card.className = 'staging-tire-object';
        card.setAttribute('data-tire-code', tireCode);
        card.setAttribute('draggable', 'true');
        card.innerHTML = 
            '<div class="d-flex align-items-center gap-2">' +
            '<i class="fa-solid fa-triangle-exclamation text-danger"></i>' +
            '<div><strong class="d-block">' + escapeHtml(tireCode) + '</strong><small class="text-gray-100">Retirada de ' + escapeHtml(positionKey) + '</small></div>' +
            '</div>' +
            '<div class="row staging-action-group"></div>';

        var actionGroup = card.querySelector('.staging-action-group');
        
        var btnReasignar = document.createElement('button');
        btnReasignar.className = 'btn btn-outline-primary btn-sm staging-action-btn';
        btnReasignar.innerHTML = '<i class="fa-solid fa-arrow-rotate-left"></i> Reasignar';
        btnReasignar.title = 'Devolver al almacén para reasignar';
        btnReasignar.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            sendToDest('REASIGNAR', tireCode);
        });

        var btnTaller = document.createElement('button');
        btnTaller.className = 'btn btn-outline-warning btn-sm staging-action-btn';
        btnTaller.innerHTML = '<i class="fa-solid fa-wrench"></i> Taller';
        btnTaller.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            sendToDest('TALLER', tireCode);
        });

        var btnAlmacen = document.createElement('button');
        btnAlmacen.className = 'btn btn-outline-info btn-sm staging-action-btn';
        btnAlmacen.innerHTML = '<i class="fa-solid fa-warehouse"></i> Almacén';
        btnAlmacen.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            sendToDest('MAIN', tireCode);
        });

        actionGroup.appendChild(btnReasignar);
        actionGroup.appendChild(btnTaller);
        actionGroup.appendChild(btnAlmacen);
        stagingZone.appendChild(card);
    }

    /**
     * Envía una llanta a su destino final (Taller o Almacén)
     * @param {string} destino - 'TALLER' | 'MAIN'
     * @param {string} tireCode - Código de la llanta
     */
    function sendToDest(destino, tireCode) {
        console.log('[04b-staging] sendToDest:', { destino, tireCode });
        if (!stagingZone) {
            console.warn('[04b-staging] sendToDest: stagingZone no disponible');
            return;
        }

        var card = stagingZone.querySelector('.staging-tire-object[data-tire-code="' + tireCode + '"]');
        if (!card) {
            console.warn('[04b-staging] sendToDest: card no encontrada', { tireCode });
            return;
        }

        var detail = State.llantasSeleccionadas.find(function(l) {
            return l.tire_code === tireCode && (l.action_type === 'REMOVE' || l.action_type === 'SCRAP');
        });

        if (detail) {
            if (destino === 'REASIGNAR') {
                // Eliminar el registro de REMOVE/SCRAP porque la llanta vuelve a estar disponible
                var idx = State.llantasSeleccionadas.indexOf(detail);
                if (idx !== -1) {
                    State.llantasSeleccionadas.splice(idx, 1);
                }
            } else {
                detail.warehouse_code_to = destino === 'TALLER' ? 'TALLER' : 'MAIN';
            }
        }

        // Animación de salida
        card.style.opacity = '0';
        card.style.transform = 'translateX(20px)';
        
        setTimeout(function() {
            card.remove();
            if (stagingZone.children.length === 1) { // Solo queda el empty-msg
                var emptyMsg = document.getElementById('staging-empty-msg');
                if (emptyMsg) emptyMsg.style.display = 'block';
            }
        }, 300);

        State.markDirty();
        Events.emit('tire:destino:asignado', { tireCode: tireCode, destino: destino });
    }

    /**
     * Escape HTML para evitar XSS
     */
    function escapeHtml(str) {
        if (typeof str !== 'string') return String(str || '');
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Exportar
    module.StagingManager = {
        init: init,
        addToStagingZone: addToStagingZone,
        sendToDest: sendToDest
    };

})(window.DocTireAssignment);
