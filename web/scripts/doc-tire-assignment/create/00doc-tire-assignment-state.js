 /* doc-tire-assignment-state.js
 * 
 * Responsabilidad: Fuente Única de Verdad (Single Source of Truth) para el estado del documento.
 * Todas las operaciones de la vista modifican estos arreglos globales.
 * La interfaz (tablas, lienzo del camión) es un reflejo reactivo de estos datos.
 */

window.DocTireAssignment = window.DocTireAssignment || {};

(function(module) {
    'use strict';

    /**
     * Estado global del documento de asignación
     */
    module.State = {
        // Configuración del documento
        config: window.DocTireFormConfig?.config || {},
        
        // Datos del encabezado
        header: {
            docentry: window.DocTireFormConfig?.document?.docentry || null,
            docnum: window.DocTireFormConfig?.document?.docnum || 'Se asigna al guardar',
            series_id: window.DocTireFormConfig?.document?.series_id || null,
            status: window.DocTireFormConfig?.document?.status || 'PLAN',
            technician_user_id: window.DocTireFormConfig?.document?.technician_user_id || null,
            doc_date: window.DocTireFormConfig?.document?.doc_date || new Date().toISOString().split('T')[0],
            doc_duedate: window.DocTireFormConfig?.document?.doc_duedate || new Date().toISOString().split('T')[0],
            priority: window.DocTireFormConfig?.document?.priority || 'LOW',
            origin_type: window.DocTireFormConfig?.document?.origin_type || 'MANUAL',
            comments: window.DocTireFormConfig?.document?.comments || '',
        },

        /**
         * Unidades seleccionadas para este documento (máximo 2)
         * cada elemento: { vehicle_code, vehicle_name, odometro, comments, layout: { axles: [], mounted_tires: [] } }
         */
        unidadesSeleccionadas: [],

        /**
         * Llantas operativas del documento
         * cada elemento: { tire_code, tire_name, tire_size, tread_design, action_type, 
         *                 vehicle_code_from, position_from, vehicle_code_to, position_to, comments }
         */
        llantasSeleccionadas: [],

        /**
         * Adjuntos del documento
         */
        attachments: [],

        /**
         * Bandera que indica si hay cambios sin guardar
         */
        isDirty: false,

        /**
         * Inicializa el estado desde datos existentes (para edición)
         */
        loadFromDocument: function(documentData) {
            if (!documentData) return;

            this.header = {
                docentry: documentData.docentry || null,
                docnum: documentData.docnum || 'Se asigna al guardar',
                series_id: documentData.series_id || null,
                status: documentData.status || 'PLAN',
                technician_user_id: documentData.technician_user_id || null,
                doc_date: documentData.doc_date || new Date().toISOString().split('T')[0],
                doc_duedate: documentData.doc_duedate || new Date().toISOString().split('T')[0],
                priority: documentData.priority || 'LOW',
                origin_type: documentData.origin_type || 'MANUAL',
                comments: documentData.comments || '',
            };

            this.unidadesSeleccionadas = documentData.vehicles || [];
            this.llantasSeleccionadas = documentData.details || [];
            this.attachments = documentData.attachments || [];
            this.isDirty = false;
        },

        /**
         * Obtiene el payload completo para enviar al backend
         */
        getPayload: function() {
            return {
                docentry: this.header.docentry,
                docnum: this.header.docnum,
                series_id: this.header.series_id || null,
                status: this.header.status,
                technician_user_id: this.header.technician_user_id || null,
                doc_date: this.header.doc_date,
                doc_duedate: this.header.doc_duedate,
                priority: this.header.priority,
                origin_type: this.header.origin_type,
                comments: this.header.comments,
                vehicles: this.unidadesSeleccionadas.map(function(v) {
                    return {
                        vehicle_code: v.vehicle_code,
                        vehicle_km: v.odometro,
                        comments: v.comments,
                    };
                }),
                details: this.llantasSeleccionadas.map(function(l) {
                    return {
                        movement_type: l.action_type,
                        tire_code: l.tire_code,
                        related_tire_code: l.related_tire_code || null,
                        vehicle_code_from: l.vehicle_code_from || null,
                        vehicle_code_to: l.vehicle_code_to || null,
                        warehouse_code_from: l.warehouse_code_from || null,
                        warehouse_code_to: l.warehouse_code_to || null,
                        position_from: l.position_from || null,
                        position_to: l.position_to || null,
                        comments: l.comments || null,
                    };
                }),
                attachments: this.attachments,
            };
        },

        /**
         * Marca el estado como modificado
         */
        markDirty: function() {
            this.isDirty = true;
            module.Events.emit('state:updated', {
                unidades: this.unidadesSeleccionadas.length,
                llantas: this.llantasSeleccionadas.length
            });
        }
    };

    /**
     * Eventos del módulo (pub/sub simple)
     */
    module.Events = {
        _handlers: {},

        on: function(event, handler) {
            if (!this._handlers[event]) {
                this._handlers[event] = [];
            }
            this._handlers[event].push(handler);
        },

        off: function(event, handler) {
            if (!this._handlers[event]) return;
            this._handlers[event] = this._handlers[event].filter(function(h) {
                return h !== handler;
            });
        },

        emit: function(event, data) {
            if (!this._handlers[event]) return;
            this._handlers[event].forEach(function(handler) {
                handler(data);
            });
        }
    };

})(window.DocTireAssignment);