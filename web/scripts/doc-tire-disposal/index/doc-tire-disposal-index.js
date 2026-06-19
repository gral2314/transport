(function () {
    if (window.DocTireIndexPage) {
        return;
    }

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const toQuery = (params) => new URLSearchParams(params).toString();

    const createModal = (id) => {
        const element = document.getElementById(id);
        return element && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(element) : null;
    };

    const button = (className, icon, label, action, docentry) => `
        <button type="button" class="btn btn-sm ${className} doc-tire-action" data-action="${action}" data-docentry="${docentry}">
            <i class="fa-solid ${icon}"></i> ${label}
        </button>`;

    const dropdownItem = (icon, label, action, docentry) => `
        <a class="dropdown-item doc-tire-action" href="#" data-action="${action}" data-docentry="${docentry}">
            <i class="fa-solid ${icon} me-2"></i>${label}
        </a>`;

    window.DocTireIndexPage = {
        initialized: false,
        init() {
            if (this.initialized) {
                return;
            }

            const moduleConfig = window.DocTireModuleConfig || null;
            if (!moduleConfig || !moduleConfig.config) {
                return;
            }

            this.initialized = true;
            this.config = moduleConfig.config;
            this.routes = this.config.routes || {};
            this.tableBody = document.querySelector('#doc-tire-table tbody');
            this.searchInput = document.getElementById('doc-tire-search');

            document.getElementById('doc-tire-create')?.addEventListener('click', () => {
                window.location.href = this.routes.create;
            });

            document.getElementById('doc-tire-refresh')?.addEventListener('click', () => this.loadList());

            this.searchInput?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    this.loadList();
                }
            });

            this.tableBody?.addEventListener('click', (event) => {
                const buttonElement = event.target.closest('.doc-tire-action');
                if (!buttonElement) {
                    return;
                }

                event.preventDefault();

                const { action, docentry } = buttonElement.dataset;
                this.handleAction(action, Number(docentry || 0));
            });

            this.loadList();
        },

        async loadList() {
            if (!this.tableBody) {
                return;
            }

            this.tableBody.innerHTML = `<tr><td colspan="${(this.config.listColumns || []).length + 1}" class="text-center text-muted py-4">Cargando...</td></tr>`;
            const url = `${this.routes.list}?${toQuery({ search: this.searchInput?.value?.trim() || '' })}`;

            try {
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const payload = await response.json();
                if (payload.Success !== 'Ok') {
                    throw new Error(payload.Msg || 'No fue posible cargar la informacion');
                }

                const rows = Array.isArray(payload.Data) ? payload.Data : [];
                if (rows.length === 0) {
                    this.tableBody.innerHTML = `<tr><td colspan="${(this.config.listColumns || []).length + 1}" class="text-center text-muted py-4">No hay documentos registrados.</td></tr>`;
                    return;
                }

                this.tableBody.innerHTML = rows.map((row) => this.renderRow(row)).join('');
            } catch (error) {
                this.tableBody.innerHTML = `<tr><td colspan="${(this.config.listColumns || []).length + 1}" class="text-center text-danger py-4">${error.message}</td></tr>`;
            }
        },

        renderRow(row) {
            const columns = (this.config.listColumns || []).map((column) => `<td>${this.formatValue(row[column.field], column)}</td>`).join('');
            const docentry = Number(row.docentry || 0);
            const quickViewBtn = button('btn-outline-secondary', 'fa-eye', '', 'quick-view', docentry);
            const actions = `<div class="d-flex flex-wrap gap-1 justify-content-center align-items-center m-0 p-0">
                ${quickViewBtn}
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>${dropdownItem('fa-pen-to-square', 'Editar', 'edit', docentry)}</li>
                        <li>${dropdownItem('fa-print', 'Preview', 'preview', docentry)}</li>
                        <li>${dropdownItem('fa-file-pdf', 'PDF', 'pdf', docentry)}</li>
                        <li>${dropdownItem('fa-paper-plane', 'Correo', 'send-mail', docentry)}</li>
                        <li>${dropdownItem('fa-lock', 'Cerrar', 'close', docentry)}</li>
                        <li>${dropdownItem('fa-ban', 'Cancelar', 'cancel', docentry)}</li>
                    </ul>
                </div>
            </div>`;

            return `<tr>${columns}<td class="text-center">${actions}</td></tr>`;
        },

        formatValue(value, column) {
            let rendered = value ?? '';
            if (column.lookup && Object.prototype.hasOwnProperty.call(column.lookup, rendered)) {
                rendered = column.lookup[rendered];
            }

            if (column.badge) {
                const color = rendered === 'Cancelado' || rendered === 'Si'
                    ? 'danger'
                    : rendered === 'Cerrado'
                        ? 'success'
                        : rendered === 'Abierto'
                            ? 'warning text-dark'
                            : 'secondary';
                return `<span class="badge bg-${color}">${rendered || '-'}</span>`;
            }

            return rendered === '' ? '<span class="text-muted">-</span>' : String(rendered);
        },

        async handleAction(action, docentry) {
            switch (action) {
                case 'edit':
                    window.location.href = `${this.routes.updateBase}?docentry=${docentry}`;
                    return;
                case 'preview':
                    window.open(`${this.routes.previewBase}?docentry=${docentry}`, '_blank');
                    return;
                case 'pdf':
                    window.open(`${this.routes.pdfBase}?docentry=${docentry}`, '_blank');
                    return;
                case 'quick-view':
                    await this.openModalContent('doc-tire-quick-view-body', 'doc-tire-quick-view-modal', `${this.routes.quickViewBase}?docentry=${docentry}`);
                    return;
                case 'send-mail':
                    await this.openModalContent('doc-tire-send-mail-body', 'doc-tire-send-mail-modal', `${this.routes.sendMailBase}?docentry=${docentry}`, true);
                    return;
                case 'close':
                    await this.postActionWithConfirmation('Cerrar documento', 'El documento quedara bloqueado para edicion.', this.routes.close, docentry);
                    return;
                case 'cancel':
                    await this.postActionWithConfirmation('Cancelar documento', 'La cancelacion es logica y conservara el folio.', this.routes.cancel, docentry);
                    return;
                default:
                    return;
            }
        },

        async openModalContent(targetId, modalId, url, bindMailForm = false) {
            const target = document.getElementById(targetId);
            if (!target) {
                return;
            }

            target.innerHTML = '<div class="text-center py-3 text-muted">Cargando...</div>';
            const modal = createModal(modalId);
            modal?.show();

            try {
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                target.innerHTML = await response.text();
                if (bindMailForm) {
                    this.bindMailForm(target.querySelector('#doc-tire-send-mail-form'), modal);
                }
            } catch (error) {
                target.innerHTML = `<div class="alert alert-danger mb-0">${error.message}</div>`;
            }
        },

        bindMailForm(form, modal) {
            if (!form) {
                return;
            }

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const docentry = form.dataset.docentry;
                const formData = new FormData(form);
                formData.append('_csrf', csrfToken());

                try {
                    const response = await fetch(`${this.routes.sendMailBase}?docentry=${docentry}`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData,
                    });
                    const payload = await response.json();
                    if (payload.Success !== 'Ok') {
                        throw new Error(payload.Msg || 'No se pudo enviar el correo');
                    }

                    modal?.hide();
                    Swal.fire('Correo enviado', payload.Msg || 'Operacion completada', 'success');
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                }
            }, { once: true });
        },

        async postActionWithConfirmation(title, text, url, docentry) {
            const confirmation = await Swal.fire({
                title,
                text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Continuar',
                cancelButtonText: 'Cancelar',
            });

            if (!confirmation.isConfirmed) {
                return;
            }

            const formData = new FormData();
            formData.append('_csrf', csrfToken());
            formData.append('docentry', String(docentry));

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData,
                });
                const payload = await response.json();
                if (payload.Success !== 'Ok') {
                    throw new Error(payload.Msg || 'Operacion no completada');
                }

                Swal.fire('Proceso completado', payload.Msg || 'Operacion correcta', 'success');
                this.loadList();
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },
    };

    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            window.DocTireIndexPage.init();
        });
    } else {
        window.DocTireIndexPage.init();
    }
})();