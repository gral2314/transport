/**
 * 00_state.js — Namespace global y helpers para DocTireMaintenance (Create/Update)
 *
 * Responsabilidad: Fuente Única de Verdad (Single Source of Truth) para el estado del formulario.
 * Todas las operaciones leen y modifican estos datos globales.
 * La interfaz (tablas, resumen) es un reflejo de este estado.
 *
 * DynamicAssetBundle lo carga desde web/scripts/doc-tire-maintenance/create/
 */

window.DocTireMntForm = window.DocTireMntForm || {};

(function (module) {
    'use strict';

    // ── Helpers ──────────────────────────────────────────────────────────

    /** Escape HTML para prevenir XSS */
    module.e = function (str) {
        if (!str && str !== 0) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(String(str)));
        return div.innerHTML;
    };

    /** Obtener CSRF token */
    module.csrf = function () {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    };

    /** Obtener instancia de modal Bootstrap */
    module.getModal = function (id) {
        var el = document.getElementById(id);
        if (!el) return null;
        try {
            return window.bootstrap.Modal.getOrCreateInstance(el);
        } catch (e) {
            return null;
        }
    };

    /** Toast con SweetAlert2 */
    module.toast = function (icon, title) {
        if (typeof Swal === 'undefined') return;
        Swal.fire({
            icon: icon,
            title: title,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    };

    // ── Event Bus ────────────────────────────────────────────────────────

    module.Events = {
        _listeners: {},

        on: function (name, fn) {
            if (!this._listeners[name]) this._listeners[name] = [];
            this._listeners[name].push(fn);
        },

        off: function (name, fn) {
            if (!this._listeners[name]) return;
            this._listeners[name] = this._listeners[name].filter(function (f) { return f !== fn; });
        },

        emit: function (name, data) {
            if (!this._listeners[name]) return;
            this._listeners[name].forEach(function (fn) {
                try { fn(data); } catch (e) { console.error('[Events] Error en ' + name + ':', e); }
            });
        }
    };

    // ── State ────────────────────────────────────────────────────────────

    module.State = {
        /** Configuración del módulo (inyectada por PHP) */
        config: window.DocTireFormConfig?.config || {},

        /** Datos del documento actual */
        header: {
            docentry: null,
            docnum: 'Se asigna al guardar',
            series_id: null,
            doc_date: new Date().toISOString().split('T')[0],
            doc_duedate: new Date().toISOString().split('T')[0],
            repair_date: new Date().toISOString().split('T')[0],
            return_date: '',
            provider_code: null,
            status: 'PLAN',
            technician_user_id: null,
            comments: ''
        },

        /** Detalles operativos [{ tire_code, tire_km, tread_depth, repair_type, comments }] */
        details: [],

        /** Adjuntos [{ linenum, filename, filepath, notes, file }] */
        attachments: [],

        /** Llantas seleccionadas del modal (antes de confirmar) */
        selectedTires: {},

        /** Contadores */
        detailCounter: 0,
        attachmentCounter: 0,

        /** Bandera de cambios sin guardar */
        isDirty: false,

        /**
         * Inicializa el estado desde los datos inyectados por PHP.
         */
        init: function () {
            var formConfig = window.DocTireFormConfig || {};
            var documentData = formConfig.document || {};
            var isNewRecord = formConfig.isNewRecord !== false;

            this.header.docentry = documentData.docentry || null;
            this.header.docnum = documentData.docnum || 'Se asigna al guardar';
            this.header.series_id = documentData.series_id || null;
            this.header.doc_date = documentData.doc_date || new Date().toISOString().split('T')[0];
            this.header.doc_duedate = documentData.doc_duedate || new Date().toISOString().split('T')[0];
            this.header.repair_date = documentData.repair_date || new Date().toISOString().split('T')[0];
            this.header.return_date = documentData.return_date || '';
            this.header.provider_code = documentData.provider_code || null;
            this.header.status = documentData.status || 'PLAN';
            this.header.technician_user_id = documentData.technician_user_id || null;
            this.header.comments = documentData.comments || '';

            this.details = Array.isArray(documentData.details) ? documentData.details : [];
            this.attachments = Array.isArray(documentData.attachments) ? documentData.attachments : [];
            this.detailCounter = this.details.length;
            this.attachmentCounter = this.attachments.length;
            this.isDirty = false;
        },

        /** Marcar como sucio */
        markDirty: function () {
            this.isDirty = true;
        },

        /**
         * Construye el payload para enviar al backend.
         */
        getPayload: function () {
            return {
                docentry: this.header.docentry,
                docnum: this.header.docnum,
                series_id: this.header.series_id || null,
                doc_date: this.header.doc_date,
                doc_duedate: this.header.doc_duedate,
                repair_date: this.header.repair_date,
                return_date: this.header.return_date || null,
                provider_code: this.header.provider_code || null,
                status: this.header.status,
                technician_user_id: this.header.technician_user_id || null,
                comments: this.header.comments,
                details: this.details,
                attachments: this.attachments
            };
        }
    };

    //console.log('[DocTireMntForm] Namespace inicializado.');
})(window.DocTireMntForm);
