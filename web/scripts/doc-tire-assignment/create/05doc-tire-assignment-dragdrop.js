/**
 * 05doc-tire-assignment-dragdrop.js
 * Responsabilidad: Sistema de arrastrar y soltar (Drag & Drop) para el taller visual.
 * 
 * Arquitectura Enterprise:
 * - Validators: Validación centralizada con códigos de error
 * - DragHandlers: Feedback visual mediante clases CSS (sin inline styles)
 * - BusinessActions: Lógica de negocio (assign, move, swap, replace to staging)
 * - StagingManager: Cuarentena con event listeners (sin onclick inline)
 * - TrashDrop: Envía a staging, nunca baja directa
 * - DOMHelpers: Actualización del DOM delegada a Chassis module
 * 
 * Referencias DOM:
 * - #dynamic-truck-container: Chasis visual (delegado)
 * - #available-tires-container: Panel de llantas disponibles (NO #doc-details-table)
 * - #staging-zone: Zona de cuarentena
 * - #trash-drop-zone: Zona de baja
 */
(function(module) {
    'use strict';
    var State = module.State;
    var Events = module.Events;

    // =========================================================================
    // REFERENCIAS A ELEMENTOS DEL DOM
    // =========================================================================
    var container = null;
    var tireWarehouseContainer = null;
    var stagingZone = null;
    var trashZone = null;

    var dragData = {
        element: null,
        tireCode: '',
        tireName: '',
        tireSize: '',
        sourceType: '', // 'warehouse' | 'chassis'
        sourcePosition: '',
        sourceVehicle: '',
        sourceWarehouse: ''
    };

    var isDragging = false;
    var dropExecuted = false; // Bandera para evitar que handleDragEnd revierta cambios post-drop

    // =========================================================================
    // VALIDATORS - Resolución lazy con fallback inline
    // Si validators.js (04a) no se cargó por orden/caché, usa implementación
    // mínima para no bloquear la funcionalidad.
    // =========================================================================
    function getValidators() {
        if (module.Validators) return module.Validators;
        // Fallback inline: permite drag libre y validación básica
        return {
            canDrag: function() { return { valid: true, reason: '' }; },
            canDrop: function() { return { valid: true, reason: '', code: 'OK' }; },
            _isSizeCompatible: function() { return true; }
        };
    }

    // =========================================================================
    // INICIALIZACIÓN
    // =========================================================================
    function init() {
        container = document.getElementById('dynamic-truck-container');
        trashZone = document.getElementById('trash-drop-zone');
        tireWarehouseContainer = document.getElementById('available-tires-container');
        stagingZone = document.getElementById('staging-zone');

        if (!container) {
            console.warn('[05-dragdrop] #dynamic-truck-container no encontrado.');
            return;
        }

        // Inicializar StagingManager externo
        if (module.StagingManager && module.StagingManager.init) {
            module.StagingManager.init();
        }

        initChassisDragDrop();
        initTrashZone();
        initStagingZone();

        Events.on('chassis:rendered', function() {
            // Los eventos están delegados, no necesita re-inicialización
        });

        Events.on('tire:warehouse:rendered', function() {
            initWarehouseDrag();
        });

        // Inicializar drag desde staging
        initStagingDrag();

        console.log('[05-dragdrop] Módulo inicializado.');
    }

    /**
     * Inicializa eventos de drag & drop en el chasis
     */
    function initChassisDragDrop() {
        if (!container) return;
        container.addEventListener('dragstart', handleDragStart, false);
        container.addEventListener('dragend', handleDragEnd, false);
        container.addEventListener('dragover', handleDragOver, false);
        container.addEventListener('dragenter', handleDragEnter, false);
        container.addEventListener('dragleave', handleDragLeave, false);
        container.addEventListener('drop', handleDrop, false);
    }

    /**
     * Inicializa la zona de retiro (trash)
     */
    function initTrashZone() {
        if (!trashZone) return;
        trashZone.addEventListener('dragover', handleTrashDragOver, false);
        trashZone.addEventListener('dragleave', handleTrashDragLeave, false);
        trashZone.addEventListener('drop', handleTrashDrop, false);
    }

    /**
     * Inicializa la zona de Staging (Cuarentena)
     */
    function initStagingZone() {
        if (!stagingZone) return;
        stagingZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            stagingZone.classList.add('drag-over');
        });
        stagingZone.addEventListener('dragleave', function(e) {
            stagingZone.classList.remove('drag-over');
        });
        stagingZone.addEventListener('drop', handleStagingDrop, false);
    }

    /**
     * Inicializa el drag en el almacén de llantas (tyre-object DIVs)
     */
    function initWarehouseDrag() {
        if (!tireWarehouseContainer) return;
        // Delegación: los .tyre-object dentro de #available-tires-container disparan eventos
        tireWarehouseContainer.addEventListener('dragstart', handleWarehouseDragStart, false);
        tireWarehouseContainer.addEventListener('dragend', handleDragEnd, false);
    }

    /**
     * Inicializa el drag en la zona de staging (staging-tire-object cards)
     */
    function initStagingDrag() {
        var stagingContainer = document.getElementById('staging-zone');
        if (!stagingContainer) return;
        stagingContainer.addEventListener('dragstart', handleStagingDragStart, false);
        stagingContainer.addEventListener('dragend', handleDragEnd, false);
    }

    // =========================================================================
    // DRAG HANDLERS - Feedback visual mediante clases CSS
    // =========================================================================

    function handleDragStart(e) {
        var target = e.target.closest('.tyre-drop-zone');
        if (!target || target.getAttribute('data-has-tire') !== 'true') {
            e.preventDefault();
            return;
        }

        var tireCode = target.getAttribute('data-tire-code');
        if (!tireCode) return;

        // Validar antes de permitir drag
        var validResult = getValidators().canDrag({ tireCode: tireCode });
        if (!validResult.valid) {
            e.preventDefault();
            console.warn('[05-dragdrop] canDrag falló:', validResult.reason);
            return;
        }

        console.log('[05-dragdrop] handleDragStart (chassis):', { tireCode, position: target.getAttribute('data-position'), vehicle: target.getAttribute('data-vehicle-code') });
        isDragging = true;
        dragData = {
            element: target,
            tireCode: tireCode,
            tireName: target.querySelector('.fw-bold') ? target.querySelector('.fw-bold').textContent.trim() : tireCode,
            tireSize: target.querySelector('.text-xxxs') ? target.querySelector('.text-xxxs').textContent.trim() : '',
            sourceType: 'chassis',
            sourcePosition: target.getAttribute('data-position'),
            sourceVehicle: target.getAttribute('data-vehicle-code'),
            sourceWarehouse: ''
        };    
        console.log('[05-dragdrop] dragData set (chassis):', dragData);

        e.dataTransfer.setData('text/plain', JSON.stringify({
            type: 'chassis',
            tireCode: tireCode
        }));
        e.dataTransfer.effectAllowed = 'move';
        target.classList.add('dragging');
    }

    function handleWarehouseDragStart(e) {
        var target = e.target.closest('.tyre-object');
        if (!target) return;

        var tireCode = target.getAttribute('data-tire-code');
        if (!tireCode) return;

        // Validar antes de permitir drag
        var validResult = getValidators().canDrag({ tireCode: tireCode });
        if (!validResult.valid) {
            e.preventDefault();
            console.warn('[05-dragdrop] canDrag (warehouse) falló:', validResult.reason);
            return;
        }

        console.log('[05-dragdrop] handleWarehouseDragStart:', { tireCode, tireName: target.getAttribute('data-tire-name'), tireSize: target.getAttribute('data-tire-size') });
        isDragging = true;
        dragData = {
            element: target,
            tireCode: tireCode,
            tireName: target.getAttribute('data-tire-name') || tireCode,
            tireSize: target.getAttribute('data-tire-size') || '',
            sourceType: 'warehouse',
            sourcePosition: '',
            sourceVehicle: '',
            sourceWarehouse: 'MAIN'
        };
        console.log('[05-dragdrop] dragData set (warehouse):', dragData);

        e.dataTransfer.setData('text/plain', JSON.stringify({
            type: 'warehouse',
            tireCode: tireCode
        }));
        e.dataTransfer.effectAllowed = 'move';
        target.classList.add('dragging');
    }

    function handleStagingDragStart(e) {
        var target = e.target.closest('.staging-tire-object');
        if (!target) return;

        var tireCode = target.getAttribute('data-tire-code');
        if (!tireCode) return;

        // No arrastrar si se hizo clic en un botón de acción
        if (e.target.closest('.staging-action-btn')) {
            e.preventDefault();
            return;
        }

        console.log('[05-dragdrop] handleStagingDragStart:', { tireCode });
        isDragging = true;
        dragData = {
            element: target,
            tireCode: tireCode,
            tireName: tireCode,
            tireSize: '',
            sourceType: 'staging',
            sourcePosition: '',
            sourceVehicle: '',
            sourceWarehouse: ''
        };
        console.log('[05-dragdrop] dragData set (staging):', dragData);

        e.dataTransfer.setData('text/plain', JSON.stringify({
            type: 'staging',
            tireCode: tireCode
        }));
        e.dataTransfer.effectAllowed = 'move';
        target.classList.add('dragging');
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';

        var dropZone = e.target.closest('.tyre-drop-zone');
        if (!dropZone) return;

        var validResult = getValidators().canDrop(dropZone, dragData);
        if (validResult.valid) {
            dropZone.classList.remove('invalid-target');
            dropZone.classList.add('valid-target');
        } else {
            dropZone.classList.remove('valid-target');
            dropZone.classList.add('invalid-target');
        }
    }

    function handleDragEnter(e) {
        e.preventDefault();
    }

    function handleDragLeave(e) {
        var dropZone = e.target.closest('.tyre-drop-zone');
        if (dropZone) {
            dropZone.classList.remove('valid-target', 'invalid-target');
        }
    }

    function handleDragEnd(e) {
        console.log('[05-dragdrop] handleDragEnd: drag terminado', {
            sourceType: dragData.sourceType,
            tireCode: dragData.tireCode,
            dropExecuted: dropExecuted
        });

        // Si el drop ya se ejecutó, no restaurar nada
        if (dropExecuted) {
            console.log('[05-dragdrop] Drop ya ejecutado, limpiando sin restaurar');
            if (dragData.element) {
                dragData.element.classList.remove('dragging');
            }
            isDragging = false;
            dragData = {};
            dropExecuted = false;
            return;
        }

        // Si el drag se canceló (sin drop válido) y venía del warehouse, restaurar visibilidad
        if (isDragging && dragData.sourceType === 'warehouse' && dragData.tireCode) {
            showTireInWarehouse(dragData.tireCode);
        }

        if (dragData.element) {
            dragData.element.classList.remove('dragging');
        }
        if (stagingZone) {
            stagingZone.classList.remove('drag-over');
        }
        if (trashZone) {
            trashZone.classList.remove('drag-over');
        }

        // Limpiar clases de todas las drop zones
        var zones = document.querySelectorAll('.tyre-drop-zone');
        zones.forEach(function(z) {
            z.classList.remove('valid-target', 'invalid-target');
        });

        isDragging = false;
        dragData = {};
        dropExecuted = false;
    }

    function handleTrashDragOver(e) {
        e.preventDefault();
        if (dragData.sourceType === 'chassis') {
            trashZone.classList.add('drag-over');
        }
    }

    function handleTrashDragLeave(e) {
        trashZone.classList.remove('drag-over');
    }

    // =========================================================================
    // DROP HANDLER - Router de 4 casos
    // =========================================================================

    function handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        var dropZone = e.target.closest('.tyre-drop-zone');
        if (!dropZone) return;

        var positionKey = dropZone.getAttribute('data-position');
        var vehicleCode = dropZone.getAttribute('data-vehicle-code');
        var hasTire = dropZone.getAttribute('data-has-tire') === 'true';
        var existingTireCode = dropZone.getAttribute('data-tire-code') || '';

        console.log('[05-dragdrop] handleDrop:', { positionKey, vehicleCode, hasTire, existingTireCode, sourceType: dragData.sourceType, dragTireCode: dragData.tireCode });

        if (!isDragging || !dragData.sourceType) {
            Toast.fire({icon: 'error',title: 'Nno está en arrastre o sin origen'});
            console.warn('[05-dragdrop] Drop rechazado: no está en arrastre o sin sourceType');
            return;
        }

        // Validar drop
        var validResult = getValidators().canDrop(dropZone, dragData);
        if (!validResult.valid) {
            Toast.fire({icon: 'error',title: validResult.reason + ' No se puede soltar aquí'});
            console.warn('[05-dragdrop] Drop rechazado:', validResult.reason, '(code:', validResult.code + ')');
            return;
        }

        // *** ROUTER DE 4 CASOS ***
        // Caso 1: warehouse → vacío = Asignación
        if (dragData.sourceType === 'warehouse' && !hasTire) {
            dropExecuted = true;
            assignTireToPosition(dragData.tireCode, dragData.tireName, dragData.tireSize, vehicleCode, positionKey);
            return;
        }

        // Caso 2: chassis → vacío = Movimiento/Rotación
        if (dragData.sourceType === 'chassis' && !hasTire) {
            dropExecuted = true;
            moveTireToPosition(dragData.tireCode, dragData.tireName, dragData.tireSize,
                               dragData.sourcePosition, dragData.sourceVehicle,
                               vehicleCode, positionKey);
            return;
        }

        // Caso 3: chassis → ocupado = Intercambio (con confirmación)
        if (dragData.sourceType === 'chassis' && hasTire) {
            e.preventDefault();
            // Capturar valores ANTES de que dragData se limpie en handleDragEnd
            var capturedDragData = {
                tireCode: dragData.tireCode,
                tireName: dragData.tireName,
                tireSize: dragData.tireSize,
                sourceType: dragData.sourceType,
                sourcePosition: dragData.sourcePosition,
                sourceVehicle: dragData.sourceVehicle,
                sourceWarehouse: dragData.sourceWarehouse
            };
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Intercambiar llantas?',
                    html: '<p>La posición <strong>' + escapeHtml(positionKey) + '</strong> ya tiene montada <strong>' + escapeHtml(existingTireCode) + '</strong>.</p><p>Se intercambiarán ambas llantas.</p>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, intercambiar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        dropExecuted = true;
                        swapTires(capturedDragData, {
                            tireCode: existingTireCode,
                            position: positionKey,
                            vehicleCode: vehicleCode,
                            zone: dropZone
                        });
                    }
                });
            } else {
                // Fallback si SweetAlert2 no está disponible
                swapTires(capturedDragData, {
                    tireCode: existingTireCode,
                    position: positionKey,
                    vehicleCode: vehicleCode,
                    zone: dropZone
                });
            }
            return;
        }

        // Caso 4: warehouse → ocupado = Reemplazo directo a staging (con confirmación)
        if (dragData.sourceType === 'warehouse' && hasTire) {
            e.preventDefault();
            // Capturar valores ANTES de que dragData se limpie en handleDragEnd
            var capturedNewCode = dragData.tireCode;
            var capturedNewName = dragData.tireName;
            var capturedNewSize = dragData.tireSize;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Reemplazar llanta?',
                    html: '<p>La posición <strong>' + escapeHtml(positionKey) + '</strong> tiene montada <strong>' + escapeHtml(existingTireCode) + '</strong>.</p><p>La llanta existente pasará a la zona de staging (cuarentena).</p>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, reemplazar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        dropExecuted = true;
                        executeReplaceToStaging(capturedNewCode, capturedNewName, capturedNewSize,
                                                existingTireCode, vehicleCode, positionKey);
                    }
                });
            } else {
                // Fallback si SweetAlert2 no está disponible
                executeReplaceToStaging(capturedNewCode, capturedNewName, capturedNewSize,
                                        existingTireCode, vehicleCode, positionKey);
            }
            return;
        }

        // Caso 5: staging → vacío = Reasignar desde staging
        if (dragData.sourceType === 'staging' && !hasTire) {
            dropExecuted = true;
            // Remover la card de staging
            var stagingCard = document.querySelector('.staging-tire-object[data-tire-code="' + dragData.tireCode + '"]');
            if (stagingCard) {
                stagingCard.remove();
                var emptyMsg = document.getElementById('staging-empty-msg');
                if (emptyMsg && stagingZone && stagingZone.children.length <= 1) {
                    emptyMsg.style.display = 'block';
                }
            }
            // Eliminar el registro de REMOVE/SCRAP del state
            var idx = -1;
            for (var i = 0; i < State.llantasSeleccionadas.length; i++) {
                if (State.llantasSeleccionadas[i].tire_code === dragData.tireCode &&
                    (State.llantasSeleccionadas[i].action_type === 'REMOVE' || State.llantasSeleccionadas[i].action_type === 'SCRAP')) {
                    idx = i;
                    break;
                }
            }
            if (idx !== -1) {
                State.llantasSeleccionadas.splice(idx, 1);
            }
            // Asignar al chasis
            assignTireToPosition(dragData.tireCode, dragData.tireName, dragData.tireSize, vehicleCode, positionKey);
            return;
        }

        // Caso 6: staging → ocupado = Reemplazar desde staging (con confirmación)
        if (dragData.sourceType === 'staging' && hasTire) {
            e.preventDefault();
            var capturedStagingCode = dragData.tireCode;
            var capturedStagingName = dragData.tireName;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Reemplazar llanta desde staging?',
                    html: '<p>La posición <strong>' + escapeHtml(positionKey) + '</strong> tiene montada <strong>' + escapeHtml(existingTireCode) + '</strong>.</p><p>La llanta de staging reemplazará a la actual, que pasará a cuarentena.</p>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, reemplazar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        dropExecuted = true;
                        // Remover la card de staging
                        var stagingCard = document.querySelector('.staging-tire-object[data-tire-code="' + capturedStagingCode + '"]');
                        if (stagingCard) {
                            stagingCard.remove();
                            var emptyMsg = document.getElementById('staging-empty-msg');
                            if (emptyMsg && stagingZone && stagingZone.children.length <= 1) {
                                emptyMsg.style.display = 'block';
                            }
                        }
                        // Eliminar el registro de REMOVE/SCRAP del state
                        var idx = -1;
                        for (var i = 0; i < State.llantasSeleccionadas.length; i++) {
                            if (State.llantasSeleccionadas[i].tire_code === capturedStagingCode &&
                                (State.llantasSeleccionadas[i].action_type === 'REMOVE' || State.llantasSeleccionadas[i].action_type === 'SCRAP')) {
                                idx = i;
                                break;
                            }
                        }
                        if (idx !== -1) {
                            State.llantasSeleccionadas.splice(idx, 1);
                        }
                        // La llanta existente va a staging
                        addToStagingZone(existingTireCode, vehicleCode, positionKey);
                        // La llanta de staging se asigna al chasis
                        assignTireToPosition(capturedStagingCode, capturedStagingName, '', vehicleCode, positionKey);
                    }
                });
            } else {
                // Fallback
                executeReplaceToStaging(capturedStagingCode, capturedStagingName, '',
                                        existingTireCode, vehicleCode, positionKey);
            }
            return;
        }
    }

    function handleTrashDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!isDragging || dragData.sourceType !== 'chassis') {
            console.warn('[05-dragdrop] TrashDrop rechazado: no es desde chasis');
            return;
        }

        console.log('[05-dragdrop] handleTrashDrop: enviando a staging', { tireCode: dragData.tireCode, sourceVehicle: dragData.sourceVehicle, sourcePosition: dragData.sourcePosition });
        // Enviar a staging, nunca baja directa
        addToStagingZone(dragData.tireCode, dragData.sourceVehicle, dragData.sourcePosition);
        
        // Limpiar posición en chasis
        updatePositionInDOM(dragData.sourceVehicle, dragData.sourcePosition, '', '', '');
        
        // Registrar en State
        updateTireInState(dragData.tireCode, dragData.tireName, {
            action_type: 'REMOVE',
            vehicle_code_from: dragData.sourceVehicle,
            position_from: dragData.sourcePosition,
            warehouse_code_to: 'PENDING'
        });
        
        State.markDirty();
        Events.emit('tire:removed', { tireCode: dragData.tireCode });
    }

    /**
     * Handle drop en la zona de staging (cuarentena)
     * Acepta llantas desde chasis (REMOVE) y desde warehouse (asignación directa a staging)
     */
    function handleStagingDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!isDragging || !dragData.tireCode) {
            console.warn('[05-dragdrop] StagingDrop rechazado: no hay drag activo');
            return;
        }

        console.log('[05-dragdrop] handleStagingDrop:', { tireCode: dragData.tireCode, sourceType: dragData.sourceType });

        if (dragData.sourceType === 'chassis') {
            // Misma lógica que TrashDrop: enviar a staging y limpiar chasis
            addToStagingZone(dragData.tireCode, dragData.sourceVehicle, dragData.sourcePosition);
            updatePositionInDOM(dragData.sourceVehicle, dragData.sourcePosition, '', '', '');
            updateTireInState(dragData.tireCode, dragData.tireName, {
                action_type: 'REMOVE',
                vehicle_code_from: dragData.sourceVehicle,
                position_from: dragData.sourcePosition,
                warehouse_code_to: 'PENDING'
            });
            State.markDirty();
            Events.emit('tire:removed', { tireCode: dragData.tireCode });
        } else if (dragData.sourceType === 'warehouse') {
            // Asignar directamente a staging (ej: llanta dañada)
            addToStagingZone(dragData.tireCode, '', '');
            // Marcar como asignada en warehouse
            if (module.markTireAsAssigned) {
                module.markTireAsAssigned(dragData.tireCode);
            }
            updateTireInState(dragData.tireCode, dragData.tireName, {
                action_type: 'REMOVE',
                vehicle_code_from: '',
                position_from: '',
                warehouse_code_to: 'PENDING'
            });
            State.markDirty();
            Events.emit('tire:removed', { tireCode: dragData.tireCode });
        }
    }

    // =========================================================================
    // BUSINESS ACTIONS - Lógica de negocio
    // =========================================================================

    function assignTireToPosition(tireCode, tireName, tireSize, vehicleCode, positionKey) {
        console.log('[05-dragdrop] assignTireToPosition:', { tireCode, tireName, tireSize, vehicleCode, positionKey });
        // PRIMERO: marcar en warehouse (sincronización bidireccional)
        removeTireFromWarehouse(tireCode);
        // SEGUNDO: actualizar chasis
        updatePositionInDOM(vehicleCode, positionKey, tireCode, tireName, tireSize);

        State.llantasSeleccionadas.push({
            tire_code: tireCode,
            tire_name: tireName,
            action_type: 'ASSIGN',
            vehicle_code_to: vehicleCode,
            position_to: positionKey,
            warehouse_code_from: 'MAIN'
        });

        State.markDirty();
        Events.emit('tire:assigned', { tireCode: tireCode, position: positionKey, vehicleCode: vehicleCode });
    }

    function moveTireToPosition(tireCode, tireName, tireSize, fromPos, fromVeh, toVeh, toPos) {
        console.log('[05-dragdrop] moveTireToPosition:', { tireCode, fromPos, fromVeh, toVeh, toPos });
        updatePositionInDOM(fromVeh, fromPos, '', '', '');
        updatePositionInDOM(toVeh, toPos, tireCode, tireName, tireSize);

        var detail = State.llantasSeleccionadas.find(function(l) { return l.tire_code === tireCode; });
        if (detail) {
            detail.action_type = (fromVeh === toVeh) ? 'ROTATE' : 'TRANSFER';
            detail.position_from = fromPos;
            detail.position_to = toPos;
            detail.vehicle_code_from = fromVeh;
            detail.vehicle_code_to = toVeh;
        }

        State.markDirty();
        Events.emit('tire:moved', { tireCode: tireCode, fromPosition: fromPos, toPosition: toPos, fromVehicle: fromVeh, toVehicle: toVeh });
    }

    function swapTires(sourceData, targetData) {
        console.log('[05-dragdrop] swapTires:', { source: sourceData.tireCode, target: targetData.tireCode, targetPosition: targetData.position });
        // Intercambiar en DOM
        updatePositionInDOM(targetData.vehicleCode, targetData.position, sourceData.tireCode, sourceData.tireName, sourceData.tireSize);
        updatePositionInDOM(sourceData.sourceVehicle, sourceData.sourcePosition, targetData.tireCode, '', targetData.tireSize);

        // Actualizar State para ambas
        updateTireInState(sourceData.tireCode, sourceData.tireName, {
            action_type: 'ROTATE',
            related_tire_code: targetData.tireCode,
            vehicle_code_from: sourceData.sourceVehicle,
            position_from: sourceData.sourcePosition,
            vehicle_code_to: targetData.vehicleCode,
            position_to: targetData.position
        });

        updateTireInState(targetData.tireCode, '', {
            action_type: 'ROTATE',
            related_tire_code: sourceData.tireCode,
            vehicle_code_from: targetData.vehicleCode,
            position_from: targetData.position,
            vehicle_code_to: sourceData.sourceVehicle,
            position_to: sourceData.sourcePosition
        });

        State.markDirty();
        Events.emit('tire:swapped', { sourceTire: sourceData.tireCode, targetTire: targetData.tireCode });
    }

    function executeReplaceToStaging(newCode, newName, newSize, oldCode, vehicleCode, positionKey) {
        console.log('[05-dragdrop] executeReplaceToStaging:', { newCode, oldCode, vehicleCode, positionKey });
        // PRIMERO: marcar en warehouse (sincronización bidireccional)
        removeTireFromWarehouse(newCode);
        // SEGUNDO: poner nueva llanta en chasis
        updatePositionInDOM(vehicleCode, positionKey, newCode, newName, newSize);

        // Registrar nueva asignación
        updateTireInState(newCode, newName, {
            action_type: 'ASSIGN',
            vehicle_code_to: vehicleCode,
            position_to: positionKey,
            warehouse_code_from: 'MAIN'
        });

        // Enviar vieja a staging
        addToStagingZone(oldCode, vehicleCode, positionKey);

        // Registrar vieja como removida
        updateTireInState(oldCode, '', {
            action_type: 'REMOVE',
            vehicle_code_from: vehicleCode,
            position_from: positionKey,
            warehouse_code_to: 'PENDING'
        });

        State.markDirty();
        Events.emit('tire:replaced', { oldTire: oldCode, newTire: newCode, position: positionKey });
    }

    // =========================================================================
    // STAGING MANAGER - Referencia al módulo externo staging-manager.js
    // =========================================================================

    function addToStagingZone(tireCode, vehicleCode, positionKey) {
        if (module.StagingManager && module.StagingManager.addToStagingZone) {
            module.StagingManager.addToStagingZone(tireCode, vehicleCode, positionKey);
        }
    }

    function sendToDest(destino, tireCode) {
        if (module.StagingManager && module.StagingManager.sendToDest) {
            module.StagingManager.sendToDest(destino, tireCode);
        }
    }

    // =========================================================================
    // DOM HELPERS - Actualización delegada y utilidades
    // =========================================================================

    function updatePositionInDOM(vehicleCode, positionKey, tireCode, tireName, tireSize) {
        var updatePos = window.DocTireAssignment.updatePosition;
        if (typeof updatePos === 'function') {
            updatePos(vehicleCode, positionKey, tireCode, tireName, tireSize);
        } else {
            console.warn('[dragdrop] window.DocTireAssignment.updatePosition no es una función.');
        }
    }

    function removeTireFromWarehouse(tireCode) {
        if (!tireWarehouseContainer) return;
        var tyre = tireWarehouseContainer.querySelector('.tyre-object[data-tire-code="' + tireCode + '"]');
        if (tyre) {
            tyre.classList.add('tire-assigned');
        }
    }

    function showTireInWarehouse(tireCode) {
        if (!tireWarehouseContainer) return;
        var tyre = tireWarehouseContainer.querySelector('.tyre-object[data-tire-code="' + tireCode + '"]');
        if (tyre) {
            tyre.classList.remove('tire-assigned');
        }
    }

    function updateTireInState(tireCode, tireName, data) {
        var detail = State.llantasSeleccionadas.find(function(l) {
            return l.tire_code === tireCode;
        });

        if (detail) {
            Object.assign(detail, data);
        } else {
            State.llantasSeleccionadas.push(Object.assign({
                tire_code: tireCode,
                tire_name: tireName,
                action_type: 'ASSIGN'
            }, data));
        }
    }

    function escapeHtml(str) {
        if (typeof str !== 'string') return String(str || '');
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // =========================================================================
    // EXPORTACIÓN DEL MÓDULO
    // =========================================================================

    module.initDragDrop = function() {
        init();
    };

    module.DragDrop = {
        sendToDest: sendToDest,
        removeTireFromWarehouse: removeTireFromWarehouse,
        showTireInWarehouse: showTireInWarehouse,
        getValidators: getValidators,
        StagingManager: module.StagingManager
    };

})(window.DocTireAssignment);