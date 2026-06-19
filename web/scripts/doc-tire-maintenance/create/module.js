/**
 * module.js — Orquestador del formulario DocTireMaintenance (Create/Update)
 *
 * DynamicAssetBundle carga este archivo automáticamente y también
 * todos los demás *.js de este directorio en orden alfabético.
 *
 * Este orquestador:
 * 1. Inicializa el State desde los datos de PHP
 * 2. Llama a cada sub-módulo en orden
 * 3. Configura el comportamiento específico create vs update
 * 4. Muestra/oculta botones de workflow según estado
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var module = window.DocTireMntForm;

    if (!module) {
        console.error('[DocTireMntForm] Namespace no disponible. ¿Falta 00_state.js?');
        return;
    }

    if (!window.DocTireFormConfig) {
        console.error('[DocTireMntForm] Configuración no disponible. ¿Falta window.DocTireFormConfig?');
        return;
    }

    var isNewRecord = window.DocTireFormConfig.isNewRecord !== false;

    // ── Paso 1: Inicializar State ───────────────────────────────────────
    module.State.init();

    // ── Paso 2: Inicializar sub-módulos en orden ────────────────────────
    // 01_series.js
    if (typeof module._seriesInit === 'function') {
        module._seriesInit();
    }

    // 02_tire_selector.js
    if (typeof module._tireSelectorInit === 'function') {
        module._tireSelectorInit();
    }

    // 03_details.js
    if (typeof module._detailsInit === 'function') {
        module._detailsInit();
    }

    // 04_attachments.js
    if (typeof module._attachmentsInit === 'function') {
        module._attachmentsInit();
    }

    // 05_workflow.js
    if (typeof module._workflowInit === 'function') {
        module._workflowInit();
    }

    // 06_save.js
    if (typeof module._saveInit === 'function') {
        module._saveInit();
    }

    // ── Paso 3: Configuración create vs update ──────────────────────────
    configureEditMode(isNewRecord);

    // ── Paso 4: Mostrar/ocultar botones de workflow según estado ────────
    configureWorkflowButtons();

    // ── Paso 5: Prevenir salida con cambios sin guardar ─────────────────
    window.addEventListener('beforeunload', function (e) {
        if (module.State.isDirty) {
            e.preventDefault();
            e.returnValue = 'Tiene cambios sin guardar. ¿Desea salir?';
            return e.returnValue;
        }
    });

    //console.log('[DocTireMntForm] Formulario inicializado. Modo: ' + (isNewRecord ? 'Crear' : 'Editar'));
});

/**
 * Configura el formulario según el modo (crear o editar).
 */
function configureEditMode(isNewRecord) {
    var module = window.DocTireMntForm;
    var State = module.State;

    if (!isNewRecord) {
        // Modo edición: deshabilitar selector de serie
        var seriesEl = document.getElementById('series_id');
        if (seriesEl) {
            seriesEl.disabled = true;
        }

        // El folio no debe cambiar
        var docnumEl = document.getElementById('docnum');
        if (docnumEl) {
            docnumEl.readOnly = true;
        }

        // Mostrar botón de vista previa
        var previewBtn = document.getElementById('doc-tire-open-preview');
        if (previewBtn) {
            previewBtn.classList.remove('d-none');
        }
    } else {
        // Modo creación: ocultar vista previa y botones de workflow
        var previewBtn = document.getElementById('doc-tire-open-preview');
        if (previewBtn) {
            previewBtn.classList.add('d-none');
        }
    }
}

/**
 * Muestra los botones de workflow relevantes según el estado actual del documento.
 */
function configureWorkflowButtons() {
    var module = window.DocTireMntForm;
    var State = module.State;
    var status = State.header.status;

    // Ocultar todos primero
    var allBtns = document.querySelectorAll('.workflow-btn');
    allBtns.forEach(function (btn) {
        btn.classList.add('d-none');
    });

    if (!status) return;

    // Mostrar botones según estado
    var btnMap = {
        'PLAN': ['release', 'cancel'],
        'LIBERADO': ['start', 'cancel'],
        'EN_PROCESO': ['execute', 'reject', 'cancel'],
        'EJECUTADO': ['validate', 'close', 'reject', 'cancel'],
        'VALIDADO': ['close', 'reject', 'cancel'],
        'CERRADO': [],
        'CANCELADO': [],
        'RECHAZADO': ['close', 'cancel']
    };

    var allowed = btnMap[status];
    if (!allowed) return;

    allowed.forEach(function (action) {
        var btn = document.getElementById('wf-btn-' + action);
        if (btn) btn.classList.remove('d-none');
    });
}