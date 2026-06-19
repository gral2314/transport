/**
 * doc-tire-assignment-series.js
 *
 * Responsabilidad: Gestión del selector de series y actualización del folio.
 * - Escucha cambios en el campo series_id
 * - Obtiene el siguiente número vía AJAX desde SeriesController::getNextNumber
 * - Actualiza el campo docnum, el texto del folio y el estado global
 */

(function(module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;

    /**
     * Referencias a elementos del DOM
     */
    var seriesSelect = null;
    var docnumField = null;
    var docnumText = null;
    var form = null;

    /**
     * Inicializa el módulo de series
     */
    function init() {
        form = document.getElementById('doc-tire-form-shell');
        if (!form) {
            console.warn('[doc-tire-assignment-series] Formulario no encontrado.');
            return;
        }

        seriesSelect = form.querySelector('#series_id');
        docnumField = form.querySelector('#docnum');
        docnumText = document.getElementById('docnum-text');

        if (!seriesSelect) {
            console.warn('[doc-tire-assignment-series] Campo #series_id no encontrado.');
            return;
        }

        // Sincronizar series_id inicial al estado solo si tiene valor
        // En modo edición, el HTML ya viene con el valor seleccionado del servidor
        if (seriesSelect.value) {
            State.header.series_id = seriesSelect.value;
        }

        // Vincular evento change
        seriesSelect.addEventListener('change', function(e) {
            var newSeriesId = e.target.value;
            State.header.series_id = newSeriesId;
            State.markDirty();

            if (newSeriesId) {
                fetchNextNumber(newSeriesId);
            }
        });

        //console.log('[doc-tire-assignment-series] Módulo de series inicializado.');
    }

    /**
     * Obtiene el siguiente número de documento para la serie seleccionada
     * @param {string|number} seriesId
     */
    function fetchNextNumber(seriesId) {
        var routes = State.config.routes || {};
        // Usar peekNextNumber (solo lectura, no incrementa el consecutivo)
        var url = routes.peekNextNumber || routes.getNextNumber;

        if (!url) {
            console.error('[doc-tire-assignment-series] Ruta peekNextNumber no configurada.');
            return;
        }

        // Construir URL con parámetros
        var separator = url.indexOf('?') === -1 ? '?' : '&';
        var requestUrl = url + separator + 'objectName=DocTireMovement&seriesId=' + encodeURIComponent(seriesId);
        
        fetch(requestUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            var contentType = response.headers.get('content-type');
            if (contentType && contentType.indexOf('application/json') !== -1) {
                return response.json();
            }
            return response.text().then(function(text) {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Respuesta no válida: ' + text.substring(0, 200));
                }
            });
        })
        .then(function(result) {
            if (result.Success === 'Ok' && result.Data) {
                updateDocnum(result.Data.docNum);
            } else {
                console.warn('[doc-tire-assignment-series] Error al obtener siguiente número:', result.Msg);
                // Si hay error, mostrar el mensaje pero no bloquear
                if (result.Msg) {
                    showWarningMessage(result.Msg);
                }
            }
        })
        .catch(function(err) {
            console.error('[doc-tire-assignment-series] Error en fetchNextNumber:', err);
        });
    }

    /**
     * Actualiza el campo docnum y el estado global
     * @param {string} newDocnum
     */
    function updateDocnum(newDocnum) {
        // Actualizar campo oculto/readonly
        if (docnumField) {
            docnumField.value = newDocnum;
        }

        // Actualizar texto del folio en el encabezado
        if (docnumText) {
            docnumText.textContent = newDocnum;
        }

        // Actualizar estado global
        State.header.docnum = newDocnum;
    }

    /**
     * Muestra un mensaje de advertencia tipo toast
     * @param {string} message
     */
    function showWarningMessage(message) {
        if (typeof toastr !== 'undefined') {
            toastr.warning(message, 'Series', {
                closeButton: true,
                progressBar: true,
                timeOut: 5000
            });
        }
    }

    // Registrar módulo (sin auto-inicializar — module.js lo orquesta)
    module.Series = {
        init: init,
        fetchNextNumber: fetchNextNumber,
        updateDocnum: updateDocnum,
    };

})(window.DocTireAssignment);
