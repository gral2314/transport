/**
 * 04_attachments.js — Gestión de archivos adjuntos
 *
 * Responsabilidad:
 * - Agregar filas de adjunto en #doc-attachments-body
 * - Manejar input file (archivo nuevo) vs visualización de archivo existente
 * - Permitir eliminar filas
 * - Sincronizar con State.attachments y actualizar contador
 */

(function (module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;
    var e = module.e;

    var tbody = null;
    var addBtn = null;
    var summaryCount = null;
    var attachmentFields = [];

    function init() {
        tbody = document.getElementById('doc-attachments-body');
        addBtn = document.getElementById('add-attachment-row');
        summaryCount = document.getElementById('summary-attachment-count');

        if (!tbody) {
            console.warn('[04_attachments] #doc-attachments-body no encontrado.');
            return;
        }

        var config = State.config || {};
        attachmentFields = config.attachmentFields || [];

        if (attachmentFields.length === 0) {
            attachmentFields = [
                { name: 'filename', label: 'Archivo', type: 'file' },
                { name: 'notes', label: 'Notas', type: 'text' }
            ];
        }

        // Botón "Agregar adjunto"
        if (addBtn) {
            addBtn.addEventListener('click', function (e) {
                e.preventDefault();
                addAttachmentRow();
            });
        }

        // Renderizar adjuntos existentes (edición)
        renderExistingRows();

        // Delegación: eliminar adjunto
        tbody.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-remove-attachment');
            if (!btn) return;
            e.preventDefault();
            var index = parseInt(btn.getAttribute('data-attachment-index'), 10);
            if (!isNaN(index)) {
                removeAttachmentRow(index);
            }
        });

        // Delegación: cambio de notas
        tbody.addEventListener('change', function (e) {
            var field = e.target.closest('[data-attachment-index]');
            if (!field) return;
            var index = parseInt(field.getAttribute('data-attachment-index'), 10);
            if (isNaN(index) || !State.attachments[index]) return;
            if (field.name === 'notes' || field.name === 'comments') {
                State.attachments[index].notes = field.value;
                State.markDirty();
            }
        });

        // Delegación: cambio de archivo
        tbody.addEventListener('change', function (e) {
            if (e.target.type !== 'file') return;
            var index = parseInt(e.target.getAttribute('data-attachment-index'), 10);
            if (isNaN(index) || !State.attachments[index]) return;
            var fileList = e.target.files;
            if (fileList && fileList.length > 0) {
                var file = fileList[0];
                State.attachments[index].file = file;
                State.attachments[index].filename = file.name;
                // Mostrar nombre en el span
                var filenameSpan = tbody.querySelector('.attachment-filename[data-attachment-index="' + index + '"]');
                if (filenameSpan) filenameSpan.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
                State.markDirty();
            }
        });

        //console.log('[04_attachments] Inicializado.');
    }

    function addAttachmentRow() {
        State.attachmentCounter++;
        var linenum = State.attachmentCounter;

        var row = {
            linenum: linenum,
            filename: '',
            filepath: '',
            notes: '',
            file: null,
            isNew: true
        };

        State.attachments.push(row);
        renderRow(row, State.attachments.length - 1);
        updateSummary();
        State.markDirty();
    }

    function removeAttachmentRow(index) {
        if (index < 0 || index >= State.attachments.length) return;
        State.attachments.splice(index, 1);
        renderAllRows();
        updateSummary();
        State.markDirty();
    }

    function renderExistingRows() {
        if (State.attachments.length > 0) {
            renderAllRows();
            updateSummary();
        }
    }

    function renderAllRows() {
        if (!tbody) return;
        tbody.innerHTML = '';
        State.attachments.forEach(function (row, idx) {
            row.linenum = idx + 1;
            renderRow(row, idx);
        });
    }

    function renderRow(row, index) {
        if (!tbody) return;

        var tr = document.createElement('tr');
        tr.setAttribute('data-attachment-index', index);

        attachmentFields.forEach(function (field) {
            var td = document.createElement('td');

            if (field.type === 'file') {
                // Mostrar nombre de archivo si ya existe
                if (row.file) {
                    td.innerHTML = '<span class="attachment-filename text-success" data-attachment-index="' + index + '">' +
                        '<i class="fa-solid fa-file"></i> ' + e(row.filename || row.file.name) + ' (' + formatFileSize(row.file.size) + ')</span>';
                } else if (row.filename && !row.file) {
                    td.innerHTML = '<span class="attachment-filename" data-attachment-index="' + index + '">' +
                        '<i class="fa-solid fa-file-pdf"></i> ' + e(row.filename) + '</span>';
                } else {
                    td.innerHTML = '<span class="attachment-filename text-muted" data-attachment-index="' + index + '">Sin archivo</span>';
                }
                // Siempre mostrar input file para subir/reemplazar
                td.innerHTML += '<input type="file" class="form-control form-control-sm mt-1 attachment-file-input" name="file" data-attachment-index="' + index + '" style="font-size: 0.8rem;">';
            } else {
                td.innerHTML = '<input type="text" class="form-control form-control-sm" name="' + e(field.name) + '" data-attachment-index="' + index + '" value="' + e(row.notes || '') + '" placeholder="' + e(field.label || '') + '">';
            }

            tr.appendChild(td);
        });

        // Columna de acciones
        var tdAction = document.createElement('td');
        tdAction.style.width = '40px';
        tdAction.style.textAlign = 'center';
        tdAction.innerHTML = '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-attachment" data-attachment-index="' + index + '" title="Eliminar"><i class="fa-solid fa-trash-can"></i></button>';
        tr.appendChild(tdAction);

        tbody.appendChild(tr);
    }

    function updateSummary() {
        if (summaryCount) {
            summaryCount.textContent = State.attachments.length;
        }
    }

    function formatFileSize(bytes) {
        if (!bytes) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB'];
        var i = 0;
        var size = bytes;
        while (size >= 1024 && i < units.length - 1) {
            size /= 1024;
            i++;
        }
        return size.toFixed(1) + ' ' + units[i];
    }

    module._attachmentsInit = init;
})(window.DocTireMntForm);
