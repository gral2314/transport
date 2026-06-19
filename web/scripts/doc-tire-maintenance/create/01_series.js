/**
 * 01_series.js — Gestión del selector de series y folio
 *
 * Responsabilidad:
 * - Seleccionar la serie por defecto al cargar la página
 * - Escuchar cambios en #series_id
 * - Obtener siguiente folio vía AJAX (getNextDocnum / peekNextNumber)
 * - Actualizar campo #docnum y estado global
 */

(function (module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;
    var e = module.e;

    var seriesSelect = null;
    var docnumField = null;

    function init() {
        seriesSelect = document.getElementById('series_id');
        docnumField = document.getElementById('docnum');

        if (!seriesSelect) {
            console.warn('[01_series] Campo #series_id no encontrado.');
            return;
        }

        // Auto-seleccionar serie por defecto (primera opción con valor)
        var isNewRecord = window.DocTireFormConfig?.isNewRecord !== false;
        if (isNewRecord && !seriesSelect.value) {
            var firstOption = seriesSelect.querySelector('option[value]:not([value=""])');
            if (firstOption) {
                seriesSelect.value = firstOption.value;
                State.header.series_id = firstOption.value;
                fetchNextNumber(firstOption.value);
            }
        } else if (seriesSelect.value) {
            State.header.series_id = seriesSelect.value;
            if (isNewRecord) {
                fetchNextNumber(seriesSelect.value);
            }
        }

        // Evento change
        seriesSelect.addEventListener('change', function () {
            var newSeriesId = seriesSelect.value;
            State.header.series_id = newSeriesId;
            State.markDirty();

            if (newSeriesId) {
                fetchNextNumber(newSeriesId);
            }
        });

        //console.log('[01_series] Inicializado.');
    }

    /**
     * Obtiene el siguiente número de documento para la serie.
     * @param {string|number} seriesId
     */
    function fetchNextNumber(seriesId) {
        var routes = window.DocTireFormUrls || {};
        var url = routes.getNextDocnum;

        if (!url) {
            console.error('[01_series] Ruta getNextDocnum no configurada en window.DocTireFormUrls.');
            return;
        }

        var separator = url.indexOf('?') === -1 ? '?' : '&';
        var requestUrl = url + separator + 'series_id=' + encodeURIComponent(seriesId);

        fetch(requestUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
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
                    throw new Error('Respuesta no válida: ' + text.substring(0, 200));
                }
            });
        })
        .then(function (result) {
            if (result && result.Success === 'Ok' && result.Data) {
                var docnum = result.Data.docnum || result.Data.DocNum || 'Pendiente';
                if (docnumField) {
                    docnumField.value = docnum;
                }
                State.header.docnum = docnum;
                State.header.series_id = result.Data.series_id || seriesId;
            } else {
                module.toast('warning', 'No se pudo obtener el folio: ' + ((result && result.Msg) || 'Error desconocido'));
            }
        })
        .catch(function (err) {
            console.error('[01_series] Error al obtener folio:', err);
            module.toast('error', 'Error al obtener el siguiente folio.');
        });
    }

    // Registrar inicialización
    module._seriesInit = init;
})(window.DocTireMntForm);
