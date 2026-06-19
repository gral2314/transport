/**
 * doc-tire-assignment-form.js
 * 
 * Responsabilidad: Gestión del formulario principal del documento.
 * - Captura de datos de cabecera (fechas, prioridad, origen)
 * - Validación de datos antes del guardado
 * - Envío del payload JSON al backend
 * - Manejo de la respuesta del servidor
 * - Overlay de carga
 */

(function(module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;

    /**
     * Referencias a elementos del formulario
     */
    var form = null;
    var saveBtn = null;
    var previewBtn = null;
    var docnumText = null;

    /**
     * Indica si hay una operación de guardado en curso
     */
    var isSaving = false;

    /**
     * Indica si el formulario ya se ha inicializado completamente
     */
    var isInitialized = false;

    /**
     * Muestra el overlay de carga
     */
    function showLoadingOverlay(text) {
        var overlay = document.getElementById('doc-tire-loading-overlay');
        if (!overlay) return;
        var textEl = document.getElementById('doc-tire-loading-text');
        if (textEl) textEl.textContent = text || 'Cargando...';
        overlay.style.display = 'flex';
    }

    /**
     * Oculta el overlay de carga
     */
    function hideLoadingOverlay() {
        var overlay = document.getElementById('doc-tire-loading-overlay');
        if (!overlay) return;
        overlay.style.display = 'none';
    }

    /**
     * Inicializa Select2 en campos del formulario que lo requieran.
     * Actualmente: technician_user_id (búsqueda de técnicos/empleados).
     */
    function initSelect2Fields() {
        // technician_user_id: Select2 con búsqueda
        var techSelect = document.getElementById('technician_user_id');
        if (techSelect && typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            try {
                jQuery(techSelect).select2({
                    placeholder: 'Seleccionar técnico...',
                    allowClear: true,
                    width: '100%',
                    language: 'es'
                });
                // Sincronizar cambios de Select2 al State
                jQuery(techSelect).on('change', function(e) {
                    State.header.technician_user_id = jQuery(this).val() ? parseInt(jQuery(this).val()) : null;
                    State.markDirty();
                });
            } catch (e) {
                console.warn('[doc-tire-assignment-form] Error al inicializar Select2 en technician_user_id:', e);
            }
        }
    }

    /**
     * Inicializa el módulo del formulario
     */
    function init() {
        form = document.getElementById('doc-tire-form-shell');
        saveBtn = document.getElementById('doc-tire-save');
        previewBtn = document.getElementById('doc-tire-open-preview');
        docnumText = document.getElementById('docnum-text');

        if (!form) {
            console.warn('[doc-tire-assignment-form] Formulario #doc-tire-form-shell no encontrado.');
            return;
        }

        // Sincronizar campos del formulario con el estado
        syncFormToState();

        // Vincular eventos de cambio en campos del formulario
        bindFormEvents();

        // Inicializar Select2 en campos que lo requieran
        initSelect2Fields();

        // Vincular botón de guardado
        if (saveBtn) {
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleSave();
            });
        }

        // Vincular botón de preview
        if (previewBtn) {
            previewBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openPreview();
            });
        }

        // Escuchar cambios en el estado para habilitar/deshabilitar botones
        function onStateChanged() {
            updatePreviewButtonState();
            updateSaveButtonState();
        }

        Events.on('units:selected', onStateChanged);
        Events.on('tire:assigned', onStateChanged);
        Events.on('tire:moved', onStateChanged);
        Events.on('tire:swapped', onStateChanged);
        Events.on('tire:replaced', onStateChanged);
        Events.on('tire:removed', onStateChanged);
        Events.on('state:updated', onStateChanged);

        isInitialized = true;

        // Actualizar estado inicial de botones
        updatePreviewButtonState();
        updateSaveButtonState();

        //console.log('[doc-tire-assignment-form] Módulo de formulario inicializado.');
    }

    /**
     * Sincroniza los campos del formulario HTML con el estado global
     */
    function syncFormToState() {
        if (!form) return;

        var docentry = form.querySelector('#docentry');
        var docnum = form.querySelector('#docnum');
        var docDate = form.querySelector('#doc_date');
        var docDueDate = form.querySelector('#doc_duedate');
        var priority = form.querySelector('#priority');
        var originType = form.querySelector('#origin_type');
        var seriesSelect = form.querySelector('#series_id');
        var statusSelect = form.querySelector('#status');
        var technicianSelect = form.querySelector('#technician_user_id');
        var comments = form.querySelector('#comments');

        // Sincronizar desde el estado al formulario
        if (docentry && State.header.docentry !== null) {
            docentry.value = State.header.docentry;
        }
        if (docnum && State.header.docnum) {
            docnum.value = State.header.docnum;
            if (docnumText) {
                docnumText.textContent = State.header.docnum;
            }
        }
        if (docDate && State.header.doc_date) {
            docDate.value = State.header.doc_date;
        }
        if (docDueDate && State.header.doc_duedate) {
            docDueDate.value = State.header.doc_duedate;
        }
        if (priority && State.header.priority) {
            priority.value = State.header.priority;
        }
        if (originType && State.header.origin_type) {
            originType.value = State.header.origin_type;
        }
        if (seriesSelect && State.header.series_id) {
            seriesSelect.value = String(State.header.series_id);
        }
        if (statusSelect && State.header.status) {
            statusSelect.value = State.header.status;
        }
        if (technicianSelect && State.header.technician_user_id) {
            technicianSelect.value = String(State.header.technician_user_id);
        }

        // Si no hay fecha establecida, usar la fecha actual
        if (docDate && !docDate.value) {
            var today = new Date().toISOString().split('T')[0];
            docDate.value = today;
            State.header.doc_date = today;
        }
        if (docDueDate && !docDueDate.value) {
            var today = new Date().toISOString().split('T')[0];
            docDueDate.value = today;
            State.header.doc_duedate = today;
        }
    }

    /**
     * Vincula eventos de cambio en los campos del formulario
     */
    function bindFormEvents() {
        if (!form) return;

        // Fecha del documento
        var docDate = form.querySelector('#doc_date');
        if (docDate) {
            docDate.addEventListener('change', function(e) {
                State.header.doc_date = e.target.value;
                State.markDirty();
            });
        }

        // Fecha de ejecución
        var docDueDate = form.querySelector('#doc_duedate');
        if (docDueDate) {
            docDueDate.addEventListener('change', function(e) {
                State.header.doc_duedate = e.target.value;
                State.markDirty();
            });
        }

        // Prioridad
        var priority = form.querySelector('#priority');
        if (priority) {
            priority.addEventListener('change', function(e) {
                State.header.priority = e.target.value;
                State.markDirty();
            });
        }

        // Origen
        var originType = form.querySelector('#origin_type');
        if (originType) {
            originType.addEventListener('change', function(e) {
                State.header.origin_type = e.target.value;
                State.markDirty();
            });
        }

        // Serie
        var seriesSelect = form.querySelector('#series_id');
        if (seriesSelect) {
            seriesSelect.addEventListener('change', function(e) {
                State.header.series_id = e.target.value ? parseInt(e.target.value) : null;
                State.markDirty();
            });
        }

        // Estatus
        var statusSelect = form.querySelector('#status');
        if (statusSelect) {
            statusSelect.addEventListener('change', function(e) {
                State.header.status = e.target.value;
                State.markDirty();
            });
        }

        // Técnico asignado
        var technicianSelect = form.querySelector('#technician_user_id');
        if (technicianSelect) {
            technicianSelect.addEventListener('change', function(e) {
                State.header.technician_user_id = e.target.value ? parseInt(e.target.value) : null;
                State.markDirty();
            });
        }

        // Comentarios (si existe el campo)
        var comments = form.querySelector('#comments');
        if (comments) {
            comments.addEventListener('blur', function(e) {
                State.header.comments = e.target.value;
                State.markDirty();
            });
        }
    }

    /**
     * Maneja el guardado del documento
     */
    function handleSave() {
        if (isSaving) {
            console.warn('[doc-tire-assignment-form] Ya hay un guardado en curso.');
            return;
        }

        // Validar datos antes de guardar
        var validationResult = validateDocument();
        if (!validationResult.valid) {
            showValidationErrors(validationResult.errors);
            return;
        }

        isSaving = true;
        updateSaveButtonState(true);

        // Mostrar overlay de carga
        showLoadingOverlay('Guardando documento...');

        // Mostrar indicador visual de guardado
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Guardando...';
        }

        // Sincronizar datos del formulario al estado antes de guardar
        syncFormToState();

        // Recolectar adjuntos desde la UI antes de obtener el payload
        var attachModule = window.DocTireAssignment.Attachments;
        if (attachModule && attachModule.collectAttachments) {
            State.attachments = attachModule.collectAttachments();
        }

        // Obtener el payload completo
        var payload = State.getPayload();

        // Emitir evento para que attachments.js incluya sus datos
        Events.emit('form:saving', payload);

        var routes = State.config.routes || {};
        var saveUrl = routes.save;

        if (!saveUrl) {
            console.error('[doc-tire-assignment-form] No hay ruta de guardado configurada.');
            finishSave(false, 'Error de configuración: ruta de guardado no definida.');
            return;
        }

        // Obtener el token CSRF del meta tag
        var csrfToken = '';
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            csrfToken = meta.getAttribute('content');
        }

        // Enviar datos al backend
        fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(payload)
        })
        .then(function(response) {
            // Verificar si la respuesta es JSON
            var contentType = response.headers.get('content-type');
            if (contentType && contentType.indexOf('application/json') !== -1) {
                return response.json();
            }
            return response.text().then(function(text) {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Respuesta no válida del servidor: ' + text.substring(0, 200));
                }
            });
        })
        .then(function(result) {
            if (result.Success === 'Ok') {
                finishSave(true, result.Msg || 'Documento guardado correctamente.', result.Data);
            } else {
                finishSave(false, result.Msg || 'Error al guardar el documento.');
            }
        })
        .catch(function(err) {
            console.error('[doc-tire-assignment-form] Error en guardado:', err);
            finishSave(false, 'Error de conexión al guardar: ' + err.message);
        });
    }

    /**
     * Finaliza el proceso de guardado
     */
    function finishSave(success, message, data) {
        isSaving = false;

        // Ocultar overlay de carga
        hideLoadingOverlay();

        // Restaurar botón de guardado
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<svg class="svg-inline--fa fa-floppy-disk" viewBox="0 0 448 512" style="width:12px;"><path fill="currentColor" d="M64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-242.7c0-17-6.7-33.3-18.7-45.3L352 50.7C340 38.7 323.7 32 306.7 32L64 32zm32 96c0-17.7 14.3-32 32-32l160 0c17.7 0 32 14.3 32 32l0 64c0 17.7-14.3 32-32 32l-160 0c-17.7 0-32-14.3-32-32l0-64zM224 288a64 64 0 1 1 0 128 64 64 0 1 1 0-128z"></path></svg> Guardar';
        }

        if (success) {
            // Actualizar docnum si se generó uno nuevo
            if (data && data.docnum) {
                State.header.docnum = data.docnum;
                State.header.docentry = data.docentry || State.header.docentry;
                
                var docnumField = form ? form.querySelector('#docnum') : null;
                if (docnumField) docnumField.value = data.docnum;
                if (docnumText) docnumText.textContent = data.docnum;

                var docentryField = form ? form.querySelector('#docentry') : null;
                if (docentryField && data.docentry) docentryField.value = data.docentry;
            }

            State.isDirty = false;
            showSuccessMessage(message || 'Documento guardado correctamente.');
            updatePreviewButtonState();

            // Redirigir a la vista previa después de 1.5 segundos
            var previewBase = State.config.routes?.previewBase;
            var docentryId = State.header.docentry;
            if (previewBase && docentryId) {
                setTimeout(function() {
                    window.location.href = previewBase + '?docentry=' + docentryId;
                }, 1500);
            }
        } else {
            showErrorMessage(message || 'Error al guardar el documento.');
        }

        updateSaveButtonState();
    }

    /**
     * Valida los datos del documento antes de guardar
     */
    function validateDocument() {
        var errors = [];

        // Validar que haya al menos una unidad seleccionada
        if (!State.unidadesSeleccionadas || State.unidadesSeleccionadas.length === 0) {
            errors.push('Debe seleccionar al menos una unidad (Tracto o Remolque).');
        }

        // Validar odómetro en todas las unidades
        State.unidadesSeleccionadas.forEach(function(unidad) {
            var odometro = parseFloat(unidad.odometro) || 0;
            if (odometro <= 0) {
                errors.push('La unidad ' + unidad.vehicle_code + ' debe tener un odómetro/kilometraje válido mayor a 0.');
            }
        });

        // Validar que haya al menos un detalle de llanta
        if (!State.llantasSeleccionadas || State.llantasSeleccionadas.length === 0) {
            errors.push('Debe registrar al menos un movimiento de llanta (asignación, rotación o retiro).');
        }

        // Validar que las llantas tengan posición destino si es asignación o traslado
        State.llantasSeleccionadas.forEach(function(llanta, index) {
            if ((llanta.action_type === 'ASSIGN' || llanta.action_type === 'TRANSFER') && !llanta.position_to) {
                errors.push('La llanta ' + (llanta.tire_code || '# ' + (index + 1)) + ' no tiene una posición destino asignada en el chasis.');
            }
            if (llanta.action_type === 'REMOVE' && !llanta.position_from && !llanta.vehicle_code_from) {
                errors.push('La llanta ' + (llanta.tire_code || '# ' + (index + 1)) + ' marcada como retiro no tiene posición origen.');
            }
        });

        // Validar fechas
        if (!State.header.doc_date) {
            errors.push('Debe indicar la fecha del documento.');
        }
        if (!State.header.doc_duedate) {
            errors.push('Debe indicar la fecha de ejecución.');
        }

        return {
            valid: errors.length === 0,
            errors: errors
        };
    }

    /**
     * Muestra errores de validación en la interfaz
     */
    function showValidationErrors(errors) {
        if (!errors || errors.length === 0) return;

        // Crear mensaje de error
        var message = '<strong>Errores de validación:</strong><br><ul class="mb-0 ps-3">';
        errors.forEach(function(error) {
            message += '<li>' + escapeHtml(error) + '</li>';
        });
        message += '</ul>';

        showErrorMessage(message);

        // Hacer scroll al primer error o al panel de unidades
        var unitsTab = document.querySelector('[data-bs-target="#doc-tab-vehicles"]');
        if (unitsTab) {
            unitsTab.click();
        }

        console.warn('[doc-tire-assignment-form] Errores de validación:', errors);
    }

    /**
     * Abre la vista previa del documento en un modal vía AJAX
     */
    function openPreview() {
        var routes = State.config.routes || {};
        var quickViewBase = routes.quickViewBase;
        var previewBase = routes.previewBase;
        var docentry = State.header.docentry;

        if (!docentry) {
            alert('Debe guardar el documento primero antes de previsualizarlo.');
            return;
        }

        if (!quickViewBase) {
            console.error('[doc-tire-assignment-form] Ruta de quickView no configurada.');
            return;
        }

        var modalEl = document.getElementById('mdl-doc-preview');
        if (!modalEl) {
            console.error('[doc-tire-assignment-form] Modal #mdl-doc-preview no encontrado en el DOM.');
            return;
        }

        var modalBody = document.getElementById('mdl-doc-preview-body');
        var openPageLink = document.getElementById('mdl-doc-preview-open-page');

        // Configurar enlace "Abrir en página completa"
        if (openPageLink && previewBase) {
            openPageLink.href = previewBase + '?docentry=' + docentry;
        }

        // Mostrar spinner mientras carga
        modalBody.innerHTML = '<div class="text-center text-muted py-5">' +
            '<div class="spinner-border text-primary mb-3" role="status">' +
            '<span class="visually-hidden">Cargando...</span></div>' +
            '<p>Cargando vista previa...</p></div>';

        // Abrir el modal
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();

        // Cargar contenido vía AJAX
        var url = quickViewBase + '?docentry=' + docentry;
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            return response.text();
        })
        .then(function(html) {
            modalBody.innerHTML = html;
        })
        .catch(function(err) {
            console.error('[doc-tire-assignment-form] Error al cargar vista previa:', err);
            modalBody.innerHTML = '<div class="alert alert-danger mb-0">' +
                '<strong>Error al cargar la vista previa:</strong> ' + escapeHtml(err.message) + '</div>';
        });
    }

    /**
     * Actualiza el estado del botón de preview
     */
    function updatePreviewButtonState() {
        if (!previewBtn) return;

        var hasContent = State.llantasSeleccionadas.length > 0 && 
                        State.unidadesSeleccionadas.length > 0 &&
                        State.header.docentry !== null;

        previewBtn.disabled = !hasContent;
        previewBtn.title = hasContent ? 'Ver vista previa del documento' : 'Guarde el documento primero para habilitar la vista previa';
    }

    /**
     * Actualiza el estado del botón de guardado
     */
    function updateSaveButtonState(saving) {
        if (!saveBtn) return;

        if (saving) {
            saveBtn.disabled = true;
            return;
        }

        // Habilitar solo si hay unidades Y llantas seleccionadas
        var hasContent = State.unidadesSeleccionadas.length > 0 && 
                        State.llantasSeleccionadas.length > 0;

        saveBtn.disabled = !hasContent;
    }

    /**
     * Muestra un mensaje de éxito
     */
    function showSuccessMessage(message) {
        // Buscar o crear un contenedor de notificaciones
        var toastContainer = document.getElementById('toast-container');
        
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }

        var toastId = 'toast-' + Date.now();
        var toastHtml = '<div id="' + toastId + '" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                        '  <div class="d-flex">' +
                        '    <div class="toast-body">' +
                        '      <svg class="svg-inline--fa fa-check-circle me-1" viewBox="0 0 512 512" style="width:14px;"><path fill="currentColor" d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"></path></svg> ' + message +
                        '    </div>' +
                        '    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                        '  </div>' +
                        '</div>';

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        var toastElement = document.getElementById(toastId);
        if (toastElement) {
            var toast = new bootstrap.Toast(toastElement, { delay: 4000 });
            toast.show();

            // Eliminar del DOM después de ocultarse
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }
    }

    /**
     * Muestra un mensaje de error
     */
    function showErrorMessage(message) {
        var toastContainer = document.getElementById('toast-container');
        
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }

        var toastId = 'toast-' + Date.now();
        var toastHtml = '<div id="' + toastId + '" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                        '  <div class="d-flex">' +
                        '    <div class="toast-body">' +
                        '      <svg class="svg-inline--fa fa-exclamation-circle me-1" viewBox="0 0 512 512" style="width:14px;"><path fill="currentColor" d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24l0 160c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-160c0-13.3 10.7-24 24-24zm-32 224a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"></path></svg> ' + message +
                        '    </div>' +
                        '    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                        '  </div>' +
                        '</div>';

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        var toastElement = document.getElementById(toastId);
        if (toastElement) {
            var toast = new bootstrap.Toast(toastElement, { delay: 8000 });
            toast.show();

            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }
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
    // INICIALIZACIÓN
    // =========================================================================

    /**
     * Inicializa el módulo del formulario
     */
    module.initForm = function() {
        init();
    };

    /**
     * Expone funciones para uso externo
     */
    module.handleSave = function() {
        handleSave();
    };

    module.validateDocument = function() {
        return validateDocument();
    };

    module.showSuccessMessage = function(message) {
        showSuccessMessage(message);
    };

    module.showErrorMessage = function(message) {
        showErrorMessage(message);
    };

    module.showValidationErrors = function(errors) {
        showValidationErrors(errors);
    };

    module.showLoadingOverlay = function(text) {
        showLoadingOverlay(text);
    };

    module.hideLoadingOverlay = function() {
        hideLoadingOverlay();
    };

})(window.DocTireAssignment);