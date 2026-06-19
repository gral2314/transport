(function () {
    'use strict';

    if (typeof jQuery === 'undefined') return;
    const $ = jQuery;

    function setSelectOptions($el, list, placeholder, codeField, nameField) {
        if (!$el || !$el.length) return;
        const current = $el.val();
        $el.empty();
        $el.append($('<option>').val('').text(placeholder || '-- Seleccionar --'));
        (list || []).forEach(function (r) {
            const code = r[codeField || 'code'];
            const name = r[nameField || 'name'];
            $el.append($('<option>').val(code).text(name));
        });
        if (current) $el.val(current);
    }

    function fillForm(data) {
        Object.keys(data || {}).forEach(function (k) {
            const $f = $('#item-form [name="' + k + '"]');
            if ($f.length) $f.val(data[k]);
        });
    }

    function clearForm() {
        $('#item-form')[0].reset();
        $('#item_itemcode').prop('readonly', false).val('');
        $('#item_is_inventory').val('Y');
        $('#item_is_purchase').val('Y');
        $('#item_is_sales').val('Y');
        $('#item_active').val('Y');
    }

    function loadOptions() {
        const cfg = window.itemsConfig || {};
        if (!cfg.getFormOptions) return;

        $.get(cfg.getFormOptions).done(function (resp) {
            if (!resp || (resp.Success !== 'Ok' && resp.Success !== 'OK')) return;
            const d = resp.Data || {};
            setSelectOptions($('#item_group'), d.groups, '-- Grupo --');
            setSelectOptions($('#item_tire_code'), d.tires, '-- Llanta --', 'code', 'name');
        });
    }

    $(function () {
        loadOptions();

        $('#btn-add-item').on('click', function () {
            clearForm();
        });

        $('#item-form').on('submit', function (e) {
            e.preventDefault();
            const cfg = window.itemsConfig || {};
            if (!cfg.save) return;

            const payload = {};
            $('#item-form').find('[name]').each(function () {
                payload[$(this).attr('name')] = $(this).val();
            });

            $.post(cfg.save, payload).done(function (resp) {
                if (resp && (resp.Success === 'Ok' || resp.Success === 'OK')) {
                    $('#item-modal').modal('hide');
                    if (window.tbl_items) window.tbl_items.ajax.reload(null, false);
                } else {
                    alert(resp && resp.Msg ? resp.Msg : 'Error al guardar ítem');
                }
            }).fail(function () {
                alert('Error de red al guardar ítem');
            });
        });

        const table = document.getElementById('tbl-items');
        if (table) {
            table.addEventListener('click', function (e) {
                const btn = e.target.closest('.dt-btn-action[data-action="edit"]');
                if (!btn) return;
                e.preventDefault();
                e.stopPropagation();
                const pk = btn.getAttribute('data-pk');
                const cfg = window.itemsConfig || {};
                if (!pk || !cfg.get) return;

                $.get(cfg.get, { pk: pk }).done(function (resp) {
                    if (resp && (resp.Success === 'Ok' || resp.Success === 'OK') && resp.Data) {
                        clearForm();
                        fillForm(resp.Data);
                        $('#item_itemcode').prop('readonly', true);
                        $('#item-modal').modal('show');
                    } else {
                        alert(resp && resp.Msg ? resp.Msg : 'No se pudo cargar el registro');
                    }
                });
            }, true);
        }
    });
})();
