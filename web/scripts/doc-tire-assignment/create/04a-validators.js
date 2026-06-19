/**
 * validators.js
 * 
 * Responsabilidad: Validación centralizada de operaciones de Drag & Drop.
 * Desacoplado de 05-dragdrop.js para facilitar mantenimiento y testing.
 * 
 * Exporta: module.Validators con canDrag(), canDrop(), _isSizeCompatible()
 * 
 * Uso:
 *   var result = Validators.canDrop(zoneElement, dragData);
 *   if (!result.valid) { console.warn(result.reason); }
 */

(function(module) {
    'use strict';

    var State = module.State;

    var Validators = {
        /**
         * Valida si una llanta puede ser arrastrada
         * @param {Object} tireData - { tireCode, locked?, ... }
         * @returns {{ valid: boolean, reason: string }}
         */
        canDrag: function(tireData) {
            if (!tireData || !tireData.tireCode) {
                console.warn('[04a-validators] canDrag: código inválido', tireData);
                return { valid: false, reason: 'Código de llanta inválido' };
            }
            console.log('[04a-validators] canDrag:', { tireCode: tireData.tireCode, valid: true });
            // Hook para bloqueo futuro: if (tireData.locked) { ... }
            return { valid: true, reason: '' };
        },

        /**
         * Valida si una llanta puede soltarse en una zona de destino
         * @param {HTMLElement} targetZone - .tyre-drop-zone destino
         * @param {Object} dragData - datos del elemento arrastrado
         * @returns {{ valid: boolean, reason: string, code?: string }}
         */
        canDrop: function(targetZone, dragData) {
            if (!targetZone) {
                console.warn('[04a-validators] canDrop: zona destino inválida');
                return { valid: false, reason: 'Zona de destino inválida', code: 'INVALID_TARGET' };
            }

            var hasTire = targetZone.getAttribute('data-has-tire') === 'true';
            var existingTireCode = targetZone.getAttribute('data-tire-code') || '';
            var positionKey = targetZone.getAttribute('data-position');
            var vehicleCode = targetZone.getAttribute('data-vehicle-code');

            console.log('[04a-validators] canDrop:', { dragTire: dragData.tireCode, targetPosition: positionKey, targetVehicle: vehicleCode, hasTire, existingTireCode });

            // Regla 1: Auto-drop (misma llanta)
            if (dragData.tireCode === existingTireCode) {
                console.warn('[04a-validators] canDrop: SELF_DROP - misma llanta');
                return { valid: false, reason: 'No puedes soltar la misma llanta', code: 'SELF_DROP' };
            }

            // Regla 2: Misma posición en el chasis
            if (dragData.sourceType === 'chassis' && dragData.sourcePosition === positionKey) {
                console.warn('[04a-validators] canDrop: SAME_POSITION - ya está aquí');
                return { valid: false, reason: 'Ya está en esta posición', code: 'SAME_POSITION' };
            }

            // Regla 3: Compatibilidad de tamaño (hook para validación futura)
            if (!this._isSizeCompatible(dragData.tireSize, targetZone)) {
                console.warn('[04a-validators] canDrop: INCOMPATIBLE_SIZE');
                return { valid: false, reason: 'Tamaño incompatible', code: 'INCOMPATIBLE_SIZE' };
            }

            // Regla 4: Llanta de almacén ya montada
            if (dragData.sourceType === 'warehouse') {
                var alreadyMounted = State.llantasSeleccionadas.some(function(l) {
                    return l.tire_code === dragData.tireCode && (l.action_type === 'ASSIGN' || l.action_type === 'TRANSFER');
                });
                if (alreadyMounted) {
                    console.warn('[04a-validators] canDrop: ALREADY_MOUNTED - llanta ya montada');
                    return { valid: false, reason: 'Esta llanta ya está montada', code: 'ALREADY_MOUNTED' };
                }
            }

            console.log('[04a-validators] canDrop: OK - drop permitido');
            return { valid: true, reason: '', code: 'OK' };
        },

        /**
         * Verifica compatibilidad de tamaño (hook)
         * @param {string} tireSize
         * @param {HTMLElement} targetZone
         * @returns {boolean}
         */
        _isSizeCompatible: function(tireSize, targetZone) {
            // Hook para validación futura de tamaño
            return true;
        }
    };

    // Exportar
    module.Validators = Validators;

})(window.DocTireAssignment);
