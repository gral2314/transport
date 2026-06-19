/**
 * doc-tire-assignment-vehicle.js
 * 
 * Responsabilidad: Gestión de la tabla de unidades seleccionadas.
 * Renderiza las unidades en la tabla del documento principal.
 * Maneja la edición inline de odómetro y comentarios.
 * Dispara la carga del layout del chasis cuando se agrega una unidad.
 */

(function(module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;

    /**
     * Tabla de unidades en el documento
     */
    var vehiclesTableBody = null;

    /**
     * Inicializa el módulo de vehículos
     */
    function init() {
        vehiclesTableBody = document.getElementById('doc-vehicles-body');
        if (!vehiclesTableBody) {
            console.warn('[doc-tire-assignment-vehicle] Elemento #doc-vehicles-body no encontrado.');
            return;
        }

        // Escuchar eventos de cambio en unidades
        Events.on('units:selected', function(unidades) {
            renderVehicleTable(unidades);
            loadVehiclesLayout(unidades);
        });

        Events.on('units:changed', function(unidades) {
            renderVehicleTable(unidades);
        });

        // Render inicial si hay unidades cargadas
        if (State.unidadesSeleccionadas.length > 0) {
            renderVehicleTable(State.unidadesSeleccionadas);
        }

        //console.log('[doc-tire-assignment-vehicle] Módulo de vehículos inicializado.');
    }

    /**
     * Renderiza la tabla de unidades seleccionadas en el documento
     */
    function renderVehicleTable(unidades) {
        if (!vehiclesTableBody) return;

        if (!unidades || unidades.length === 0) {
            vehiclesTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No hay unidades vinculadas. Haga clic en "Agregar Unidad" para seleccionar.</td></tr>';
            return;
        }

        var html = '';
        var maxUnits = 2;

        unidades.forEach(function(unidad, index) {
            var badgeColor = index === 0 ? 'primary' : 'success';
            var label = index === 0 ? 'TRACT' : 'REMO';
            var odometroValue = unidad.odometro || '';
            var commentsValue = unidad.comments || '';
            var layoutLoaded = unidad.layout !== null;

            html += '<tr data-vehicle-code="' + escapeHtml(unidad.vehicle_code) + '" data-vehicle-index="' + index + '">';
            html += '  <td>';
            html += '    <div class="d-flex align-items-center gap-2">';
            html += '      <span class="badge bg-' + badgeColor + '">' + label + '-' + (index + 1) + '</span>';
            html += '      <strong>' + escapeHtml(unidad.vehicle_code) + '</strong>';
            html += '    </div>';
            html += '    <small class="text-muted d-block">' + escapeHtml(unidad.vehicle_name || '') + '</small>';
            html += '  </td>';
            html += '  <td>';
            html += '    <input type="number" class="form-control form-control-sm vehicle-odometro" ' +
                     'value="' + odometroValue + '" ' +
                     'data-vehicle-code="' + escapeHtml(unidad.vehicle_code) + '" ' +
                     'placeholder="Odómetro" step="0.01" min="0">';
            html += '  </td>';
            html += '  <td>';
            html += '    <input type="text" class="form-control form-control-sm vehicle-comments" ' +
                     'value="' + escapeHtml(commentsValue) + '" ' +
                     'data-vehicle-code="' + escapeHtml(unidad.vehicle_code) + '" ' +
                     'placeholder="Comentarios de la unidad">';
            html += '  </td>';
            html += '  <td class="text-center" style="width: 80px;">';
            
            // Estado de layout
            if (layoutLoaded) {
                html += '    <span class="badge bg-success">Layout cargado</span>';
            } else {
                html += '    <button type="button" class="btn btn-outline-info btn-xs btn-load-layout" ' +
                         'data-vehicle-code="' + escapeHtml(unidad.vehicle_code) + '" ' +
                         'title="Cargar configuración de ejes">';
                html += '      <svg class="svg-inline--fa fa-truck" viewBox="0 0 640 512" style="width:14px;"><path fill="currentColor" d="M48 0C21.5 0 0 21.5 0 48V368c0 26.5 21.5 48 48 48H64c0 53 43 96 96 96s96-43 96-96H384c0 53 43 96 96 96s96-43 96-96h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V288 256 237.3c0-17-6.7-33.3-18.7-45.3L512 114.7c-12-12-28.3-18.7-45.3-18.7H416V48c0-26.5-21.5-48-48-48H48zM416 160h50.7L544 237.3V256H416V160zM112 416a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm368-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"></path></svg>';
                html += '    </button>';
            }
            
            html += '    <button type="button" class="btn btn-outline-danger btn-xs ms-1 btn-remove-vehicle" ' +
                     'data-vehicle-code="' + escapeHtml(unidad.vehicle_code) + '" ' +
                     'title="Desvincular unidad">';
            html += '      <svg class="svg-inline--fa fa-xmark" viewBox="0 0 384 512" style="width:10px;"><path fill="currentColor" d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"></path></svg>';
            html += '    </button>';
            html += '  </td>';
            html += '</tr>';
        });

        vehiclesTableBody.innerHTML = html;

        // Vincular eventos a los inputs
        bindVehicleInputs(vehiclesTableBody);

        // Vincular eventos a los botones
        bindVehicleButtons(vehiclesTableBody);
    }

    /**
     * Vincula eventos a los inputs de odómetro y comentarios
     */
    function bindVehicleInputs(container) {
        if (!container) return;

        // Inputs de odómetro
        var odometroInputs = container.querySelectorAll('.vehicle-odometro');
        odometroInputs.forEach(function(input) {
            input.removeEventListener('change', handleOdometroChange);
            input.removeEventListener('blur', handleOdometroChange);
            input.addEventListener('blur', handleOdometroChange);
        });

        // Inputs de comentarios
        var commentsInputs = container.querySelectorAll('.vehicle-comments');
        commentsInputs.forEach(function(input) {
            input.removeEventListener('change', handleCommentsChange);
            input.removeEventListener('blur', handleCommentsChange);
            input.addEventListener('blur', handleCommentsChange);
        });
    }

    /**
     * Maneja el cambio de odómetro
     */
    function handleOdometroChange(e) {
        var input = e.target;
        var vehicleCode = input.getAttribute('data-vehicle-code');
        var value = parseFloat(input.value) || 0;

        var unidad = State.unidadesSeleccionadas.find(function(u) {
            return u.vehicle_code === vehicleCode;
        });

        if (unidad) {
            unidad.odometro = value;
            State.markDirty();
        }
    }

    /**
     * Maneja el cambio de comentarios
     */
    function handleCommentsChange(e) {
        var input = e.target;
        var vehicleCode = input.getAttribute('data-vehicle-code');
        var value = input.value || '';

        var unidad = State.unidadesSeleccionadas.find(function(u) {
            return u.vehicle_code === vehicleCode;
        });

        if (unidad) {
            unidad.comments = value;
            State.markDirty();
        }
    }

    /**
     * Vincula eventos a los botones de acción de vehículos
     */
    function bindVehicleButtons(container) {
        if (!container) return;

        // Botón de cargar layout
        var loadLayoutButtons = container.querySelectorAll('.btn-load-layout');
        loadLayoutButtons.forEach(function(btn) {
            btn.removeEventListener('click', handleLoadLayout);
            btn.addEventListener('click', handleLoadLayout);
        });

        // Botón de eliminar vehículo
        var removeButtons = container.querySelectorAll('.btn-remove-vehicle');
        removeButtons.forEach(function(btn) {
            btn.removeEventListener('click', handleRemoveVehicle);
            btn.addEventListener('click', handleRemoveVehicle);
        });
    }

    /**
     * Carga el layout del chasis para una unidad
     */
    function handleLoadLayout(e) {
        var btn = e.currentTarget;
        var vehicleCode = btn.getAttribute('data-vehicle-code');

        if (!vehicleCode) return;

        // Cambiar estado visual del botón
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

        // Buscar unidad en el estado y disparar carga de layout
        var unidad = State.unidadesSeleccionadas.find(function(u) {
            return u.vehicle_code === vehicleCode;
        });

        if (unidad) {
            loadSingleVehicleLayout(unidad, btn);
        }
    }

    /**
     * Carga el layout de una sola unidad vía AJAX
     */
    function loadSingleVehicleLayout(unidad, btn) {
        var routes = State.config.routes || {};
        var layoutUrl = routes.vehicleLayout;

        if (!layoutUrl) {
            console.error('[doc-tire-assignment-vehicle] No hay ruta configurada para vehicleLayout.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<svg class="svg-inline--fa fa-truck" viewBox="0 0 640 512" style="width:14px;"><path fill="currentColor" d="..."></path></svg>';
            }
            return;
        }

        // Construir URL con el código del vehículo
        var url = layoutUrl + '?vehicleCode=' + encodeURIComponent(unidad.vehicle_code);

        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(result) {
                if (result.Success === 'Ok' && result.Data) {
                    // Guardar layout en el estado de la unidad
                    unidad.layout = {
                        vehicle: result.Data.vehicle || {},
                        axles: result.Data.axles || [],
                        mounted_tires: result.Data.mounted_tires || []
                    };

                    State.markDirty();

                    // Actualizar la fila para mostrar que el layout está cargado
                    if (btn) {
                        var row = btn.closest('tr');
                        if (row) {
                            var cell = btn.closest('td');
                            if (cell) {
                                cell.innerHTML = '<span class="badge bg-success">Layout cargado</span>' +
                                    '<button type="button" class="btn btn-outline-danger btn-xs ms-1 btn-remove-vehicle" ' +
                                    'data-vehicle-code="' + escapeHtml(unidad.vehicle_code) + '" ' +
                                    'title="Desvincular unidad">' +
                                    '<svg class="svg-inline--fa fa-xmark" viewBox="0 0 384 512" style="width:10px;"><path fill="currentColor" d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5-12.5-12.5-32.8 0-45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"></path></svg>' +
                                    '</button>';

                                // Volver a vincular el botón de eliminar
                                var newRemoveBtn = cell.querySelector('.btn-remove-vehicle');
                                if (newRemoveBtn) {
                                    newRemoveBtn.addEventListener('click', handleRemoveVehicle);
                                }
                            }
                        }
                    }

                    // Emitir evento para que el módulo de chasis dibuje el layout
                    Events.emit('vehicle:layout:loaded', unidad);

                    console.log('[doc-tire-assignment-vehicle] Layout cargado para:', unidad.vehicle_code, unidad.layout);
                } else {
                    console.error('[doc-tire-assignment-vehicle] Error al cargar layout:', result.Msg);
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<svg class="svg-inline--fa fa-truck" viewBox="0 0 640 512" style="width:14px;"><path fill="currentColor" d="M48 0C21.5 0 0 21.5 0 48V368c0 26.5 21.5 48 48 48H64c0 53 43 96 96 96s96-43 96-96H384c0 53 43 96 96 96s96-43 96-96h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V288 256 237.3c0-17-6.7-33.3-18.7-45.3L512 114.7c-12-12-28.3-18.7-45.3-18.7H416V48c0-26.5-21.5-48-48-48H48zM416 160h50.7L544 237.3V256H416V160zM112 416a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm368-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"></path></svg>';
                    }
                }
            })
            .catch(function(err) {
                console.error('[doc-tire-assignment-vehicle] Error en fetch layout:', err);
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="svg-inline--fa fa-truck" viewBox="0 0 640 512" style="width:14px;"><path fill="currentColor" d="M48 0C21.5 0 0 21.5 0 48V368c0 26.5 21.5 48 48 48H64c0 53 43 96 96 96s96-43 96-96H384c0 53 43 96 96 96s96-43 96-96h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V288 256 237.3c0-17-6.7-33.3-18.7-45.3L512 114.7c-12-12-28.3-18.7-45.3-18.7H416V48c0-26.5-21.5-48-48-48H48zM416 160h50.7L544 237.3V256H416V160zM112 416a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm368-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"></path></svg>';
                }
            });
    }

    /**
     * Carga los layouts de todas las unidades seleccionadas
     */
    function loadVehiclesLayout(unidades) {
        if (!unidades || unidades.length === 0) return Promise.resolve([]);

        var routes = State.config.routes || {};
        var layoutUrl = routes.vehicleLayout;

        if (!layoutUrl) return Promise.resolve([]);

        var promises = [];

        unidades.forEach(function(unidad) {
            // Solo cargar si no tiene layout aún
            if (unidad.layout !== null) return;

            var url = layoutUrl + '?vehicleCode=' + encodeURIComponent(unidad.vehicle_code);

            var promise = fetch(url)
                .then(function(response) { return response.json(); })
                .then(function(result) {
                    if (result.Success === 'Ok' && result.Data) {
                        unidad.layout = {
                            vehicle: result.Data.vehicle || {},
                            axles: result.Data.axles || [],
                            mounted_tires: result.Data.mounted_tires || []
                        };
                        return unidad;
                    }
                    return null;
                })
                .catch(function(err) {
                    console.error('[doc-tire-assignment-vehicle] Error cargando layout para', unidad.vehicle_code, err);
                    return null;
                });

            promises.push(promise);
        });

        if (promises.length > 0) {
            return Promise.all(promises).then(function(results) {
                var loadedUnits = results.filter(function(r) { return r !== null; });
                if (loadedUnits.length > 0) {
                    // Emitir evento con todas las unidades que tienen layout cargado
                    Events.emit('vehicle:layouts:loaded', loadedUnits);
                    // Actualizar la tabla para reflejar layouts cargados
                    renderVehicleTable(State.unidadesSeleccionadas);
                }
                return loadedUnits;
            });
        }

        return Promise.resolve([]);
    }

    /**
     * Maneja la eliminación de un vehículo
     */
    function handleRemoveVehicle(e) {
        var btn = e.currentTarget;
        var vehicleCode = btn.getAttribute('data-vehicle-code');

        if (!vehicleCode) return;

        if (!confirm('¿Está seguro de desvincular la unidad ' + vehicleCode + '? Se eliminarán todas las llantas asociadas a esta unidad.')) {
            return;
        }

        // Remover del estado
        State.unidadesSeleccionadas = State.unidadesSeleccionadas.filter(function(u) {
            return u.vehicle_code !== vehicleCode;
        });

        // Remover llantas que estaban asignadas a esta unidad
        State.llantasSeleccionadas = State.llantasSeleccionadas.filter(function(l) {
            return l.vehicle_code_to !== vehicleCode && l.vehicle_code_from !== vehicleCode;
        });

        // Limpiar cards de staging del DOM asociadas a esta unidad
        var stagingZone = document.getElementById('staging-zone');
        if (stagingZone) {
            var cards = stagingZone.querySelectorAll('.staging-tire-object');
            cards.forEach(function(card) {
                var tireCode = card.getAttribute('data-tire-code');
                if (tireCode) {
                    var detail = State.llantasSeleccionadas.find(function(l) {
                        return l.tire_code === tireCode;
                    });
                    // Si la llanta ya no está en state (se eliminó arriba), remover card
                    if (!detail) {
                        card.remove();
                    }
                }
            });
            // Mostrar mensaje vacío si ya no hay cards
            var remainingCards = stagingZone.querySelectorAll('.staging-tire-object');
            if (remainingCards.length === 0) {
                var emptyMsg = document.getElementById('staging-empty-msg');
                if (emptyMsg) emptyMsg.style.display = 'block';
            }
        }

        State.markDirty();

        // Actualizar tabla
        renderVehicleTable(State.unidadesSeleccionadas);

        // Emitir eventos
        Events.emit('units:changed', State.unidadesSeleccionadas);
        Events.emit('vehicle:removed', vehicleCode);

        console.log('[doc-tire-assignment-vehicle] Unidad desvinculada:', vehicleCode);
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
    module.initVehicle = function() {
        init();
    };

    /**
     * Expone funciones para uso externo
     */
    module.renderVehicleTable = function(unidades) {
        renderVehicleTable(unidades);
    };

    module.loadVehiclesLayout = function(unidades) {
        return loadVehiclesLayout(unidades);
    };

})(window.DocTireAssignment);