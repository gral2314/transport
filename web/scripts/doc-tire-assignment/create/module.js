/**
 * module.js — Punto de entrada para DocTireAssignment (Create)
 * 
 * Inicializa todos los submódulos en el orden correcto:
 *   1. State (ya auto-inicializado)
 *   2. Vehicle (tabla de unidades)
 *   3. Modal (modales de unidades y llantas)
 *   4. Chassis (lienzo del camión)
 *   5. TireWarehouse (almacén de llantas - objetos visuales)
 *   6. DragDrop (arrastrar y soltar enterprise)
 *   7. Form (formulario y guardado)
 *   8. Attachments (gestión de adjuntos)
 * 
 * Se ejecuta automáticamente al cargar el DOM.
 * DynamicAssetBundle lo carga por estar en web/scripts/doc-tire-assignment/create/
 */

(function() {
    'use strict';

    /**
     * Escape HTML para prevenir XSS
     */
    window.escapeHtml = window.escapeHtml || function(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    };

    /**
     * Inicializa todos los submódulos de DocTireAssignment
     */
    function init() {
        var module = window.DocTireAssignment;
        if (!module) {
            console.warn('[module.js] DocTireAssignment no encontrado. Verificar carga de 00doc-tire-assignment-state.js');
            return;
        }

        console.log('[module.js] Inicializando DocTireAssignment...');

        // 0. Si es edición, cargar datos del documento mediante AJAX
        var formConfig = window.DocTireFormConfig;
        var isEditMode = formConfig && formConfig.isNewRecord === false;

        if (isEditMode) {
            console.log('[module.js] Modo edición — cargando datos vía AJAX...');
            // Inicializar módulos base primero (sin state todavía, sin Series)
            initBaseModules(module);
            // Luego cargar datos del documento vía AJAX
            loadDocumentDataAjax(module, formConfig);
        } else {
            // Modo creación: inicializar todo normalmente
            initBaseModules(module);
            // Series se inicializa después de los módulos base (no hay datos AJAX)
            initSeriesModule(module);
            initRemainingModules(module, formConfig);
        }

        // Vincular botón "Agregar Unidad" si no lo hizo el modal
        var addVehicleBtn = document.getElementById('add-vehicle-row');
        if (addVehicleBtn) {
            // Remover listeners duplicados
            var newBtn = addVehicleBtn.cloneNode(true);
            addVehicleBtn.parentNode.replaceChild(newBtn, addVehicleBtn);
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof module.openUnitModal === 'function') {
                    module.openUnitModal();
                } else {
                    // Fallback: abrir modal directamente
                    var modalEl = document.getElementById('mdl-units');
                    if (modalEl) {
                        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                }
            });
        }

        // Vincular botón "Nueva llanta" (add-detail-row) como respaldo
        var addTireBtn = document.getElementById('add-detail-row');
        if (addTireBtn) {
            var newTireBtn = addTireBtn.cloneNode(true);
            addTireBtn.parentNode.replaceChild(newTireBtn, addTireBtn);
            newTireBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('[module.js] Botón +Nueva presionado, abriendo modal de llantas...');
                if (typeof module.openTireModal === 'function') {
                    module.openTireModal();
                } else {
                    var modalEl = document.getElementById('mdl-tires');
                    if (modalEl) {
                        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.show();
                    } else {
                        console.error('[module.js] Elemento #mdl-tires no encontrado en el DOM.');
                    }
                }
            });
        } else {
            console.warn('[module.js] Botón #add-detail-row no encontrado en el DOM.');
        }

        //console.log('[module.js] DocTireAssignment inicializado correctamente.');
    }

    // ──────────────────────────────────────────────
    // Funciones auxiliares para carga en modo edición
    // ──────────────────────────────────────────────

    /**
     * Inicializa los módulos base que no dependen del state
     */
    function initBaseModules(module) {
        // 1. Vehicle — tabla de unidades en el documento
        if (typeof module.initVehicle === 'function') {
            module.initVehicle();
        }

        // 2. Modal — modales de selección
        if (typeof module.initModals === 'function') {
            module.initModals();
        }

        // 3. Chassis — lienzo del camión
        if (typeof module.initChassis === 'function') {
            module.initChassis();
        }

        // 4. TireWarehouse — almacén de llantas
        if (typeof module.initTireWarehouse === 'function') {
            module.initTireWarehouse();
        }

        // 5. DragDrop — arrastrar y soltar
        if (typeof module.initDragDrop === 'function') {
            module.initDragDrop();
        }

        // 6. Audit — tablas de auditoría en tiempo real
        if (typeof module.initAudit === 'function') {
            module.initAudit();
        }

        // 7. Form — formulario y guardado (parcial, sin state todavía)
        if (typeof module.initForm === 'function') {
            module.initForm();
        }

        // 8. Summary — lista de movimientos en el panel lateral
        if (typeof module.initSummary === 'function') {
            module.initSummary();
        }

        // 9. Attachments — gestión de adjuntos
        if (typeof module.initAttachments === 'function') {
            module.initAttachments();
        }
    }

    /**
     * Inicializa Series DESPUÉS de que los datos del documento estén cargados.
     * En modo creación se llama después de initBaseModules.
     * En modo edición se llama después de syncHeaderFields en loadDocumentDataAjax.
     */
    function initSeriesModule(module) {
        if (typeof module.Series !== 'undefined' && typeof module.Series.init === 'function') {
            module.Series.init();
        }
    }

    /**
     * Inicializa los módulos restantes después de cargar datos (modo creación)
     * En modo edición, espera a que los layouts de vehículos se carguen antes
     * de emitir tires:selected, para que las llantas se coloquen en sus posiciones.
     */
    function initRemainingModules(module, formConfig) {
        var isEdit = formConfig && formConfig.isNewRecord === false;

        // Función para emitir tires:selected y luego colocar llantas en posiciones
        function placeTiresInPositions() {
            if (!isEdit || module.State.llantasSeleccionadas.length === 0) return;

            // 1. Emitir evento para que el warehouse las muestre
            module.Events.emit('tires:selected', module.State.llantasSeleccionadas);

            // 2. Colocar cada llanta en su posición del chasis si tiene position_to
            var updatePos = module.updatePosition;
            if (typeof updatePos === 'function') {
                module.State.llantasSeleccionadas.forEach(function(llanta) {
                    if (llanta.position_to && llanta.vehicle_code_to) {
                        updatePos(
                            llanta.vehicle_code_to,
                            llanta.position_to,
                            llanta.tire_code,
                            llanta.tire_name || llanta.tire_code,
                            llanta.tire_size || ''
                        );
                    }
                });
            }
        }

        if (isEdit && module.State.unidadesSeleccionadas.length > 0) {
            if (typeof module.loadVehiclesLayout === 'function') {
                // Esperar a que los layouts se carguen antes de colocar llantas
                var layoutPromise = module.loadVehiclesLayout(module.State.unidadesSeleccionadas);
                if (layoutPromise && typeof layoutPromise.then === 'function') {
                    layoutPromise.then(function() {
                        placeTiresInPositions();
                    }).catch(function() {
                        placeTiresInPositions(); // Aunque falle, intentamos colocar llantas
                    });
                } else {
                    placeTiresInPositions();
                }
            } else {
                placeTiresInPositions();
            }
        } else {
            placeTiresInPositions();
        }
    }

    /**
     * Carga los datos del documento vía AJAX en modo edición
     */
    function loadDocumentDataAjax(module, formConfig) {
        var docentry = formConfig.docentry || (formConfig.document && formConfig.document.docentry);

        if (!docentry) {
            console.error('[module.js] No se encontró docentry para modo edición.');
            return;
        }

        // Obtener URL del endpoint get desde las URLs configuradas
        var getUrl = (window.DocTireUrls && window.DocTireUrls.get)
            || (window.DocTireFormConfig && window.DocTireFormConfig.urls && window.DocTireFormConfig.urls.get)
            || '/trasnportone/web/doc-tire-assignment/get?docentry=' + docentry;

        // Si la URL no incluye el docentry, agregarlo
        if (getUrl.indexOf('docentry') === -1) {
            getUrl += (getUrl.indexOf('?') === -1 ? '?' : '&') + 'docentry=' + docentry;
        }

        console.log('[module.js] Cargando documento #' + docentry + ' desde:', getUrl);

        // Mostrar indicador de carga
        showLoadingOverlay(true);

        fetch(getUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Error HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(function(result) {
            showLoadingOverlay(false);

            if (result.Success === 'Ok' && result.Data) {
                console.log('[module.js] Datos del documento recibidos correctamente.');

                // Mapear datos al formato esperado por el State
                var documentData = mapServiceDataToState(result.Data);

                // Cargar en el State
                module.State.loadFromDocument(documentData);

                // Sincronizar campos del header HTML con el State
                syncHeaderFields(module);

                // Inicializar Series DESPUÉS de syncHeaderFields para que el select
                // ya tenga el valor correcto y el change event dispare fetchNextNumber
                initSeriesModule(module);

                // Recargar attachments desde el estado (initBaseModules los cargó vacíos)
                if (typeof module.reloadAttachments === 'function') {
                    module.reloadAttachments();
                }

                // Inicializar módulos restantes (layouts, llantas visuales)
                initRemainingModules(module, formConfig);
                $('#series_id').attr('disabled', true); // Deshabilitar cambio de serie en edición

                console.log('[module.js] Documento #' + docentry + ' cargado completamente.');
            } else {
                console.error('[module.js] Error al cargar documento:', result.Msg || 'Respuesta inválida');
                showToast('error', 'Error al cargar documento: ' + (result.Msg || 'Respuesta inválida'));
            }
        })
        .catch(function(err) {
            showLoadingOverlay(false);
            console.error('[module.js] Error en fetch get():', err);
            showToast('error', 'Error de conexión al cargar documento');
        });
    }

    /**
     * Mapea los datos del service al formato esperado por State.loadFromDocument
     */
    function mapServiceDataToState(serviceData) {
        if (!serviceData) return {};

        // Mapeo de header
        var mapped = {
            docentry: serviceData.docentry,
            docnum: serviceData.docnum,
            series_id: serviceData.series_id,
            status: serviceData.status,
            technician_user_id: serviceData.technician_user_id,
            doc_date: serviceData.doc_date,
            doc_duedate: serviceData.doc_duedate,
            priority: serviceData.priority,
            origin_type: serviceData.origin_type,
            comments: serviceData.comments,
            doc_type: serviceData.doc_type,
            doc_status: serviceData.doc_status,
            canceled: serviceData.canceled,
            createuser: serviceData.createuser,
            createdate: serviceData.createdate,
            // State.loadFromDocument() espera "vehicles" y "details"
            vehicles: [],
            details: [],
            attachments: []
        };

        // Mapear vehículos
        if (serviceData.vehicles && Array.isArray(serviceData.vehicles)) {
            mapped.vehicles = serviceData.vehicles.map(function(v) {
                return {
                    vehicle_code: v.vehicle_code,
                    vehicle_name: v.vehicle_name || v.vehicle_code,
                    comments: v.comments || '',
                    odometro: v.vehicle_km || v.odometer || 0,
                    layout: v.layout || null,
                    // Datos adicionales que puedan necesitarse
                    brand: v.brand || '',
                    model: v.model || '',
                    plate: v.plate || '',
                    doc_entry: v.doc_entry || serviceData.docentry,
                    line_num: v.linenum || v.line_num || 0
                };
            });
        }

        // Mapear detalles (llantas)
        if (serviceData.details && Array.isArray(serviceData.details)) {
            mapped.details = serviceData.details.map(function(d) {
                return {
                    doc_entry: d.docentry || serviceData.docentry,
                    line_num: d.linenum || 0,
                    movement_type: d.movement_type,
                    action_type: d.movement_type, // Mapeo: movement_type → action_type
                    tire_code: d.tire_code,
                    tire_name: d.tire_name || d.tire_code,
                    related_tire_code: d.related_tire_code || null,
                    related_tire_name: d.related_tire_name || '',
                    tire_size: d.tire_size || '',
                    tire_brand: d.tire_brand || '',
                    // Posiciones (vienen del service como position_from / position_to)
                    position_from: d.position_from || '',
                    position_to: d.position_to || '',
                    position_code: d.position_from || '', // alias para compatibilidad
                    // Vehículos (vienen del service como vehicle_code_from / vehicle_code_to)
                    vehicle_code_from: d.vehicle_code_from || '',
                    vehicle_from_name: d.vehicle_from_name || '',
                    vehicle_code_to: d.vehicle_code_to || '',
                    vehicle_to_name: d.vehicle_to_name || '',
                    vehicle_from: d.vehicle_code_from || '', // alias para compatibilidad
                    vehicle_to: d.vehicle_code_to || '',     // alias para compatibilidad
                    // Almacenes (vienen del service como whs_code_from / whs_code_to)
                    warehouse_code_from: d.whs_code_from || d.warehouse_code_from || '',
                    warehouse_code_to: d.whs_code_to || d.warehouse_code_to || '',
                    warehouse_code: d.whs_code_from || d.warehouse_code_from || '', // alias
                    // Condiciones
                    tire_condition: d.tire_condition || '',
                    physical_condition: d.physical_condition || '',
                    tire_km: d.tire_km || 0,
                    tread_depth: d.tread_depth || 0,
                    line_status: d.line_status || 'PENDING',
                    execution_date: d.execution_date || '',
                    execution_time: d.execution_time || '',
                    comments: d.comments || '',
                    quantity: d.quantity || 1,
                    // Para llantas de montaje inicial
                    axle_code: d.position_from || '',
                    side: d.side || '',
                    line_num_vehicle: d.line_num_vehicle || 0
                };
            });
        }

        // Mapear adjuntos
        if (serviceData.attachments && Array.isArray(serviceData.attachments)) {
            mapped.attachments = serviceData.attachments.map(function(a) {
                return {
                    id: a.id || a.attachment_id || 0,
                    linenum: a.linenum || 0,
                    doc_entry: a.docentry || serviceData.docentry,
                    filename: a.filename || a.file_name || '',
                    filepath: a.filepath || a.file_path || '',
                    notes: a.notes || a.comments || '',
                    mime_type: a.mime_type || '',
                    file_size: a.file_size || 0,
                    createdate: a.createdate || ''
                };
            });
        }

        return mapped;
    }

    /**
     * Sincroniza los campos del header HTML con el State después de carga AJAX
     */
    function syncHeaderFields(module) {
        var header = module.State.header;
        if (!header) return;

        // Sincronizar campos del formulario
        var fieldMap = {
            'docentry': header.docentry,
            'docnum': header.docnum,
            'doc_date': header.doc_date,
            'doc_duedate': header.doc_duedate,
            'priority': header.priority,
            'origin_type': header.origin_type,
            'comments': header.comments,
            'series_id': header.series_id,
            'status': header.status,
            'technician_user_id': header.technician_user_id
        };

        Object.keys(fieldMap).forEach(function(id) {
            var el = document.getElementById(id);
            if (el && fieldMap[id] !== null && fieldMap[id] !== undefined) {
                el.value = fieldMap[id];

                // Si es un select, disparar evento change nativo
                // EXCEPTO para series_id en modo edición: no disparar change
                // porque el change dispara fetchNextNumber() que sobreescribe
                // el docnum con el siguiente número disponible
                if (el.tagName === 'SELECT' && id !== 'series_id') {
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });

        // Si technician_user_id tiene Select2, actualizar su valor
        if (header.technician_user_id && typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            var techSelect = document.getElementById('technician_user_id');
            if (techSelect) {
                jQuery(techSelect).val(String(header.technician_user_id)).trigger('change');
            }
        }

        // Actualizar docnum text si existe
        var docnumText = document.getElementById('docnum-text');
        if (docnumText && header.docnum) {
            docnumText.textContent = header.docnum;
        }

        // Actualizar docentry hidden si existe
        var docentryHidden = document.getElementById('docentry');
        if (docentryHidden && header.docentry) {
            docentryHidden.value = header.docentry;
        }

        console.log('[module.js] Header fields sincronizados.');
    }

    /**
     * Muestra/oculta overlay de carga
     */
    function showLoadingOverlay(show) {
        var overlay = document.getElementById('doc-tire-loading-overlay');
        if (!overlay) {
            // Fallback: crear uno dinámico si no existe en el DOM
            if (show) {
                overlay = document.createElement('div');
                overlay.id = 'doc-tire-loading-overlay';
                overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.55);z-index:9999;display:flex;align-items:center;justify-content:center;flex-direction:column;';
                overlay.innerHTML = '<div class="bg-white rounded-3 shadow-lg p-4 text-center" style="min-width:200px;"><div class="spinner-border text-primary mb-2" role="status" style="width:3rem;height:3rem;"><span class="visually-hidden">Cargando...</span></div><div class="fw-semibold text-dark">Cargando...</div></div>';
                document.body.appendChild(overlay);
            }
        }
        if (overlay) {
            overlay.style.display = show ? 'flex' : 'none';
        }
    }

    /**
     * Muestra un toast de notificación
     */
    function showToast(type, message) {
        if (typeof toastr !== 'undefined') {
            var fn = toastr.error;
            if (type === 'success') fn = toastr.success;
            else if (type === 'warning') fn = toastr.warning;
            else if (type === 'info') fn = toastr.info;
            fn.call(toastr, message, '', { timeOut: 5000, closeButton: true });
        } else {
            alert(message);
        }
    }

    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
