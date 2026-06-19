/**
 * DocTireDisposal Form JS
 * Dependencias: jQuery, DataTables, SweetAlert2, Toastr, Select2
 * Cargado por DynamicAssetBundle
 */
(function ($) {
    'use strict';

    // ============================================================
    // State
    // ============================================================
    var config = window.DocTireFormConfig || {};
    var headerConfig = config.config || {};
    var documentData = config.document || {};
    var formOptions = config.formOptions || {};
    var isNewRecord = config.isNewRecord || false;
    var detailRows = [];
    var selectedTires = [];
    var detailDataTable = null;
    var tireTable = null;
    var attachmentRows = [];
    var attachmentIdCounter = 0;

    // ============================================================
    // DOM refs
    // ============================================================
    var $form = $('#doc-tire-form-shell');
    var $saveBtn = $('#doc-tire-save');
    var $detailBody = $('#doc-details-body');
    var $detailCount = $('#summary-detail-count');
    var $seriesSelect = $('#series_id');
    var $docnumField = $('#docnum');
    var $attachmentBody = $('#doc-attachments-body');
    var $attachmentCount = $('#summary-attachment-count');

    // ============================================================
    // URLs
    // ============================================================
    var Urls = {
        save: headerConfig.routes ? headerConfig.routes.save : '',
        getAvailableTires: headerConfig.routes ? headerConfig.routes.getAvailableTires : '',
        getFormOptions: headerConfig.routes ? headerConfig.routes.getFormOptions : '',
        getNextDocnum: headerConfig.routes ? headerConfig.routes.getNextDocnum : '',
    };

    // ============================================================
    // Helpers
    // ============================================================
    function sanitizeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function showToast(type, msg) {
        if (typeof toastr !== 'undefined') {
            toastr[type](msg);
        }
    }

    function showLoading(show) {
        if (show) {
            $saveBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');
        } else {
            $saveBtn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk"></i> Guardar');
        }
    }

    // ============================================================
    // Series behavior
    // ============================================================
    function onSeriesChange() {
        var seriesId = $seriesSelect.val();
        if (!seriesId) {
            $docnumField.val('Se asigna al guardar');
            return;
        }

        // Mostrar placeholder mientras se obtiene el consecutivo
        $docnumField.val('Obteniendo folio...');

        var getNextDocnumUrl = Urls.getNextDocnum;
        if (!getNextDocnumUrl) {
            $docnumField.val('Se asigna al guardar');
            return;
        }

        $.ajax({
            url: getNextDocnumUrl,
            method: 'GET',
            data: { series_id: seriesId },
            dataType: 'json',
            success: function (resp) {
                if (resp && resp.Success === 'Ok' && resp.Data && resp.Data.docNum) {
                    $docnumField.val(resp.Data.docNum);
                } else {
                    // Fallback: mostrar prefijo
                    var seriesOptions = formOptions.series_options || [];
                    var found = null;
                    $.each(seriesOptions, function (idx, s) {
                        if (String(s.code) === String(seriesId)) {
                            found = s;
                            return false;
                        }
                    });
                    $docnumField.val(found && found.prefix ? found.prefix + '...' : 'Se asigna al guardar');
                }
            },
            error: function () {
                $docnumField.val('Se asigna al guardar');
            }
        });
    }

    // ============================================================
    // Tire selector modal
    // ============================================================
    function openTireSelectorModal() {
        // Obtener llantas ya agregadas para excluirlas
        var addedCodes = [];
        if (detailDataTable) {
            detailDataTable.rows().every(function () {
                var rowData = this.data();
                if (rowData && rowData.tire_code) {
                    addedCodes.push(rowData.tire_code);
                }
            });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Seleccionar llantas para baja',
                html: getTireSelectorHtml(),
                width: '1100px',
                showCancelButton: true,
                confirmButtonText: 'Agregar seleccionadas',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                didOpen: function () {
                    initTireSelectorTable(addedCodes);
                },
                preConfirm: function () {
                    return getSelectedTiresFromTable();
                }
            }).then(function (result) {
                if (result.isConfirmed && result.value && result.value.length > 0) {
                    addTiresToDetail(result.value);
                }
            });
        }
    }

    function getTireSelectorHtml() {
        return '<div class="table-responsive">' +
            '<table class="table table-sm table-bordered" id="tire-selector-table" style="width:100%">' +
            '<thead class="table-light">' +
            '<tr>' +
            '<th style="width:40px"><input type="checkbox" id="tire-select-all"></th>' +
            '<th>Código</th>' +
            '<th>Nombre</th>' +
            '<th>Condición</th>' +
            '<th>Km actuales</th>' +
            '<th>Prof. actual (mm)</th>' +
            '<th>Reencauches</th>' +
            '<th>Estatus</th>' +
            '<th>Ubicación</th>' +
            '</tr>' +
            '</thead>' +
            '<tbody></tbody>' +
            '</table>' +
            '</div>';
    }

    function initTireSelectorTable(excludeCodes) {
        if (tireTable) {
            tireTable.destroy();
            tireTable = null;
        }

        var url = Urls.getAvailableTires || headerConfig.routes.getAvailableTires;

        tireTable = $('#tire-selector-table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: url,
                method: 'GET',
                dataSrc: function (resp) {
                    if (resp.Success === 'Ok' && Array.isArray(resp.Data)) {
                        return resp.Data;
                    }
                    return [];
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    render: function (row) {
                        var checked = '';
                        if (excludeCodes.indexOf(row.code) >= 0) {
                            checked = 'disabled';
                        }
                        return '<input type="checkbox" class="tire-checkbox" value="' +
                            sanitizeHtml(row.code) + '" ' + checked + '>';
                    }
                },
                { data: 'code' },
                { data: 'name' },
                {
                    data: 'physical_condition',
                    render: function (data) {
                        var labels = { NW: 'Nueva', RT: 'Reencauchada', GD: 'Buena', LW: 'Desgaste Bajo', IW: 'Desgaste Irregular', SD: 'Dañada', PU: 'Pinchada', UN: 'Sin Inspección' };
                        return labels[data] || data || 'N/A';
                    }
                },
                {
                    data: 'current_km',
                    render: function (data) {
                        return data ? Number(data).toLocaleString() : '0';
                    }
                },
                {
                    data: 'curr_tread_depth',
                    render: function (data) {
                        return data !== null && data !== undefined ? Number(data).toFixed(1) : 'N/A';
                    }
                },
                {
                    data: 'retread_qty',
                    render: function (data) {
                        return data || '0';
                    }
                },
                {
                    data: 'operational_status',
                    render: function (data) {
                        var labels = { AV: 'Disponible', US: 'En Uso', MT: 'Mantenimiento', DS: 'Desechada' };
                        return labels[data] || data;
                    }
                },
                {
                    data: 'location_status',
                    render: function (data) {
                        var labels = { WH: 'Almacén', VH: 'En Vehículo', WS: 'Taller', SC: 'Desecho', SP: 'En Reencauche' };
                        return labels[data] || data;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[1, 'asc']],
            pageLength: 10,
            drawCallback: function () {
                // Re-attach select all handler
                $('#tire-select-all').on('change', function () {
                    var isChecked = $(this).prop('checked');
                    $('.tire-checkbox:not(:disabled)').prop('checked', isChecked);
                });
            }
        });
    }

    function getSelectedTiresFromTable() {
        var selected = [];
        $('.tire-checkbox:checked:not(:disabled)').each(function () {
            var row = tireTable ? tireTable.row($(this).closest('tr')) : null;
            if (row && row.data()) {
                selected.push(row.data());
            }
        });
        return selected;
    }

    // ============================================================
    // Detail table (DataTable)
    // ============================================================
    function addTiresToDetail(tires) {
        $.each(tires, function (idx, tire) {
            // Evitar duplicados
            var exists = false;
            if (detailDataTable) {
                detailDataTable.rows().every(function () {
                    if (this.data() && this.data().tire_code === tire.code) {
                        exists = true;
                        return false;
                    }
                });
            }
            if (exists) return;

            var rowData = {
                tire_code: tire.code,
                tire_name: tire.name,
                disposal_reason: '',
                estimated_loss: '',
                comments: '',
                physical_condition: tire.physical_condition || '',
                current_km: tire.current_km || 0,
                curr_tread_depth: tire.curr_tread_depth || 0,
                retread_qty: tire.retread_qty || 0
            };
            detailDataTable.row.add(rowData).draw(false);
        });

        updateDetailCount();
        showToast('success', tires.length + ' llanta(s) agregada(s) al detalle');
    }

    function initDetailTable() {
        if (detailDataTable) {
            detailDataTable.destroy();
            detailDataTable = null;
        }

        // Cargar datos existentes si es edición
        var existingDetails = [];
        if (documentData.details && Array.isArray(documentData.details)) {
            existingDetails = documentData.details;
        }

        detailDataTable = $('#doc-details-table').DataTable({
            data: existingDetails,
            columns: [
                {
                    data: 'tire_code',
                    title: 'Llanta',
                    render: function (data, type, row) {
                        if (type === 'display') {
                            var condLabels = { NW: 'Nueva', RT: 'Reencauchada', GD: 'Buena', LW: 'Desgaste Bajo', IW: 'Desgaste Irregular', SD: 'Dañada', PU: 'Pinchada', UN: 'Sin Inspección' };
                            var cond = condLabels[row.physical_condition] || row.physical_condition || '';
                            var km = row.current_km ? Number(row.current_km).toLocaleString() + ' km' : '';
                            var info = sanitizeHtml(row.tire_name || '');
                            if (cond) info += ' | ' + cond;
                            if (km) info += ' | ' + km;
                            return sanitizeHtml(data) + ' <small class="text-muted">' + info + '</small>';
                        }
                        return data;
                    }
                },
                {
                    data: 'disposal_reason',
                    title: 'Motivo baja',
                    render: function (data, type, row) {
                        if (type === 'display') {
                            var options = headerConfig.detailTypeOptions || {};
                            var label = options[data] || data || '';
                            return '<select class="form-select form-select-sm reason-select" data-tire-code="' +
                                sanitizeHtml(row.tire_code) + '">' +
                                '<option value="">Seleccionar...</option>' +
                                $.map(options, function (lbl, val) {
                                    var sel = String(val) === String(data) ? 'selected' : '';
                                    return '<option value="' + sanitizeHtml(val) + '" ' + sel + '>' + sanitizeHtml(lbl) + '</option>';
                                }).join('') +
                                '</select>';
                        }
                        return data;
                    }
                },
                {
                    data: 'estimated_loss',
                    title: 'Pérdida estimada',
                    render: function (data, type) {
                        if (type === 'display') {
                            return '<input type="number" step="0.01" class="form-control form-control-sm loss-input" value="' +
                                sanitizeHtml(String(data || '')) + '">';
                        }
                        return data;
                    }
                },
                {
                    data: 'comments',
                    title: 'Comentarios',
                    render: function (data, type) {
                        if (type === 'display') {
                            return '<input type="text" class="form-control form-control-sm comment-input" value="' +
                                sanitizeHtml(String(data || '')) + '">';
                        }
                        return data;
                    }
                },
                {
                    data: null,
                    title: '',
                    orderable: false,
                    render: function () {
                        return '<button type="button" class="btn btn-outline-danger btn-sm delete-detail-row" title="Eliminar">' +
                            '<i class="fa-solid fa-trash-can"></i></button>';
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[0, 'asc']],
            pageLength: 25,
            dom: 'rtip',
            createdRow: function (row, data) {
                // Store tire_code as data attribute
                $(row).attr('data-tire-code', data.tire_code || '');
            }
        });

        // Event delegation for inline edits
        $('#doc-details-table tbody').on('change', '.reason-select', function () {
            var $row = $(this).closest('tr');
            var rowData = detailDataTable.row($row).data();
            if (rowData) {
                rowData.disposal_reason = $(this).val();
            }
        });

        $('#doc-details-table tbody').on('change', '.loss-input', function () {
            var $row = $(this).closest('tr');
            var rowData = detailDataTable.row($row).data();
            if (rowData) {
                rowData.estimated_loss = $(this).val();
            }
        });

        $('#doc-details-table tbody').on('change', '.comment-input', function () {
            var $row = $(this).closest('tr');
            var rowData = detailDataTable.row($row).data();
            if (rowData) {
                rowData.comments = $(this).val();
            }
        });

        // Delete row
        $('#doc-details-table tbody').on('click', '.delete-detail-row', function () {
            var $row = $(this).closest('tr');
            var rowData = detailDataTable.row($row).data();
            if (rowData && typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Eliminar llanta?',
                    text: rowData.tire_code + ' - ' + (rowData.tire_name || ''),
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        detailDataTable.row($row).remove().draw(false);
                        updateDetailCount();
                        showToast('success', 'Llanta eliminada del detalle');
                    }
                });
            } else {
                detailDataTable.row($row).remove().draw(false);
                updateDetailCount();
            }
        });

        updateDetailCount();
    }

    function updateDetailCount() {
        var count = detailDataTable ? detailDataTable.rows().count() : 0;
        $detailCount.text(count);
    }

    function getDetailData() {
        var data = [];
        if (!detailDataTable) return data;

        detailDataTable.rows().every(function () {
            var rowData = this.data();
            if (rowData) {
                data.push({
                    tire_code: rowData.tire_code,
                    disposal_reason: rowData.disposal_reason || '',
                    estimated_loss: rowData.estimated_loss || null,
                    comments: rowData.comments || ''
                });
            }
        });
        return data;
    }

    // ============================================================
    // Tire life labels helper
    // ============================================================
    function getTireLifeInfo(row) {
        var condLabels = { NW: 'Nueva', RT: 'Reencauchada', GD: 'Buena', LW: 'Desgaste Bajo', IW: 'Desgaste Irregular', SD: 'Dañada', PU: 'Pinchada', UN: 'Sin Inspección' };
        var parts = [];
        if (row.physical_condition) parts.push('Cond: ' + (condLabels[row.physical_condition] || row.physical_condition));
        if (row.current_km) parts.push('Km: ' + Number(row.current_km).toLocaleString());
        if (row.curr_tread_depth) parts.push('Prof: ' + Number(row.curr_tread_depth).toFixed(1) + 'mm');
        if (row.retread_qty) parts.push('Reenc: ' + row.retread_qty);
        return parts.join(' | ');
    }

    // ============================================================
    // Attachments logic
    // ============================================================
    function renderAttachmentRow(attach) {
        attach._uid = attach._uid || 'attach-' + (++attachmentIdCounter);
        var linenum = attach.linenum || '';
        var filename = attach.filename || '';
        var filepath = attach.filepath || '';
        var notes = attach.notes || '';

        return '<tr data-uid="' + attach._uid + '">' +
            '<td><input type="number" step="1" class="form-control form-control-sm attach-linenum" value="' + sanitizeHtml(String(linenum)) + '" placeholder="Linea"></td>' +
            '<td><input type="text" class="form-control form-control-sm attach-filename" value="' + sanitizeHtml(filename) + '" placeholder="Nombre archivo"></td>' +
            '<td><input type="text" class="form-control form-control-sm attach-filepath" value="' + sanitizeHtml(filepath) + '" placeholder="Ruta archivo"></td>' +
            '<td><input type="text" class="form-control form-control-sm attach-notes" value="' + sanitizeHtml(notes) + '" placeholder="Notas"></td>' +
            '<td class="text-center">' +
            '<button type="button" class="btn btn-outline-danger btn-sm delete-attach-row" title="Eliminar">' +
            '<i class="fa-solid fa-trash-can"></i></button>' +
            '</td>' +
            '</tr>';
    }

    function addAttachmentRow(attach) {
        var rowData = $.extend({
            linenum: '',
            filename: '',
            filepath: '',
            notes: ''
        }, attach || {});
        rowData._uid = 'attach-' + (++attachmentIdCounter);
        attachmentRows.push(rowData);
        $attachmentBody.append(renderAttachmentRow(rowData));
        updateAttachmentCount();
    }

    function loadExistingAttachments() {
        if (documentData.attachments && Array.isArray(documentData.attachments)) {
            $.each(documentData.attachments, function (idx, attach) {
                addAttachmentRow(attach);
            });
        }
    }

    function updateAttachmentCount() {
        var count = attachmentRows.length;
        $attachmentCount.text(count);
    }

    function getAttachmentData() {
        var data = [];
        $.each(attachmentRows, function (idx, row) {
            data.push({
                linenum: row.linenum || null,
                filename: row.filename || '',
                filepath: row.filepath || '',
                notes: row.notes || ''
            });
        });
        return data;
    }

    function bindAttachmentEvents() {
        // Delegacion de eventos para inputs en la tabla de adjuntos
        $attachmentBody.on('change', '.attach-linenum', function () {
            var $row = $(this).closest('tr');
            var uid = $row.data('uid');
            var rowData = $.grep(attachmentRows, function (r) { return r._uid === uid; })[0];
            if (rowData) rowData.linenum = $(this).val();
        });

        $attachmentBody.on('change', '.attach-filename', function () {
            var $row = $(this).closest('tr');
            var uid = $row.data('uid');
            var rowData = $.grep(attachmentRows, function (r) { return r._uid === uid; })[0];
            if (rowData) rowData.filename = $(this).val();
        });

        $attachmentBody.on('change', '.attach-filepath', function () {
            var $row = $(this).closest('tr');
            var uid = $row.data('uid');
            var rowData = $.grep(attachmentRows, function (r) { return r._uid === uid; })[0];
            if (rowData) rowData.filepath = $(this).val();
        });

        $attachmentBody.on('change', '.attach-notes', function () {
            var $row = $(this).closest('tr');
            var uid = $row.data('uid');
            var rowData = $.grep(attachmentRows, function (r) { return r._uid === uid; })[0];
            if (rowData) rowData.notes = $(this).val();
        });

        // Eliminar adjunto
        $attachmentBody.on('click', '.delete-attach-row', function () {
            var $row = $(this).closest('tr');
            var uid = $row.data('uid');
            var rowData = $.grep(attachmentRows, function (r) { return r._uid === uid; })[0];
            var label = rowData ? (rowData.filename || 'adjunto') : 'adjunto';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Eliminar adjunto?',
                    text: label,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        attachmentRows = $.grep(attachmentRows, function (r) { return r._uid !== uid; });
                        $row.remove();
                        updateAttachmentCount();
                        showToast('success', 'Adjunto eliminado');
                    }
                });
            } else {
                attachmentRows = $.grep(attachmentRows, function (r) { return r._uid !== uid; });
                $row.remove();
                updateAttachmentCount();
            }
        });
    }

    // ============================================================
    // Save
    // ============================================================
    function handleSave() {
        var details = getDetailData();

        if (details.length === 0) {
            showToast('error', 'Debe agregar al menos una llanta al detalle');
            return;
        }

        // Validar que todas las llantas tengan motivo de baja
        var missingReason = false;
        $.each(details, function (idx, d) {
            if (!d.disposal_reason) {
                missingReason = true;
                return false;
            }
        });
        if (missingReason) {
            showToast('error', 'Todas las llantas deben tener un motivo de baja');
            return;
        }

        showLoading(true);

        var formData = $form.serializeArray();
        var payload = {};
        $.each(formData, function (idx, field) {
            payload[field.name] = field.value;
        });

        payload.details = JSON.stringify(details);
        payload.attachments = JSON.stringify(getAttachmentData());

        $.ajax({
            url: Urls.save,
            method: 'POST',
            dataType: 'json',
            data: payload,
            success: function (resp) {
                showLoading(false);
                if (resp.Success === 'Ok') {
                    showToast('success', 'Documento guardado correctamente');
                    // Redirigir al index después de guardar
                    if (headerConfig.routes && headerConfig.routes.index) {
                        setTimeout(function () {
                            window.location.href = headerConfig.routes.index;
                        }, 1500);
                    }
                } else {
                    var msg = resp.Msg || 'Error al guardar el documento';
                    showToast('error', msg);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', msg, 'error');
                    }
                }
            },
            error: function (jqXHR) {
                showLoading(false);
                var msg = 'Error de comunicación con el servidor';
                try {
                    var resp = JSON.parse(jqXHR.responseText);
                    if (resp.Msg) msg = resp.Msg;
                } catch (e) {}
                showToast('error', msg);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', msg, 'error');
                }
            }
        });
    }

    // ============================================================
    // Init
    // ============================================================
    function init() {
        // Init detail DataTable
        initDetailTable();

        // Series change handler
        $seriesSelect.on('change', onSeriesChange);

        // Auto-select default series on new records
        if (isNewRecord) {
            var seriesOptions = formOptions.series_options || [];
            if (seriesOptions.length > 0 && !$seriesSelect.val()) {
                // Buscar la primera serie activa como default
                var defaultSeries = seriesOptions[0];
                $seriesSelect.val(String(defaultSeries.code)).trigger('change');
            }
        }

        // Save button
        $saveBtn.on('click', function (e) {
            e.preventDefault();
            handleSave();
        });

        // Replace "Agregar" button in details tab to open modal
        $('#add-detail-row').off('click').on('click', function (e) {
            e.preventDefault();
            openTireSelectorModal();
        });

        // Attachments: add row button
        $('#add-attachment-row').off('click').on('click', function (e) {
            e.preventDefault();
            addAttachmentRow({});
        });

        // Bind attachment table events (delegated)
        bindAttachmentEvents();

        // Load existing attachments if editing
        loadExistingAttachments();

        // Keyboard shortcut: Ctrl+Enter to save
        $(document).on('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                handleSave();
            }
        });
    }

    // Init on DOM ready
    $(document).ready(function () {
        init();
    });

})(jQuery);
