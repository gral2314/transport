/**
 * doc-tire-assignment-attachments.js
 * 
 * Responsabilidad: Gestión de la tabla de adjuntos.
 * Permite agregar filas con archivo, número de línea y notas.
 * Los adjuntos se incluyen en el payload al guardar el documento.
 */

(function(module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;

    /**
     * Referencias a elementos del DOM
     */
    var attachmentsBody = null;
    var addAttachmentBtn = null;
    var attachmentCounter = 0;

    /**
     * Inicializa el módulo de adjuntos
     */
    function init() {
        attachmentsBody = document.getElementById('doc-attachments-body');
        addAttachmentBtn = document.getElementById('add-attachment-row');

        if (!attachmentsBody) {
            console.warn('[doc-tire-assignment-attachments] Elemento #doc-attachments-body no encontrado.');
            return;
        }

        // Vincular botón "Agregar Adjunto"
        if (addAttachmentBtn) {
            addAttachmentBtn.addEventListener('click', function(e) {
                e.preventDefault();
                addAttachmentRow();
            });
        }

        // Escuchar evento de guardado para incluir adjuntos
        Events.on('form:saving', function(payload) {
            payload.attachments = collectAttachments();
        });

        // Si hay adjuntos previos en el estado, cargarlos
        if (State.attachments && State.attachments.length > 0) {
            State.attachments.forEach(function(att) {
                addAttachmentRow(att.linenum, att.filename, att.filepath, att.notes);
            });
        }

        //console.log('[doc-tire-assignment-attachments] Módulo de adjuntos inicializado.');
    }

    /**
     * Agrega una nueva fila de adjunto a la tabla
     * Si filename y filepath están presentes, muestra el archivo existente
     * con un botón de vista previa; de lo contrario, muestra un input file.
     */
    function addAttachmentRow(linenum, filename, filepath, notes) {
        if (!attachmentsBody) return;

        attachmentCounter++;
        var rowId = 'att-row-' + attachmentCounter;
        var rowNum = linenum || attachmentCounter;
        var isExisting = filename && filepath;

        var tr = document.createElement('tr');
        tr.id = rowId;
        tr.className = 'attachment-row';
        tr.setAttribute('data-att-index', attachmentCounter);

        // Columna: línea
        var html =
            '<td style="width: 50px;">' +
            '  <span class="badge bg-secondary att-linenum-display">' + rowNum + '</span>' +
            '</td>';

        // Columna: archivo (input file O nombre + vista previa)
        html += '<td>';
        if (isExisting) {
            // Archivo existente: mostrar nombre + botón vista previa
            html +=
                '  <div class="d-flex align-items-center gap-2">' +
                '    <span class="att-filename-text text-truncate" title="' + escapeHtml(filename) + '">' + escapeHtml(filename) + '</span>' +
                '    <button type="button" class="btn btn-outline-info btn-xs btn-preview-attachment" title="Vista previa">' +
                '      <svg class="svg-inline--fa fa-eye" viewBox="0 0 576 512" style="width:14px;"><path fill="currentColor" d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.4 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM432 256c0 79.5-64.5 144-144 144s-144-64.5-144-144s64.5-144 144-144s144 64.5 144 144zM288 192c0 35.3-28.7 64-64 64s-64-28.7-64-64s28.7-64 64-64s64 28.7 64 64z"/></svg>' +
                '    </button>' +
                '    <input type="hidden" class="att-filename-hidden" value="' + escapeHtml(filename) + '">' +
                '    <input type="hidden" class="att-filepath-hidden" value="' + escapeHtml(filepath) + '">' +
                '  </div>';
        } else {
            // Archivo nuevo: input file
            html +=
                '  <input type="file" class="form-control form-control-sm att-file-input" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" data-att-index="' + attachmentCounter + '">' +
                '  <input type="hidden" class="att-filename-hidden" value="">' +
                '  <input type="hidden" class="att-filepath-hidden" value="">';
        }
        html += '</td>';

        // Columna: número de línea
        html +=
            '<td>' +
            '  <input type="number" class="form-control form-control-sm att-linenum" value="' + rowNum + '" min="1" data-att-index="' + attachmentCounter + '" style="width: 80px;">' +
            '</td>';

        // Columna: notas
        html +=
            '<td>' +
            '  <input type="text" class="form-control form-control-sm att-notes" value="' + escapeHtml(notes || '') + '" placeholder="Notas del adjunto" data-att-index="' + attachmentCounter + '">' +
            '</td>';

        // Columna: botón eliminar
        html +=
            '<td style="width: 50px;" class="text-center">' +
            '  <button type="button" class="btn btn-outline-danger btn-xs btn-remove-attachment" data-att-index="' + attachmentCounter + '" title="Eliminar adjunto">' +
            '    <svg class="svg-inline--fa fa-trash-can" viewBox="0 0 448 512" style="width:12px;"><path fill="currentColor" d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>' +
            '  </button>' +
            '</td>';

        tr.innerHTML = html;
        attachmentsBody.appendChild(tr);

        // Vincular evento al botón de eliminar
        var removeBtn = tr.querySelector('.btn-remove-attachment');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                removeAttachmentRow(rowId);
            });
        }

        // Vincular evento al input de número de línea
        var linenumInput = tr.querySelector('.att-linenum');
        if (linenumInput) {
            linenumInput.addEventListener('change', function() {
                var display = tr.querySelector('.att-linenum-display');
                if (display) {
                    display.textContent = this.value || attachmentCounter;
                }
            });
        }

        // Vincular evento al input de archivo (solo si es input file)
        var fileInput = tr.querySelector('.att-file-input');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                var hiddenFilename = tr.querySelector('.att-filename-hidden');
                var hiddenFilepath = tr.querySelector('.att-filepath-hidden');
                if (this.files && this.files.length > 0) {
                    if (hiddenFilename) hiddenFilename.value = this.files[0].name;
                    if (hiddenFilepath) hiddenFilepath.value = 'upload/' + this.files[0].name;
                }
            });
        }

        // Vincular evento al botón de vista previa (solo si es archivo existente)
        var previewBtn = tr.querySelector('.btn-preview-attachment');
        if (previewBtn) {
            previewBtn.addEventListener('click', function() {
                var fp = tr.querySelector('.att-filepath-hidden');
                if (fp && fp.value) {
                    previewAttachment(fp.value, filename);
                }
            });
        }

        State.markDirty();
    }

    /**
     * Abre una vista previa del archivo en una nueva ventana/pestaña
     */
    function previewAttachment(filepath, filename) {
        if (!filepath) return;
        // Construir URL completa si es relativa
        var url = filepath;
        if (url.indexOf('://') === -1 && url.indexOf('/') === 0) {
            // Ruta absoluta del servidor
            url = window.location.origin + url;
        } else if (url.indexOf('://') === -1 && url.indexOf('/') !== 0) {
            // Ruta relativa
            var base = window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '/');
            url = base + url;
        }
        window.open(url, '_blank');
    }

    /**
     * Elimina una fila de adjunto
     */
    function removeAttachmentRow(rowId) {
        var row = document.getElementById(rowId);
        if (row && row.parentNode) {
            row.parentNode.removeChild(row);
            State.markDirty();
        }
    }

    /**
     * Recolecta todos los adjuntos del formulario
     */
    function collectAttachments() {
        var attachments = [];
        var rows = attachmentsBody.querySelectorAll('tr.attachment-row');

        rows.forEach(function(row) {
            var fileInput = row.querySelector('.att-file-input');
            var filenameHidden = row.querySelector('.att-filename-hidden');
            var filepathHidden = row.querySelector('.att-filepath-hidden');
            var filenameText = row.querySelector('.att-filename-text');
            var linenumInput = row.querySelector('.att-linenum');
            var notesInput = row.querySelector('.att-notes');

            var filename = '';
            var filepath = '';

            // Si hay un archivo seleccionado en input file, usarlo
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                filename = fileInput.files[0].name;
                filepath = 'uploads/' + filename;
            } else if (filenameText) {
                // Archivo existente: leer desde el span de nombre
                filename = filenameText.textContent.trim();
                filepath = filepathHidden ? filepathHidden.value : '';
            } else if (filenameHidden && filenameHidden.value) {
                // Fallback: hidden fields
                filename = filenameHidden.value;
                filepath = filepathHidden ? filepathHidden.value : '';
            }

            if (!filename) return; // Saltar filas sin archivo

            attachments.push({
                linenum: linenumInput ? parseInt(linenumInput.value, 10) || 1 : 1,
                filename: filename,
                filepath: filepath,
                notes: notesInput ? notesInput.value : ''
            });
        });

        return attachments;
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
     * Inicializa el módulo
     */
    module.initAttachments = function() {
        init();
    };

    /**
     * Recarga las filas de adjuntos desde el estado actual.
     * Útil en modo edición cuando los datos llegan vía AJAX después de initBaseModules.
     */
    module.reloadAttachments = function() {
        if (!attachmentsBody) {
            attachmentsBody = document.getElementById('doc-attachments-body');
        }
        if (!attachmentsBody) return;

        // Limpiar filas existentes (excepto el header de la tabla)
        var existingRows = attachmentsBody.querySelectorAll('tr.attachment-row');
        existingRows.forEach(function(row) { row.remove(); });

        attachmentCounter = 0;

        // Recargar desde el estado
        if (State.attachments && State.attachments.length > 0) {
            State.attachments.forEach(function(att) {
                addAttachmentRow(att.linenum, att.filename, att.filepath, att.notes);
            });
        }
    };

    /**
     * Expone funciones para uso externo
     */
    module.collectAttachments = function() {
        return collectAttachments();
    };

    module.addAttachmentRow = function(linenum, filename, filepath, notes) {
        addAttachmentRow(linenum, filename, filepath, notes);
    };

})(window.DocTireAssignment);
