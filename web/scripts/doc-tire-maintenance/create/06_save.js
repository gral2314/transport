/**
 * 06_save.js — Validación y guardado del formulario
 *
 * Responsabilidad:
 * - Validar campos obligatorios antes de guardar
 * - Recolectar datos del DOM + State para construir el payload
 * - Enviar POST al endpoint save
 * - Manejar respuesta (éxito → redirigir, error → mostrar mensaje)
 */

(function (module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;
    var e = module.e;

    var saveBtn = null;
    var previewBtn = null;

    function init() {
        saveBtn = document.getElementById('doc-tire-save');
        previewBtn = document.getElementById('doc-tire-open-preview');

        if (saveBtn) {
            saveBtn.addEventListener('click', function (e) {
                e.preventDefault();
                saveDocument();
            });
        }

        // Vista previa abre PDF en nueva pestaña
        if (previewBtn) {
            previewBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var routes = window.DocTireFormUrls || {};
                var base = routes.previewBase;
                if (!base) {
                    module.toast('error', 'URL de vista previa no configurada.');
                    return;
                }
                var docentry = State.header.docentry;
                if (!docentry) {
                    module.toast('warning', 'Guarde el documento antes de previsualizar.');
                    return;
                }
                var url = base + (base.indexOf('?') === -1 ? '?' : '&') + 'docentry=' + encodeURIComponent(docentry);
                window.open(url, '_blank', 'noopener');
            });
        }

        //console.log('[06_save] Inicializado.');
    }

    function validateForm() {
        var errors = [];

        // Validar serie
        if (!State.header.series_id) {
            errors.push('Debe seleccionar una serie.');
        }

        // Validar proveedor (si está configurado como requerido)
        var config = State.config || {};
        var headerFields = config.headerFields || [];
        headerFields.forEach(function (field) {
            if (field.required && field.name) {
                var value = State.header[field.name];
                if (value === null || value === undefined || value === '') {
                    errors.push('El campo <strong>' + e(field.label || field.name) + '</strong> es obligatorio.');
                }
            }
        });

        // Validar fecha documento
        if (!State.header.doc_date) {
            errors.push('La fecha del documento es obligatoria.');
        }

        // Validar detalles — al menos uno
        if (State.details.length === 0) {
            errors.push('Debe agregar al menos una llanta al detalle.');
        }

        // Validar campos requeridos en cada detalle
        var detailFields = config.detailFields || [];
        State.details.forEach(function (detail, idx) {
            detailFields.forEach(function (field) {
                if (field.required) {
                    var val = detail[field.name];
                    if (val === null || val === undefined || val === '') {
                        errors.push('Línea ' + (idx + 1) + ': el campo <strong>' + e(field.label || field.name) + '</strong> es obligatorio.');
                    }
                }
            });
        });

        return errors;
    }

    function saveDocument() {
        // Validar
        var errors = validateForm();
        if (errors.length > 0) {
            var msg = errors.map(function (e) { return '<div class="text-start">• ' + e + '</div>'; }).join('');
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                html: msg,
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Construir payload desde PHP-DOM + State
        var payload = buildPayload();

        // Mostrar loading en botón
        var originalHtml = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

        var routes = window.DocTireFormUrls || {};
        var url = routes.save;
        console.log('saveDocument payload:', payload);
        console.log(
  'saveDocument payload:',
  Object.fromEntries(payload.entries())
);
        return;
        if (!url) {
            module.toast('error', 'URL de guardado no configurada.');
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalHtml;
            return;
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-Token': module.csrf()
            },
            body: payload
        })
        .then(function (response) {
            var contentType = response.headers.get('content-type') || '';
            if (contentType.indexOf('application/json') !== -1) {
                return response.json();
            }
            return response.text().then(function (text) {
                try {
                    return JSON.parse(text);
                } catch (err) {
                    console.error('[06_save] Parse error:', text.substring(0, 500));
                    throw new Error('Respuesta del servidor no válida.');
                }
            });
        })
        .then(function (result) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalHtml;

            if (result && result.Success === 'Ok') {
                State.isDirty = false;

                var docentry = result.Data && result.Data.docentry;
                var docnum = result.Data && result.Data.docnum;

                // Actualizar State con datos devueltos
                if (docentry) {
                    State.header.docentry = docentry;
                    var docentryField = document.getElementById('docentry');
                    if (docentryField) docentryField.value = docentry;
                }
                if (docnum) {
                    State.header.docnum = docnum;
                    var docnumField = document.getElementById('docnum');
                    if (docnumField) docnumField.value = docnum;
                }

                module.toast('success', result.Msg || 'Documento guardado correctamente.');

                // Redirigir al preview del documento
                var routes = window.DocTireFormUrls || {};
                var previewBase = routes.previewBase;
                if (previewBase && docentry) {
                    var previewUrl = previewBase + (previewBase.indexOf('?') === -1 ? '?' : '&') + 'docentry=' + encodeURIComponent(docentry);
                    setTimeout(function () {
                        window.location.href = previewUrl;
                    }, 800);
                } else {
                    // Fallback: recargar página
                    setTimeout(function () {
                        window.location.reload();
                    }, 800);
                }
            } else {
                var errorMsg = (result && result.Msg) || 'Error desconocido al guardar.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error al guardar',
                    text: errorMsg,
                    confirmButtonColor: '#3085d6'
                });
            }
        })
        .catch(function (err) {
            console.error('[06_save] Error:', err);
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalHtml;
            module.toast('error', 'Error de comunicación al guardar.');
        });
    }

    function buildPayload() {
        // Leer datos del formulario desde el DOM (para campos que no están en JS state)
        var formEl = document.getElementById('doc-tire-form-shell');
        var domData = {};

        if (formEl) {
            var inputs = formEl.querySelectorAll('input[name], select[name], textarea[name]');
            inputs.forEach(function (input) {
                if (input.closest('#doc-details-body') || input.closest('#doc-attachments-body')) return;
                if (input.type === 'file') return;
                domData[input.name] = input.value;
            });
        }

        // Construir FormData para soportar subida de archivos
        var fd = new FormData();

        // Header fields
        fd.append('docentry', State.header.docentry || domData.docentry || '');
        fd.append('series_id', State.header.series_id || domData.series_id || '');
        fd.append('doc_date', State.header.doc_date || domData.doc_date || '');
        fd.append('doc_duedate', State.header.doc_duedate || domData.doc_duedate || '');
        fd.append('repair_date', State.header.repair_date || domData.repair_date || '');
        fd.append('return_date', State.header.return_date || domData.return_date || '');
        fd.append('provider_code', State.header.provider_code || domData.provider_code || '');
        fd.append('technician_user_id', State.header.technician_user_id || domData.technician_user_id || '');
        fd.append('comments', State.header.comments || domData.comments || '');
        if (State.header.status) {
            fd.append('status', State.header.status);
        }

        // Details as JSON string
        var detailsMeta = State.details.map(function (d) {
            return {
                tire_code: d.tire_code || '',
                tire_km: d.tire_km || 0,
                tread_depth: d.tread_depth || '',
                repair_type: d.repair_type || '',
                cost: d.cost || 0,
                comments: d.comments || '',
                deviation_notes: d.deviation_notes || ''
            };
        });
        fd.append('details', JSON.stringify(detailsMeta));

        // Attachments — solo los que tienen archivo seleccionado
        var hasFiles = [];
        State.attachments.forEach(function (a, i) {
            if (a.file instanceof File) {
                hasFiles.push({ index: i, attachment: a });
            }
        });

        var attachMeta = hasFiles.map(function (item) {
            return {
                filename: item.attachment.filename || '',
                notes: item.attachment.notes || ''
            };
        });
        fd.append('attachments', JSON.stringify(attachMeta));

        // Archivos — se aparean con attachMeta por índice
        hasFiles.forEach(function (item, metaIdx) {
            fd.append('attach_file_' + metaIdx, item.attachment.file);
        });

        return fd;
    }

    module._saveInit = init;
})(window.DocTireMntForm);
