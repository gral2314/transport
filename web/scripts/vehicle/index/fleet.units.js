/* moved from web/js/fleet.units.js to comply with DynamicAssetBundle */
(function () {
    if (typeof jQuery === 'undefined') return;
    const $ = jQuery;

    function esc(s) { return String(s || '').replace(/[&<>"]/g, function (m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]); }); }

    $(function () {
        const modalEl = document.getElementById('unit-modal');
        const unitModal = modalEl ? new bootstrap.Modal(modalEl) : null;

        function clearForm() {
            const form = document.getElementById('unit-form');
            if (form) form.reset();
            $('#vehicle_vehicle_code').val('');
            $('#docs-table tbody').empty();
            $('#tires-table tbody').empty();
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            // reset first tab
            $('#unit-tabs button:first').trigger('click');
        }

        function updateRowIndexes() {
            $('#docs-table tbody tr').each(function (i, tr) {
                $(tr).find('td:first').text(i + 1);
                $(tr).find('input,select,textarea').each(function () {
                    const name = $(this).attr('name');
                    if (name) $(this).attr('name', name.replace(/vehicle_document\[\d+\]/, 'vehicle_document[' + i + ']'));
                });
            });
            $('#tires-table tbody tr').each(function (i, tr) {
                $(tr).find('td:first').text(i + 1);
                $(tr).find('input,select,textarea').each(function () {
                    const name = $(this).attr('name');
                    if (name) $(this).attr('name', name.replace(/vehicle_tire\[\d+\]/, 'vehicle_tire[' + i + ']'));
                });
            });
        }

        function addDocRow(data) {
            const i = $('#docs-table tbody tr').length;
            const doc = data || {};
            const $tr = $('<tr>')
                .append($('<td>').text(i + 1))
                .append($('<td>').html('<input type="text" name="vehicle_document[' + i + '][doc_type_code]" class="form-control form-control-sm" value="' + esc(doc.doc_type_code) + '">'))
                .append($('<td>').html('<input type="text" name="vehicle_document[' + i + '][document_no]" class="form-control form-control-sm" value="' + esc(doc.document_no) + '">'))
                .append($('<td>').html('<input type="date" name="vehicle_document[' + i + '][issue_date]" class="form-control form-control-sm" value="' + esc(doc.issue_date) + '">'))
                .append($('<td>').html('<input type="date" name="vehicle_document[' + i + '][exp_date]" class="form-control form-control-sm" value="' + esc(doc.exp_date) + '">'))
                .append($('<td>').html('<button type="button" class="btn btn-sm btn-danger btn-remove-doc">Eliminar</button>'));
            $('#docs-table tbody').append($tr);
        }

        function addTireRow(data) {
            const i = $('#tires-table tbody tr').length;
            const t = data || {};
            const $tr = $('<tr>')
                .append($('<td>').text(i + 1))
                .append($('<td>').html('<input type="text" name="vehicle_tire[' + i + '][tire_code]" class="form-control form-control-sm" value="' + esc(t.tire_code) + '">'))
                .append($('<td>').html('<input type="text" name="vehicle_tire[' + i + '][eje_code]" class="form-control form-control-sm" value="' + esc(t.eje_code) + '">'))
                .append($('<td>').html('<input type="text" name="vehicle_tire[' + i + '][position_code]" class="form-control form-control-sm" value="' + esc(t.position_code) + '">'))
                .append($('<td>').html('<input type="date" name="vehicle_tire[' + i + '][install_date]" class="form-control form-control-sm" value="' + esc(t.install_date) + '">'))
                .append($('<td>').html('<input type="number" step="0.01" name="vehicle_tire[' + i + '][install_km]" class="form-control form-control-sm" value="' + esc(t.install_km) + '">'))
                .append($('<td>').html('<button type="button" class="btn btn-sm btn-danger btn-remove-tire">Eliminar</button>'));
            $('#tires-table tbody').append($tr);
        }

        // events
        $('#btn-add-unit').on('click', function () {
            clearForm();
            $('#unitModalLabel').text('Crear Unidad');
            if (unitModal) unitModal.show();
        });

        $('#btn-add-doc').on('click', function () { addDocRow(); });
        $('#btn-add-tire').on('click', function () { addTireRow(); });

        $('#docs-table').on('click', '.btn-remove-doc', function () { $(this).closest('tr').remove(); updateRowIndexes(); });
        $('#tires-table').on('click', '.btn-remove-tire', function () { $(this).closest('tr').remove(); updateRowIndexes(); });

        function markInvalid($el, msg) { $el.addClass('is-invalid'); $el.next('.invalid-feedback').text(msg || ''); }

        // submit -> valida y arma JSON
        $('#unit-form').on('submit', function (e) {
            e.preventDefault();
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            let valid = true;
            const $code = $('#vehicle_code');
            const $name = $('#vehicle_name');
            const $type = $('#vehicle_type_code');
            if (!$code.val().trim()) { markInvalid($code, 'Código requerido'); valid = false; }
            if (!$name.val().trim()) { markInvalid($name, 'Nombre requerido'); valid = false; }
            if (!$type.val().trim()) { markInvalid($type, 'Tipo requerido'); valid = false; }
            if (!valid) return;

            const vehicle = {};
            $('#unit-form').find('[name^="vehicle["]').each(function () {
                const nm = $(this).attr('name').replace(/^vehicle\[(.*)\]$/, '$1');
                if ($(this).attr('type') === 'checkbox') { vehicle[nm] = $(this).is(':checked') ? 'Y' : 'N'; }
                else vehicle[nm] = $(this).val();
            });

            const docs = [];
            $('#docs-table tbody tr').each(function () {
                const row = {};
                $(this).find('input,select,textarea').each(function () {
                    const m = $(this).attr('name').match(/vehicle_document\[\d+\]\[(.+)\]/);
                    if (m) row[m[1]] = $(this).val();
                });
                if (Object.keys(row).length) docs.push(row);
            });

            const tires = [];
            $('#tires-table tbody tr').each(function () {
                const row = {};
                $(this).find('input,select,textarea').each(function () {
                    const m = $(this).attr('name').match(/vehicle_tire\[\d+\]\[(.+)\]/);
                    if (m) row[m[1]] = $(this).val();
                });
                if (Object.keys(row).length) tires.push(row);
            });

            const payload = { vehicle: vehicle, vehicle_document: docs, vehicle_tire: tires };
            const cfg = window.fleetUnitsConfig || {};
            if (cfg.save) {
                $.post(cfg.save, payload).done(function (resp) {
                    if (resp && resp.Success === 'Ok') {
                        alert('Guardado OK');
                        if (unitModal) unitModal.hide();
                        // opcional: recargar lista
                        location.reload();
                    } else {
                        alert('Error: ' + (resp && resp.Msg ? resp.Msg : 'Respuesta inválida'));
                    }
                }).fail(function () { alert('Error de red al guardar'); });
            } else {
                console.log('fleet.units payload (simulado):', payload);
                alert('Validación OK. Payload listo en consola (simulado envío).');
                if (unitModal) unitModal.hide();
            }
        });

        // cargar opciones para selects vía AJAX; con fallback de ejemplo
        function loadOptionsFromArray(arr, $sel) {
            $sel.empty().append('<option value="">-- seleccionar --</option>');
            if (!Array.isArray(arr)) return;
            arr.forEach(function (it) { $sel.append($('<option>').val(it.code || it.id || it.value).text(it.name || it.text || it.label)); });
        }

        function loadOptions(url, $sel, fallback) {
            if (!url) { if (fallback) fallback($sel); return; }
            $.get(url).done(function (resp) {
                // soportar varios formatos: resp (array), resp.data, resp.Data
                const list = Array.isArray(resp) ? resp : (resp.data || resp.Data || null);
                if (Array.isArray(list)) {
                    loadOptionsFromArray(list, $sel);
                } else {
                    if (fallback) fallback($sel);
                }
            }).fail(function () { if (fallback) fallback($sel); });
        }

        const sampleTypes = [{ code: 'TRK', name: 'Camión' }, { code: 'VAN', name: 'Van' }, { code: 'CAR', name: 'Auto' }];
        const sampleBrands = [{ code: 'VOLVO', name: 'Volvo' }, { code: 'IVECO', name: 'Iveco' }];

        const cfg = window.fleetUnitsConfig || {};
        if (cfg.getFormOptions) {
            $.get(cfg.getFormOptions).done(function (resp) {
                const data = resp.Data || resp.data || resp;
                if (data && data.vehicle_type_list) loadOptionsFromArray(Object.entries(data.vehicle_type_list).map(([k,v])=>({code:k,name:v})), $('#vehicle_type_code'));
                else loadOptions(cfg.vehicleTypes, $('#vehicle_type_code'), function ($s) { loadOptionsFromArray(sampleTypes, $s); });

                if (data && data.brand_list) loadOptionsFromArray(Object.entries(data.brand_list).map(([k,v])=>({code:k,name:v})), $('#brand_code'));
                else loadOptions(cfg.brands, $('#brand_code'), function ($s) { loadOptionsFromArray(sampleBrands, $s); });
            }).fail(function () {
                // fallback
                loadOptions(cfg.vehicleTypes, $('#vehicle_type_code'), function ($s) { loadOptionsFromArray(sampleTypes, $s); });
                loadOptions(cfg.brands, $('#brand_code'), function ($s) { loadOptionsFromArray(sampleBrands, $s); });
            });
        } else {
            loadOptions(cfg.vehicleTypes, $('#vehicle_type_code'), function ($s) { loadOptionsFromArray(sampleTypes, $s); });
            loadOptions(cfg.brands, $('#brand_code'), function ($s) { loadOptionsFromArray(sampleBrands, $s); });
        }

        // función pública para poblar modal con datos (edición)
        window.fleetUnits = window.fleetUnits || {};
        window.fleetUnits.populate = function (data) {
            if (!data || !data.vehicle) return;
            const v = data.vehicle;
            $('#vehicle_vehicle_code').val(v.vehicle_code || '');
            $('#vehicle_code').val(v.vehicle_code || '');
            $('#vehicle_name').val(v.vehicle_name || '');
            $('#vehicle_type_code').val(v.vehicle_type_code || '');
            $('#brand_code').val(v.brand_code || '');
            $('#plate_no').val(v.plate_no || '');
            $('#economic_no').val(v.economic_no || '');
            $('#unit_year').val(v.unit_year || '');
            $('#acquisition').val(v.acquisition || 'P');
            $('#available').prop('checked', (v.available || 'Y') === 'Y');
            $('#status').val(v.status || 'A');
            $('#notes').val(v.notes || '');
            $('#docs-table tbody').empty();
            (data.vehicle_document || []).forEach(function (d) { addDocRow(d); });
            $('#tires-table tbody').empty();
            (data.vehicle_tire || []).forEach(function (t) { addTireRow(t); });
            if (unitModal) { $('#unitModalLabel').text('Editar Unidad ' + (v.vehicle_code || '')); unitModal.show(); }
        };
    });

})();
