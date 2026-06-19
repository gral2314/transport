/**
 * 05_workflow.js — Acciones de flujo de trabajo
 *
 * Responsabilidad:
 * - Escuchar clics en .workflow-btn
 * - Abrir modal #mdl-mnt-form-workflow
 * - Mostrar mensaje contextual según acción y estado actual
 * - Cargar técnicos (para acciones que requieren asignación)
 * - Confirmar y enviar POST al endpoint correspondiente
 * - Recargar o redirigir al finalizar
 */

(function (module) {
    'use strict';

    var State = module.State;
    var Events = module.Events;
    var e = module.e;
    var getModal = module.getModal;

    var pendingAction = null;

    // Mapa de acciones → configuración del modal
    var actionConfig = {
        'release': {
            label: 'Liberar',
            alertClass: 'alert-info',
            title: 'Liberar documento',
            message: 'El documento quedará liberado y listo para ejecución.',
            confirmClass: 'btn-info',
            confirmLabel: 'Liberar',
            showTech: false
        },
        'start': {
            label: 'Iniciar',
            alertClass: 'alert-primary',
            title: 'Iniciar reparación',
            message: '¿Confirma el inicio de la reparación?',
            confirmClass: 'btn-primary',
            confirmLabel: 'Iniciar',
            showTech: false
        },
        'execute': {
            label: 'Ejecutar',
            alertClass: 'alert-warning',
            title: 'Ejecutar reparación',
            message: 'Marque como ejecutada la reparación.',
            confirmClass: 'btn-warning',
            confirmLabel: 'Ejecutar',
            showTech: true
        },
        'validate': {
            label: 'Validar',
            alertClass: 'alert-success',
            title: 'Validar reparación',
            message: 'Confirme que la reparación ha sido validada.',
            confirmClass: 'btn-success',
            confirmLabel: 'Validar',
            showTech: false
        },
        'reject': {
            label: 'Rechazar',
            alertClass: 'alert-danger',
            title: 'Rechazar documento',
            message: '¿Está seguro de rechazar este documento?',
            confirmClass: 'btn-danger',
            confirmLabel: 'Rechazar',
            showTech: false
        },
        'close': {
            label: 'Cerrar',
            alertClass: 'alert-secondary',
            title: 'Cerrar documento',
            message: 'El documento se marcará como cerrado.',
            confirmClass: 'btn-secondary',
            confirmLabel: 'Cerrar',
            showTech: false
        },
        'cancel': {
            label: 'Cancelar',
            alertClass: 'alert-dark',
            title: 'Cancelar documento',
            message: '¿Está seguro de cancelar este documento? Esta acción no se puede deshacer.',
            confirmClass: 'btn-dark',
            confirmLabel: 'Cancelar documento',
            showTech: false
        }
    };

    function init() {
        // Delegación: todos los botones de workflow
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.workflow-btn');
            if (!btn) return;
            e.preventDefault();

            // Determinar acción desde el ID (wf-btn-release → release)
            var action = '';
            var btnId = btn.id || '';
            var match = btnId.match(/^wf-btn-(.+)$/);
            if (match) {
                action = match[1];
            } else {
                // Fallback: leer data-action
                action = btn.getAttribute('data-action') || '';
            }

            if (!action || !actionConfig[action]) {
                console.warn('[05_workflow] Acción no reconocida:', action);
                return;
            }

            openWorkflowModal(action);
        });

        // Botón confirmar del modal
        var confirmBtn = document.getElementById('mdl-mnt-form-wf-confirm');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                executeWorkflow();
            });
        }

        //console.log('[05_workflow] Inicializado.');
    }

    function openWorkflowModal(action) {
        pendingAction = action;
        var cfg = actionConfig[action];
        if (!cfg) return;

        // Configurar modal
        var titleEl = document.getElementById('mdl-mnt-form-wf-title');
        var alertEl = document.getElementById('mdl-mnt-form-wf-alert');
        var messageEl = document.getElementById('mdl-mnt-form-wf-message');
        var actionEl = document.getElementById('mdl-mnt-form-wf-action');
        var techRow = document.getElementById('mdl-mnt-form-wf-tech-row');
        var commentsEl = document.getElementById('mdl-mnt-form-wf-comments');
        var confirmBtn = document.getElementById('mdl-mnt-form-wf-confirm');

        if (titleEl) titleEl.textContent = cfg.title || 'Confirmar acción';
        if (alertEl) {
            alertEl.className = 'alert ' + (cfg.alertClass || 'alert-info');
            alertEl.textContent = 'Estado actual: ' + (State.header.status || 'N/A') + ' → ' + cfg.label;
        }
        if (messageEl) messageEl.textContent = cfg.message || '';
        if (actionEl) actionEl.value = action;
        if (techRow) techRow.classList.toggle('d-none', !cfg.showTech);
        if (commentsEl) commentsEl.value = '';
        if (confirmBtn) {
            confirmBtn.className = 'btn btn-sm ' + (cfg.confirmClass || 'btn-primary');
            confirmBtn.textContent = cfg.confirmLabel || 'Confirmar';
        }

        // Cargar técnicos si aplica
        if (cfg.showTech) {
            loadTechnicians();
        }

        // Abrir modal
        var modal = getModal('mdl-mnt-form-workflow');
        if (modal) modal.show();
    }

    function loadTechnicians() {
        var select = document.getElementById('mdl-mnt-form-wf-technician');
        if (!select) return;

        var formOptions = window.DocTireFormConfig?.formOptions || {};
        var technicians = formOptions.technician_options || [];

        select.innerHTML = '<option value="">Seleccionar técnico...</option>';
        technicians.forEach(function (tech) {
            var value = tech.id || tech.code || '';
            var label = tech.name || tech.text || value;
            if (!value) return;
            var option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            select.appendChild(option);
        });
    }

    function executeWorkflow() {
        if (!pendingAction) return;

        var routes = window.DocTireFormUrls || {};
        var url = routes[pendingAction];

        if (!url) {
            module.toast('error', 'Ruta no configurada para la acción: ' + pendingAction);
            return;
        }

        var technician = document.getElementById('mdl-mnt-form-wf-technician')?.value || '';
        var comments = document.getElementById('mdl-mnt-form-wf-comments')?.value || '';

        // Si la acción requiere técnico, validar
        var cfg = actionConfig[pendingAction];
        if (cfg && cfg.showTech && !technician) {
            module.toast('warning', 'Seleccione un técnico antes de continuar.');
            return;
        }

        var payload = {
            docentry: State.header.docentry,
            comments: comments,
            technician_user_id: technician || null
        };

        // Mostrar loading en el botón confirmar
        var confirmBtn = document.getElementById('mdl-mnt-form-wf-confirm');
        var originalText = confirmBtn ? confirmBtn.textContent : '';
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Procesando...';
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-Token': module.csrf()
            },
            body: JSON.stringify(payload)
        })
        .then(function (response) {
            var contentType = response.headers.get('content-type') || '';
            if (contentType.indexOf('application/json') !== -1) {
                return response.json();
            }
            return response.text().then(function (text) {
                try { return JSON.parse(text); } catch (e) { throw new Error('Error de comunicación.'); }
            });
        })
        .then(function (result) {
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.textContent = originalText;
            }

            if (result && result.Success === 'Ok') {
                // Cerrar modal
                var modal = getModal('mdl-mnt-form-workflow');
                if (modal) modal.hide();

                module.toast('success', result.Msg || cfg.label + ' completado.');
                // Recargar después de breve pausa
                setTimeout(function () {
                    window.location.reload();
                }, 1200);
            } else {
                module.toast('error', result?.Msg || 'Error al ejecutar la acción.');
            }
        })
        .catch(function (err) {
            console.error('[05_workflow] Error:', err);
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.textContent = originalText;
            }
            module.toast('error', 'Error de comunicación al ejecutar la acción.');
        });
    }

    module._workflowInit = init;
})(window.DocTireMntForm);
