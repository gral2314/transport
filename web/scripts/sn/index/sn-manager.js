(function () {
    'use strict';

    if (typeof jQuery === 'undefined') return;
    const $ = jQuery;

    function setSelectOptions($el, list, placeholder) {
        if (!$el || !$el.length) return;
        const current = $el.val();
        $el.empty();
        $el.append($('<option>').val('').text(placeholder || '-- Seleccionar --'));
        (list || []).forEach(function (r) {
            $el.append($('<option>').val(r.code).text(r.name));
        });
        if (current) $el.val(current);
    }

    function fillForm(data) {
        Object.keys(data || {}).forEach(function (k) {
            const $f = $('#sn-form [name="' + k + '"]');
            if ($f.length) $f.val(data[k]);
        });
    }

    function clearForm() {
        $('#sn-form')[0].reset();
        $('#sn_cardcode').prop('readonly', false).val('');
        $('#sn_active').val('Y');
    }

    function loadOptions() {
        const cfg = window.snConfig || {};
        if (!cfg.getFormOptions) return;

        $.get(cfg.getFormOptions).done(function (resp) {
            if (!resp || (resp.Success !== 'Ok' && resp.Success !== 'OK')) return;
            const d = resp.Data || {};
            setSelectOptions($('#sn_card_group'), d.group_sn, '-- Grupo --');
            setSelectOptions($('#sn_currency'), d.currencies, '-- Moneda --');
            setSelectOptions($('#sn_vendor'), d.vendors, '-- Vendedor --');
            setSelectOptions($('#sn_payment_cond'), d.payment_conditions, '-- Condición --');
            setSelectOptions($('#sn_payment_method'), d.payment_methods, '-- Método --');
            setSelectOptions($('#sn_cfdi_use'), d.cfdi_use, '-- Uso CFDI --');
            setSelectOptions($('#sn_cfdi_regimen'), d.cfdi_regimen, '-- Régimen CFDI --');
        });
    }

    $(function () {
        loadOptions();

        $('#btn-add-sn').on('click', function () {
            clearForm();
        });

        $('#sn-form').on('submit', function (e) {
            e.preventDefault();
            const cfg = window.snConfig || {};
            if (!cfg.save) return;

            const payload = {};
            $('#sn-form').find('[name]').each(function () {
                payload[$(this).attr('name')] = $(this).val();
            });

            $.post(cfg.save, payload).done(function (resp) {
                if (resp && (resp.Success === 'Ok' || resp.Success === 'OK')) {
                    $('#sn-modal').modal('hide');
                    if (window.tbl_sn) window.tbl_sn.ajax.reload(null, false);
                } else {
                    alert(resp && resp.Msg ? resp.Msg : 'Error al guardar');
                }
            }).fail(function () {
                alert('Error de red al guardar socio de negocio');
            });
        });

        const table = document.getElementById('tbl-sn');
        if (table) {
            table.addEventListener('click', function (e) {
                const btn = e.target.closest('.dt-btn-action[data-action="edit"]');
                if (!btn) return;
                e.preventDefault();
                e.stopPropagation();
                const pk = btn.getAttribute('data-pk');
                const cfg = window.snConfig || {};
                if (!pk || !cfg.get) return;

                $.get(cfg.get, { pk: pk }).done(function (resp) {
                    if (resp && (resp.Success === 'Ok' || resp.Success === 'OK') && resp.Data) {
                        clearForm();
                        fillForm(resp.Data);
                        $('#sn_cardcode').prop('readonly', true);
                        $('#sn-modal').modal('show');
                    } else {
                        alert(resp && resp.Msg ? resp.Msg : 'No se pudo cargar el registro');
                    }
                });
            }, true);
        }
    });
})();
