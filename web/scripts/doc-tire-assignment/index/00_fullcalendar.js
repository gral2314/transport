/**
 * FullCalendar para DocTireAssignment — list/index
 * Dependencias: FullCalendar 6, jQuery, Bootstrap, SweetAlert2
 * Cargado por _calendar.php via registerJsFile con depends=[AdmindeskAsset]
 */
(function () {
    if (window.DocTireCalendarInit) return;
    window.DocTireCalendarInit = true;

    var calendar = null;
    var calendarContainer = document.getElementById('doc-tire-calendar-container');
    var tableCard = document.getElementById('doc-tire-table-card');
    var toggleBtn = document.getElementById('doc-tire-toggle-calendar');
    var toggleLabel = document.getElementById('doc-tire-toggle-label');
    var employeeFilter = document.getElementById('calendar-employee-filter');
    var todayBtn = document.getElementById('calendar-today-btn');
    var tooltipEl = document.getElementById('calendar-event-tooltip');

    // Variable para detectar doble click
    var clickTimer = null;

    // ── Poblar select de mecánicos ──────────────────────────────────────
    function populateEmployeeFilter() {
        if (!employeeFilter) return;
        var options = window.FullCalendarUrls && window.FullCalendarUrls.mechanicOptions;
        if (!options || !Array.isArray(options)) return;

        employeeFilter.innerHTML = '<option value="">Todos los mecánicos</option>';
        options.forEach(function (opt) {
            var val = opt.id || opt.code || opt.value || '';
            var label = opt.name || opt.text || opt.label || val;
            if (!val) return;
            var o = document.createElement('option');
            o.value = val;
            o.textContent = label;
            employeeFilter.appendChild(o);
        });
    }

    // ── Cargar eventos desde el endpoint ────────────────────────────────
    function loadEvents(start, end, callback) {
        var params = {};
        if (employeeFilter && employeeFilter.value) {
            params.technician_user_id = employeeFilter.value;
        }

        $.ajax({
            url: window.FullCalendarUrls.events,
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
            console.error('Error de conexión al cargar eventos:', textStatus);
            callback([]);
        });
    }

    // ── Mostrar notificación toast con SweetAlert2 ──────────────────────
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
        var actionLabel = isResize ? 'ajustar la duración' : 'cambiar la fecha';

        Swal.fire({
            title: '¿' + (isResize ? 'Ajustar duración' : 'Mover documento') + '?',
            html: '<strong>' + docnum + '</strong><br>' +
                  '<b>Inicio:</b> ' + newStart + '<br>' +
                  '<b>Fin:</b> ' + newEnd,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, ' + (isResize ? 'ajustar' : 'mover'),
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then(function (result) {
            if (!result.isConfirmed) {
                info.revert();
                return;
            }

            $.ajax({
                url: window.FullCalendarUrls.updateDate,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    docentry: docentry,
                    start_date: newStart,
                    end_date: newEnd
                }),
                dataType: 'json'
            }).done(function (response) {
                if (response.Success === 'Ok') {
                    showToast('success', (isResize ? 'Duración' : 'Fecha') + ' actualizada');
                } else {
                    info.revert();
                    showToast('error', response.Msg || 'Error al actualizar');
                }
            }).fail(function () {
                info.revert();
                showToast('error', 'Error de conexión al actualizar');
            });
        });
    }

    // ── Inicializar FullCalendar ────────────────────────────────────────
    function initCalendar() {
        if (calendar) {
            calendar.destroy();
            calendar = null;
        }

        var calendarEl = document.getElementById('fullcalendar-container');
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
                return '+' + n + ' más';
            },
            noEventsText: 'No hay documentos para el período seleccionado',
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
                html += '<b>Inicio:</b> ' + (props.doc_date || '-') + '<br>';
                html += '<b>Fin:</b> ' + (props.doc_duedate || '-') + '<br>';
                html += '<b>Técnico:</b> ' + (props.technician || '-') + '<br>';
                if (props.priority) html += '<b>Prioridad:</b> ' + props.priority + '<br>';
                if (props.comments) html += '<b>Comentarios:</b> ' + props.comments;
                html += '</small>';
                html += '</div>';

                if (tooltipEl) {
                    tooltipEl.innerHTML = html;
                    tooltipEl.style.display = 'block';
                }
            },

            eventMouseLeave: function () {
                if (tooltipEl) {
                    tooltipEl.style.display = 'none';
                }
            },

            // ── Click simple: tooltip / Doble click: abrir edición ──
            eventClick: function (info) {
                if (clickTimer) {
                    clearTimeout(clickTimer);
                    clickTimer = null;
                    // Doble click → abrir edición
                    var docentry = parseInt(info.event.id, 10);
                    if (docentry && window.DocTireUrls && window.DocTireUrls.updateBase) {
                        window.location.href = window.DocTireUrls.updateBase + '?docentry=' + docentry;
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
        if (!toggleBtn || !calendarContainer || !tableCard) return;

        toggleBtn.addEventListener('click', function () {
            var showingCalendar = calendarContainer.style.display !== 'none';

            if (showingCalendar) {
                calendarContainer.style.display = 'none';
                tableCard.style.display = '';
                toggleLabel.textContent = 'Calendario';
                toggleBtn.innerHTML = '<i class="fa-solid fa-calendar-alt"></i> <span id="doc-tire-toggle-label">Calendario</span>';

                if (window.DocTireIndexPage && typeof window.DocTireIndexPage.loadList === 'function') {
                    window.DocTireIndexPage.loadList();
                }
            } else {
                tableCard.style.display = 'none';
                calendarContainer.style.display = '';
                toggleLabel.textContent = 'Tabla';
                toggleBtn.innerHTML = '<i class="fa-solid fa-table"></i> <span id="doc-tire-toggle-label">Tabla</span>';

                if (calendar) {
                    calendar.refetchEvents();
                }
            }
        });
    }

    // ── Filtro por empleado ─────────────────────────────────────────────
    function setupEmployeeFilter() {
        if (!employeeFilter) return;
        employeeFilter.addEventListener('change', function () {
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

        populateEmployeeFilter();
        initCalendar();
        setupToggle();
        setupEmployeeFilter();
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
