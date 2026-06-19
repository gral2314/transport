/**
 * FullCalendar para DocTireMaintenance — list/index
 * Dependencias: FullCalendar 6, jQuery, Bootstrap, SweetAlert2
 * Cargado por index.php via registerJsFile con depends=[FullCalendarAsset]
 */
(function () {
    if (window.DocTireCalendarInit) return;
    window.DocTireCalendarInit = true;

    var calendar = null;
    var calendarContainer = document.getElementById('doc-tire-calendar-container');
    var tableContainer = document.getElementById('doc-tire-table-container');
    var toggleBtn = document.getElementById('doc-tire-toggle-calendar');
    var providerFilter = document.getElementById('doc-tire-calendar-provider-filter');
    var todayBtn = document.getElementById('doc-tire-calendar-today');
    var tooltipEl = document.getElementById('doc-tire-calendar-tooltip');

    // Variable para detectar doble click
    var clickTimer = null;

    // ── Poblar select de proveedores ────────────────────────────────────
    function populateProviderFilter() {
        if (!providerFilter) return;
        var optionsJson = window.FullCalendarUrls && window.FullCalendarUrls.mechanicOptions;
        if (!optionsJson) return;

        var options;
        try {
            options = typeof optionsJson === 'string' ? JSON.parse(optionsJson) : optionsJson;
        } catch (e) {
            return;
        }

        if (!options || typeof options !== 'object') return;

        providerFilter.innerHTML = '<option value="">Todos los proveedores</option>';
        Object.keys(options).forEach(function (code) {
            var label = options[code] || code;
            var o = document.createElement('option');
            o.value = code;
            o.textContent = label;
            providerFilter.appendChild(o);
        });
    }

    // ── Cargar eventos desde el endpoint ────────────────────────────────
    function loadEvents(start, end, callback) {
        var params = {};
        if (providerFilter && providerFilter.value) {
            params.provider_code = providerFilter.value;
        }

        $.ajax({
            url: window.FullCalendarUrls.calendarEvents,
            type: 'GET',
            data: params,
            dataType: 'json'
        }).done(function (response) {
            if (response.Success === 'Ok') {
                callback(Array.isArray(response.Data) ? response.Data : []);
            } else {
                console.error('Error al cargar eventos:', response.Msg);
                callback([]);
            }
        }).fail(function (jqXHR, textStatus) {
            console.error('Error de conexion al cargar eventos:', textStatus);
            callback([]);
        });
    }

    // ── Mostrar notificacion toast con SweetAlert2 ──────────────────────
    function showToast(icon, title) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: icon,
                title: title,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    }

    // ── Confirmar cambio de fecha con SweetAlert2 ───────────────────────
    function confirmDateChange(info, isResize) {
        var docentry = parseInt(info.event.id, 10);
        if (!docentry) {
            info.revert();
            return;
        }

        var newStart = info.event.startStr;
        var newEnd = info.event.end ? info.event.endStr : newStart;
        var props = info.event.extendedProps || {};
        var docnum = props.docnum || info.event.title || 'Documento #' + docentry;
        var actionLabel = isResize ? 'ajustar la duracion' : 'cambiar la fecha';

        Swal.fire({
            title: '¿' + (isResize ? 'Ajustar duracion' : 'Mover documento') + '?',
            html: '<strong>' + docnum + '</strong><br>' +
                  '<b>Inicio (reparacion):</b> ' + newStart + '<br>' +
                  '<b>Fin (retorno):</b> ' + newEnd,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Si, ' + (isResize ? 'ajustar' : 'mover'),
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then(function (result) {
            if (!result.isConfirmed) {
                info.revert();
                return;
            }

            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            var formData = new FormData();
            formData.append('docentry', docentry);
            formData.append('repair_date', newStart);
            formData.append('return_date', newEnd);
            formData.append('_csrf', csrfToken);

            $.ajax({
                url: window.FullCalendarUrls.updateDate,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            }).done(function (response) {
                if (response.Success === 'Ok') {
                    showToast('success', (isResize ? 'Duracion' : 'Fecha') + ' actualizada');
                } else {
                    info.revert();
                    showToast('error', response.Msg || 'Error al actualizar');
                }
            }).fail(function () {
                info.revert();
                showToast('error', 'Error de conexion al actualizar');
            });
        });
    }

    // ── Inicializar FullCalendar ────────────────────────────────────────
    function initCalendar() {
        if (calendar) {
            calendar.destroy();
            calendar = null;
        }

        var calendarEl = document.getElementById('doc-tire-calendar');
        if (!calendarEl) return;

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            height: 'auto',
            firstDay: 1,
            editable: true,
            selectable: false,
            dayMaxEvents: 3,
            moreLinkText: function (n) {
                return '+' + n + ' mas';
            },
            noEventsText: 'No hay documentos para el periodo seleccionado',
            loading: function (isLoading) {
                calendarEl.style.opacity = isLoading ? '0.6' : '1';
            },

            // ── Eventos ──────────────────────────────────────────────
            events: function (fetchInfo, successCallback, failureCallback) {
                loadEvents(fetchInfo.startStr, fetchInfo.endStr, function (events) {
                    successCallback(events);
                });
            },

            // ── Drag & drop: confirmar con SweetAlert ────────────────
            eventDrop: function (info) {
                confirmDateChange(info, false);
            },

            // ── Redimensionar: confirmar con SweetAlert ──────────────
            eventResize: function (info) {
                confirmDateChange(info, true);
            },

            // ── Tooltip al hover ─────────────────────────────────────
            eventMouseEnter: function (info) {
                var props = info.event.extendedProps || {};
                var html = '<div style="padding:8px 12px;">';
                html += '<strong>' + (props.docnum || info.event.title) + '</strong><br>';
                html += '<span class="badge" style="background:' + info.event.backgroundColor + ';color:#fff;">' + (props.statusLabel || '') + '</span><br>';
                html += '<small>';
                html += '<b>Inicio (reparacion):</b> ' + (props.doc_date || '-') + '<br>';
                html += '<b>Fin (retorno):</b> ' + (props.return_date || '-') + '<br>';
                html += '<b>Proveedor:</b> ' + (props.provider || '-') + '<br>';
                if (props.comments) html += '<b>Comentarios:</b> ' + props.comments;
                html += '</small>';
                html += '</div>';

                if (tooltipEl) {
                    tooltipEl.innerHTML = html;
                    tooltipEl.classList.remove('d-none');
                    tooltipEl.style.display = 'block';
                }
            },

            eventMouseLeave: function () {
                if (tooltipEl) {
                    tooltipEl.style.display = 'none';
                    tooltipEl.classList.add('d-none');
                }
            },

            // ── Click simple: tooltip / Doble click: abrir edicion ──
            eventClick: function (info) {
                if (clickTimer) {
                    clearTimeout(clickTimer);
                    clickTimer = null;
                    // Doble click → abrir edicion
                    var docentry = parseInt(info.event.id, 10);
                    if (docentry && window.DocTireModuleConfig && window.DocTireModuleConfig.config && window.DocTireModuleConfig.config.routes) {
                        var updateBase = window.DocTireModuleConfig.config.routes.updateBase;
                        if (updateBase) {
                            window.location.href = updateBase + '?docentry=' + docentry;
                        }
                    }
                } else {
                    clickTimer = setTimeout(function () {
                        clickTimer = null;
                        // Click simple: el tooltip ya se muestra con eventMouseEnter
                    }, 250);
                }
            }
        });

        calendar.render();
    }

    // ── Toggle Tabla / Calendario ───────────────────────────────────────
    function setupToggle() {
        if (!toggleBtn || !calendarContainer || !tableContainer) return;

        toggleBtn.addEventListener('click', function () {
            var showingCalendar = calendarContainer.style.display !== 'none';

            if (showingCalendar) {
                calendarContainer.style.display = 'none';
                tableContainer.style.display = '';
                toggleBtn.innerHTML = '<i class="fa-solid fa-calendar-alt"></i> <span id="doc-tire-toggle-label">Calendario</span>';
                
                if (window.DocTireIndexPage && typeof window.DocTireIndexPage.loadList === 'function') {
                    window.DocTireIndexPage.loadList();
                }
            } else {
                tableContainer.style.display = 'none';
                calendarContainer.style.display = '';
                toggleBtn.innerHTML = '<i class="fa-solid fa-table"></i> <span id="doc-tire-toggle-label">Tabla</span>';

                if (calendar) {
                    calendar.refetchEvents();
                }
            }
        });
    }

    // ── Filtro por proveedor ────────────────────────────────────────────
    function setupProviderFilter() {
        if (!providerFilter) return;
        providerFilter.addEventListener('change', function () {
            if (calendar) calendar.refetchEvents();
        });
    }

    // ── Tooltip sigue al mouse ──────────────────────────────────────────
    function setupTooltipFollow() {
        if (!tooltipEl) return;
        document.addEventListener('mousemove', function (e) {
            if (tooltipEl.style.display === 'block') {
                var x = e.clientX + 15;
                var y = e.clientY + 15;
                if (x + 320 > window.innerWidth) x = e.clientX - 330;
                if (y + 200 > window.innerHeight) y = e.clientY - 210;
                tooltipEl.style.left = x + 'px';
                tooltipEl.style.top = y + 'px';
            }
        });
    }

    // ── Init ────────────────────────────────────────────────────────────
    function init() {
        if (typeof FullCalendar === 'undefined') {
            setTimeout(init, 200);
            return;
        }

        populateProviderFilter();
        initCalendar();
        setupToggle();
        setupProviderFilter();
        setupTooltipFollow();

        if (todayBtn) {
            todayBtn.addEventListener('click', function () {
                if (calendar) calendar.today();
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();