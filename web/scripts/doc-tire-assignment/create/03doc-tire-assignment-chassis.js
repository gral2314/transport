/**
 * 03doc-tire-assignment-chassis.js
 * Responsabilidad: Renderizado dinámico del chasis con soporte para ejes compuestos (Tandem).
 * Incluye Tooltips descriptivos para posiciones complejas.
 */
(function(module) {
    'use strict';
    var State = module.State;
    var Events = module.Events;

    var container = null;

    // Mapa de etiquetas legibles para tooltips y UI compacta
    var POSITION_LABELS = {
        'LS': 'Ext. Izq.', 'RS': 'Ext. Der.',
        'LI': 'Int. Izq.', 'LO': 'Ext. Izq.', 'RI': 'Int. Der.', 'RO': 'Ext. Der.',
        'LI1': 'I1 Izq.', 'LO1': 'E1 Izq.', 'RI1': 'I1 Der.', 'RO1': 'E1 Der.',
        'LI2': 'I2 Izq.', 'LO2': 'E2 Izq.', 'RI2': 'I2 Der.', 'RO2': 'E2 Der.',
        'LI3': 'I3 Izq.', 'LO3': 'E3 Izq.', 'RI3': 'I3 Der.', 'RO3': 'E3 Der.'
    };

    // Mapa completo para Tooltips (Descripción extendida)
    var POSITION_FULL_LABELS = {
        'LS': 'Izquierdo Externo', 'RS': 'Derecho Externo',
        'LI': 'Izquierdo Interno', 'LO': 'Izquierdo Externo', 
        'RI': 'Derecho Interno', 'RO': 'Derecho Externo',
        'LI1': 'Eje 1 - Izquierdo Interno', 'LO1': 'Eje 1 - Izquierdo Externo', 
        'RI1': 'Eje 1 - Derecho Interno', 'RO1': 'Eje 1 - Derecho Externo',
        'LI2': 'Eje 2 - Izquierdo Interno', 'LO2': 'Eje 2 - Izquierdo Externo', 
        'RI2': 'Eje 2 - Derecho Interno', 'RO2': 'Eje 2 - Derecho Externo',
        'LI3': 'Eje 3 - Izquierdo Interno', 'LO3': 'Eje 3 - Izquierdo Externo', 
        'RI3': 'Eje 3 - Derecho Interno', 'RO3': 'Eje 3 - Derecho Externo'
    };

    function init() {
        container = document.getElementById('dynamic-truck-container');
        
        Events.on('vehicle:layout:loaded', function(unidad) {
            renderChassisLayout(unidad);
        });
        
        Events.on('vehicle:layouts:loaded', function(unidades) {
            if (container) {
                container.innerHTML = '';
                unidades.forEach(function(unidad) {
                    renderChassisLayout(unidad);
                });
            }
        });

        console.log('[doc-tire-assignment-chassis] Módulo inicializado con Tooltips.');
    }

    /**
     * Renderiza el layout completo de una unidad
     */
    function renderChassisLayout(unidad) {
        if (!unidad || !unidad.layout || !unidad.layout.axles) {
            console.warn('[03-chassis] renderChassisLayout: datos inválidos', unidad);
            return;
        }

        console.log('[03-chassis] renderChassisLayout:', { vehicleCode: unidad.vehicle_code, axles: unidad.layout.axles.length });
        var layout = unidad.layout;
        var vehicleCode = unidad.vehicle_code;
        
        // 1. DETERMINAR EL ANCHO VISUAL SEGÚN LA COMPLEJIDAD DE LOS EJES
        var hasComplexAxles = false;
        var maxTiresInSingleAxle = 0;

        layout.axles.forEach(function(axle) {
            var tireQty = parseInt(axle.tire_qty) || 2;
            if (tireQty > maxTiresInSingleAxle) {
                maxTiresInSingleAxle = tireQty;
            }
            // Si algún eje tiene 4 o más llantas (dual/tandem), lo consideramos complejo
            if (tireQty >= 4) {
                hasComplexAxles = true;
            }
        });

        // Definir clase de columna Bootstrap
        // - Si tiene ejes complejos (>=4 llantas por eje físico) -> col-6 (50% ancho)
        // - Si solo tiene ejes sencillos (2 llantas) -> col-4 (33% ancho) para aprovechar mejor el espacio
        var widthClass = hasComplexAxles ? 'col-5' : 'col-4'; 
        
        // NOTA: Si quieres ser aún más agresivo con el espacio para ejes sencillos, 
        // puedes usar 'col-4'. Para tandems grandes, 'col-12' asegura que no se rompa la línea.
        // Ajuste recomendado para tu layout 2-8-2:
        // Si la unidad es "grande" (tandem), usa col-12 para que ocupe todo el centro.
        // Si es "pequeña" (sencillo), usa col-6 para que quepan dos lado a lado si fuera necesario, 
        // o col-4 si quieres dejar más aire.
        
        // Vamos a usar esta lógica robusta:
        if (maxTiresInSingleAxle >= 8) {
            widthClass = 'col-5'; // Tandem triple o muy grande, necesita todo el ancho
        } else if (hasComplexAxles) {
            widthClass = 'col-5'; // Tandem dual, ocupa la mayoría del centro
        } else {
            widthClass = 'col-4'; // Eje sencillo, compacto
        }

        var truckDiv = document.createElement('div');
        // Aplicar la clase dinámica junto con los estilos base
        truckDiv.className = 'truck-chassis-layout bg-white border rounded shadow-sm p-4 mb-4 ' + widthClass;
        truckDiv.style.borderTop = '5px solid #0d6efd';
        truckDiv.setAttribute('data-vehicle-code', vehicleCode);

        truckDiv.innerHTML = 
            '<div class="text-center border-bottom pb-3 mb-4">' +
            '<span class="badge bg-primary mb-2 px-3 py-2">' + escapeHtml(unidad.vehicle_type_name || unidad.vehicle_type || 'VEHICLE') + '</span>' +
            '<h5 class="fw-bold text-dark mb-1">' + escapeHtml(vehicleCode) + '</h5>' +
            '<div class="mt-2"><i class="fa-solid fa-truck-front fa-2x text-muted" style="opacity:0.2;"></i>' +
            '<span class="text-xs text-muted d-block mt-1 fw-bold">↑ FRENTE</span></div></div>';





        // var truckDiv = document.createElement('div');
        // truckDiv.className = 'truck-chassis-layout bg-white border rounded shadow-sm p-4 mb-4';
        // truckDiv.style.borderTop = '5px solid #0d6efd';
        // truckDiv.setAttribute('data-vehicle-code', vehicleCode);

        // truckDiv.innerHTML = 
        //     '<div class="text-center border-bottom pb-3 mb-4">' +
        //     '<span class="badge bg-primary mb-2 px-3 py-2">' + escapeHtml(unidad.vehicle_type_name || unidad.vehicle_type || 'VEHICLE') + '</span>' +
        //     '<h5 class="fw-bold text-dark mb-1">' + escapeHtml(vehicleCode) + '</h5>' +
        //     '<div class="mt-2"><i class="fa-solid fa-truck-front fa-2x text-muted" style="opacity:0.2;"></i>' +
        //     '<span class="text-xs text-muted d-block mt-1 fw-bold">↑ FRENTE</span></div></div>';

        layout.axles.forEach(function(axle, index) {
            var axleIndex = axle.line_num || (index + 1);
            var axleType = axle.axle_type_code || 'S1';
            var tireQty = parseInt(axle.tire_qty) || 2;

            var physicalAxlesCount = 1;
            if (tireQty >= 8) {
                physicalAxlesCount = Math.round(tireQty / 4);
            }

            var axleGroupContainer = document.createElement('div');
            axleGroupContainer.className = 'truck-axis-group mb-4 p-2 border rounded bg-light';
            axleGroupContainer.style.borderLeft = '4px solid #6c757d';
            
            var groupTitle = document.createElement('div');
            groupTitle.className = 'text-center mb-2';
            groupTitle.innerHTML = '<span class="badge bg-secondary text-xs">Eje ' + axleIndex + ' (' + escapeHtml(axle.axle_type_name || axleType) + ')</span>';
            axleGroupContainer.appendChild(groupTitle);

            var internalAxlesRow = document.createElement('div');
            internalAxlesRow.className = 'row d-flex justify-content-center gap-3 bg-gray-300';

            for (var p = 1; p <= physicalAxlesCount; p++) {
                var physicalAxleDiv = document.createElement('div');
                physicalAxleDiv.className = 'physical-axle d-flex flex-column align-items-center';
                
                if (physicalAxlesCount > 1) {
                    var subLabel = document.createElement('small');
                    subLabel.className = 'text-muted mb-1 text-xxs fw-bold';
                    subLabel.textContent = 'Sub-eje ' + p;
                    physicalAxleDiv.appendChild(subLabel);
                }

                var tiresContainer = document.createElement('div');
                tiresContainer.className = 'd-flex justify-content-center align-items-center gap-2 position-relative';
                
                var positions = generatePositionsForPhysicalAxle(p, physicalAxlesCount, tireQty);
                
                positions.forEach(function(posCode) {
                    var searchKey = posCode; 
                    var mountedTire = findMountedTire(layout.mounted_tires, vehicleCode, axleIndex, searchKey);
                    
                    // positionKey incluye axleIndex para evitar colisiones entre ejes
                    // (ej. Eje 1 LS y Eje 2 LS tenían el mismo positionKey)
                    var zone = createDropZone(
                        vehicleCode + '|' + axleIndex + '|' + searchKey,
                        vehicleCode,
                        axleIndex,
                        searchKey,
                        mountedTire
                    );
                    
                    tiresContainer.appendChild(zone);
                });

                physicalAxleDiv.appendChild(tiresContainer);
                internalAxlesRow.appendChild(physicalAxleDiv);
            }

            axleGroupContainer.appendChild(internalAxlesRow);
            truckDiv.appendChild(axleGroupContainer);
        });

        container.appendChild(truckDiv);
        
        // Inicializar Tooltips de Bootstrap después de insertar en el DOM
        var tooltipTriggerList = [].slice.call(truckDiv.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, { placement: 'top', trigger: 'hover' });
        });

        Events.emit('chassis:rendered');
    }

    function generatePositionsForPhysicalAxle(physicalIndex, totalPhysical, totalTireQty) {
        if (totalTireQty === 2) return ['LS', 'RS'];
        
        if (totalTireQty === 4 || (totalTireQty === 8 && totalPhysical === 2)) {
             if (totalPhysical === 2) {
                 return ['LI' + physicalIndex, 'LO' + physicalIndex, 'RI' + physicalIndex, 'RO' + physicalIndex];
             }
             return ['LI', 'LO', 'RI', 'RO'];
        }

        if (totalTireQty >= 12 || (totalTireQty === 12 && totalPhysical === 3)) {
            return ['LI' + physicalIndex, 'LO' + physicalIndex, 'RI' + physicalIndex, 'RO' + physicalIndex];
        }

        return ['LS', 'RS'];
    }

    function findMountedTire(mountedTires, vehicleCode, axleLineNum, positionCode) {
        if (!mountedTires) return null;
        return mountedTires.find(function(t) {
            return t.vehicle_code === vehicleCode && 
                   t.axle_line_num === axleLineNum && 
                   t.position_code === positionCode;
        });
    }

    /**
     * Crea la zona visual de drop CON TOOLTIP
     * MEJORA VISUAL CONDICIONAL - SIN CAMBIO FUNCIONAL
     * hasTire ahora es booleano estricto: solo true si mountedTire existe Y tire_code no es nulo/vacío.
     * Esto evita "llantas fantasma" cuando tire_code es null en el JSON del backend.
     */
    function createDropZone(positionKey, vehicleCode, axleIndex, posCode, mountedTire) {
        console.log('[03-chassis] createDropZone:', { positionKey, vehicleCode, axleIndex, posCode, hasTire: !!(mountedTire && mountedTire.tire_code) });
        var zone = document.createElement('div');
        zone.className = 'tyre-drop-zone';
        zone.setAttribute('draggable', 'true');
        zone.setAttribute('data-position', positionKey);
        zone.setAttribute('data-vehicle-code', vehicleCode);
        zone.setAttribute('data-axis', axleIndex);
        zone.setAttribute('data-pos-code', posCode);

        // Configuración del Tooltip
        var fullLabel = POSITION_FULL_LABELS[posCode] || posCode;
        zone.setAttribute('data-bs-toggle', 'tooltip');
        zone.setAttribute('data-bs-title', fullLabel);
        zone.setAttribute('title', fullLabel);

        // MEJORA VISUAL CONDICIONAL: hasTire es true SOLO si hay tire_code real
        var hasTire = !!(mountedTire && mountedTire.tire_code && mountedTire.tire_code.trim() !== '');
        var tireCode = hasTire ? mountedTire.tire_code : '';
        var tireName = hasTire ? (mountedTire.tire_name || '') : '';
        var tireSize = hasTire ? (mountedTire.tire_size || '') : '';

        zone.setAttribute('data-has-tire', hasTire ? 'true' : 'false');
        zone.setAttribute('data-tire-code', tireCode);

        var label = POSITION_LABELS[posCode] || posCode;
        updateZoneContent(zone, hasTire, tireCode, tireName, tireSize, label);

        return zone;
    }

    /**
     * Actualiza el contenido visual y refresca el tooltip
     * MEJORA VISUAL CONDICIONAL: hasTire validado estrictamente contra tireCode real
     */
    function updatePosition(vehicleCode, positionKey, tireCode, tireName, tireSize) {
        if (!container) {
            console.warn('[03-chassis] updatePosition: container no disponible');
            return;
        }
        var zone = container.querySelector('.tyre-drop-zone[data-position="' + positionKey + '"]');
        if (!zone) {
            console.warn('[03-chassis] updatePosition: zona no encontrada', { positionKey, vehicleCode });
            return;
        }

        console.log('[03-chassis] updatePosition:', { vehicleCode, positionKey, tireCode, tireName, tireSize });
        // MEJORA VISUAL CONDICIONAL: hasTire solo true si hay tireCode real
        var hasTire = !!(tireCode && tireCode.trim() !== '');
        var posCode = zone.getAttribute('data-pos-code');
        var label = POSITION_LABELS[posCode] || posCode;

        zone.setAttribute('data-has-tire', hasTire ? 'true' : 'false');
        zone.setAttribute('data-tire-code', tireCode || '');
        
        updateZoneContent(zone, hasTire, tireCode, tireName, tireSize, label);

        // Refrescar instancia del tooltip si existe
        var tooltipInstance = bootstrap.Tooltip.getInstance(zone);
        if (tooltipInstance) {
            tooltipInstance.dispose();
            new bootstrap.Tooltip(zone, { placement: 'top', trigger: 'hover' });
        }
    }

    /**
     * MEJORA VISUAL CONDICIONAL - SIN CAMBIO FUNCIONAL
     * Ahora usa clases semánticas (.zone-label, .zone-code, .zone-size) en lugar de estilos inline.
     * El CSS con selectores [data-has-tire="true"/"false"] controla colores, imágenes y overlays.
     * Los atributos data-* y eventos drag se mantienen intactos.
     */
    function updateZoneContent(zone, hasTire, tireCode, tireName, tireSize, label) {
        if (hasTire) {
            zone.innerHTML = 
                '<span class="zone-label">' + escapeHtml(label) + '</span>' +
                '<span class="zone-code text-truncate">' + escapeHtml(tireCode) + '</span>' +
                '<span class="zone-size text-truncate">' + escapeHtml(tireSize || '') + '</span>';
        } else {
            zone.innerHTML = 
                '<span class="zone-label">' + escapeHtml(label) + '</span>' +
                '<span class="zone-code">VACÍO</span>' +
                '<span class="zone-size">&nbsp;</span>';
        }
    }

    function getAllPositions() {
        var positions = [];
        if (!container) return positions;
        var zones = container.querySelectorAll('.tyre-drop-zone');
        zones.forEach(function(zone) {
            positions.push({
                positionKey: zone.getAttribute('data-position'),
                vehicleCode: zone.getAttribute('data-vehicle-code'),
                hasTire: zone.getAttribute('data-has-tire') === 'true',
                tireCode: zone.getAttribute('data-tire-code') || '',
                posCode: zone.getAttribute('data-pos-code')
            });
        });
        return positions;
    }

    function isTireMounted(tireCode) {
        return State.llantasSeleccionadas.some(function(l) {
            return l.tire_code === tireCode && (l.action_type === 'ASSIGN' || l.action_type === 'TRANSFER' || l.action_type === 'ROTATE');
        });
    }

    function escapeHtml(str) {
        if (typeof str !== 'string') return String(str || '');
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    module.initChassis = function() { init(); };
    module.updatePosition = function(v, p, c, n, s) { updatePosition(v, p, c, n, s); };
    module.getAllPositions = function() { return getAllPositions(); };
    module.isTireMounted = function(c) { return isTireMounted(c); };

})(window.DocTireAssignment);