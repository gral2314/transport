(function () {
    if (window.DocTireFormPage) {
        return;
    }

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const normalizeOptions = (field, formOptions) => {
        if (field.options) {
            return field.options;
        }

        const source = formOptions?.[field.optionsFrom] || [];
        if (!Array.isArray(source)) {
            return source;
        }

        if (source.length === 0) {
            return {};
        }

        if (typeof source[0] === 'object') {
            return source.reduce((carry, row) => {
                const value = row.code ?? row.id ?? row.value;
                const label = row.name ?? row.label ?? value;
                if (value !== undefined) {
                    carry[String(value)] = String(label ?? value);
                }
                return carry;
            }, {});
        }

        return source;
    };

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const buildInput = (field, value, formOptions) => {
        const fieldName = escapeHtml(field.name);
        const currentValue = value ?? '';
        if (field.type === 'select') {
            const options = normalizeOptions(field, formOptions);
            const items = [`<option value="">Seleccionar...</option>`].concat(
                Object.entries(options).map(([optionValue, label]) => `<option value="${escapeHtml(optionValue)}" ${String(currentValue) === String(optionValue) ? 'selected' : ''}>${escapeHtml(label)}</option>`),
            );
            return `<select class="form-select form-select-sm" data-row-field="${fieldName}">${items.join('')}</select>`;
        }

        const step = field.step ? `step="${escapeHtml(field.step)}"` : '';
        const type = field.type === 'number' ? 'number' : 'text';
        return `<input type="${type}" class="form-control form-control-sm" data-row-field="${fieldName}" value="${escapeHtml(currentValue)}" ${step}>`;
    };

    window.DocTireFormPage = {
        initialized: false,
        init() {
            if (this.initialized) {
                return;
            }

            const payload = window.DocTireFormConfig || null;
            if (!payload || !payload.config) {
                return;
            }

            this.initialized = true;
            this.config = payload.config;
            this.routes = this.config.routes || {};
            this.document = payload.document || {};
            this.formOptions = payload.formOptions || {};
            this.vehicles = Array.isArray(this.document.vehicles) ? this.document.vehicles : [];
            this.details = Array.isArray(this.document.details) ? this.document.details : [];
            this.attachments = Array.isArray(this.document.attachments) ? this.document.attachments : [];

            document.getElementById('add-vehicle-row')?.addEventListener('click', () => {
                this.vehicles.push({});
                this.renderRows('vehicles');
            });
            document.getElementById('add-detail-row')?.addEventListener('click', () => {
                this.details.push({});
                this.renderRows('details');
            });
            document.getElementById('add-attachment-row')?.addEventListener('click', () => {
                this.attachments.push({});
                this.renderRows('attachments');
            });

            document.getElementById('doc-tire-save')?.addEventListener('click', () => this.save());
            document.getElementById('doc-tire-open-preview')?.addEventListener('click', () => this.openPreview());

            ['vehicles', 'details', 'attachments'].forEach((group) => {
                document.getElementById(`doc-${group}-body`)?.addEventListener('click', (event) => {
                    const button = event.target.closest('.remove-row');
                    if (!button) {
                        return;
                    }
                    const index = Number(button.dataset.index || -1);
                    this[group].splice(index, 1);
                    this.renderRows(group);
                });
            });

            this.renderRows('vehicles');
            this.renderRows('details');
            this.renderRows('attachments');
            this.refreshSummary();
        },

        renderRows(group) {
            const body = document.getElementById(`doc-${group}-body`);
            const fields = this.config[group === 'vehicles' ? 'vehicleFields' : group === 'details' ? 'detailFields' : 'attachmentFields'] || [];
            if (!body) {
                return;
            }

            const rows = this[group] || [];
            if (rows.length === 0) {
                body.innerHTML = `<tr><td colspan="${fields.length + 1}" class="text-center text-muted py-3">Sin registros.</td></tr>`;
                this.refreshSummary();
                return;
            }

            body.innerHTML = rows.map((row, index) => {
                const cells = fields.map((field) => `<td>${buildInput(field, row[field.name], this.formOptions)}</td>`).join('');
                return `<tr data-index="${index}">${cells}<td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row" data-index="${index}"><i class="fa-solid fa-trash"></i></button></td></tr>`;
            }).join('');

            this.refreshSummary();
        },

        collectRows(group) {
            const body = document.getElementById(`doc-${group}-body`);
            if (!body) {
                return [];
            }

            return Array.from(body.querySelectorAll('tr[data-index]')).map((row) => {
                const data = {};
                row.querySelectorAll('[data-row-field]').forEach((input) => {
                    data[input.dataset.rowField] = input.value;
                });
                return data;
            });
        },

        collectPayload() {
            const payload = {};
            (this.config.headerFields || []).forEach((field) => {
                const input = document.getElementById(field.name);
                if (input) {
                    payload[field.name] = input.value;
                }
            });

            payload.docentry = document.getElementById('docentry')?.value || '';
            payload.comments = document.getElementById('comments')?.value || '';
            payload.vehicles = this.collectRows('vehicles');
            payload.details = this.collectRows('details');
            payload.attachments = this.collectRows('attachments');

            if (!payload.docentry) {
                delete payload.docentry;
            }

            return payload;
        },

        async save() {
            const formData = new FormData();
            const payload = this.collectPayload();

            Object.entries(payload).forEach(([key, value]) => {
                if (Array.isArray(value)) {
                    formData.append(key, JSON.stringify(value));
                } else {
                    formData.append(key, value ?? '');
                }
            });

            formData.append('_csrf', csrfToken());

            try {
                const response = await fetch(this.routes.save, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData,
                });
                const result = await response.json();
                if (result.Success !== 'Ok') {
                    throw new Error(result.Msg || 'No se pudo guardar el documento');
                }

                const savedDocument = result.Data || {};
                this.document = savedDocument;
                document.getElementById('docentry').value = savedDocument.docentry || '';
                const folio = document.getElementById('docnum');
                if (folio) {
                    folio.value = savedDocument.docnum || folio.value;
                }
                document.getElementById('doc-tire-open-preview')?.removeAttribute('disabled');

                const decision = await Swal.fire({
                    title: 'Documento guardado',
                    text: 'Puede continuar capturando, abrir el preview o volver al listado.',
                    icon: 'success',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: 'Abrir preview',
                    denyButtonText: 'Volver al listado',
                    cancelButtonText: 'Seguir editando',
                });

                if (decision.isConfirmed) {
                    this.openPreview();
                } else if (decision.isDenied) {
                    window.location.href = this.routes.index;
                }
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },

        openPreview() {
            const docentry = document.getElementById('docentry')?.value;
            if (!docentry) {
                Swal.fire('Pendiente', 'Guarde el documento antes de abrir el preview.', 'info');
                return;
            }

            window.open(`${this.routes.previewBase}?docentry=${docentry}`, '_blank');
        },

        refreshSummary() {
            const detailsCount = this.collectRows('details').length;
            const attachmentsCount = this.collectRows('attachments').length;
            const vehiclesCount = this.collectRows('vehicles').length;

            const detailElement = document.getElementById('summary-detail-count');
            const attachmentElement = document.getElementById('summary-attachment-count');
            const vehicleElement = document.getElementById('summary-vehicle-count');

            if (detailElement) {
                detailElement.textContent = String(detailsCount);
            }
            if (attachmentElement) {
                attachmentElement.textContent = String(attachmentsCount);
            }
            if (vehicleElement) {
                vehicleElement.textContent = String(vehiclesCount);
            }
        },
    };
})();