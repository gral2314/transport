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

    const button = (className, icon, label, action, docentry, extraAttrs = '') => `
        <button type="button" class="btn btn-sm ${className} doc-tire-action" data-action="${action}" data-docentry="${docentry}" ${extraAttrs}>
            <i class="fa-solid ${icon}"></i> ${label}
        </button>`;

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

            document.getElementById('doc-tire-refresh')?.addEventListener('click', () => {
                this.currentFilter.page = 1;
                this.loadList();
            });

            this.searchInput?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    this.currentFilter.page = 1;
                    this.loadList();
                }
            });

            this.tableBody?.addEventListener('click', (event) => {
                const buttonElement = event.target.closest('.doc-tire-action');
                if (!buttonElement) {
                    return;
                }

                const { action, docentry } = buttonElement.dataset;
                this.handleAction(action, Number(docentry || 0));
            });

            // Status toggle filters
            this.currentFilter = { status: '', page: 1, perPage: 10 };
            const toggleContainer = document.getElementById('doc-tire-status-filters');
            if (toggleContainer) {
                toggleContainer.addEventListener('click', (event) => {
                    const btn = event.target.closest('button[data-status]');
                    if (!btn) {
                        return;
                    }

                    const status = btn.dataset.status || '';
                    const allButtons = toggleContainer.querySelectorAll('button[data-status]');

                    if (btn.classList.contains('active')) {
                        // Click on already-active button → clear filter (show all)
                        allButtons.forEach(b => b.classList.remove('active'));
                        const allBtn = toggleContainer.querySelector('button[data-status=""]');
                        allBtn?.classList.add('active');
                        this.currentFilter.status = '';
                    } else {
                        // Activate clicked button, deactivate others
                        allButtons.forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        this.currentFilter.status = status;
                    }
                    this.currentFilter.page = 1;
                    this.loadList();
                });
            }

            this.loadList();
        },

        async loadList() {
            if (!this.tableBody) {
                return;
            }

            this.tableBody.innerHTML = `<tr><td colspan="${(this.config.listColumns || []).length + 1}" class="text-center text-muted py-4">Cargando...</td></tr>`;
            const page = this.currentFilter?.page || 1;
            const perPage = this.currentFilter?.perPage || 10;
            const url = `${this.routes.list}?${toQuery({ search: this.searchInput?.value?.trim() || '', status: this.currentFilter?.status || '', page: page, per_page: perPage })}`;

            try {
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const payload = await response.json();
                if (payload.Success !== 'Ok') {
                    throw new Error(payload.Msg || 'No fue posible cargar la informacion');
                }

                // Formato paginado: Data.items + Data.pagination
                const data = payload.Data;
                const rows = Array.isArray(data?.items) ? data.items : (Array.isArray(data) ? data : []);
                const pagination = data?.pagination || null;

                if (rows.length === 0) {
                    this.tableBody.innerHTML = `<tr><td colspan="${(this.config.listColumns || []).length + 1}" class="text-center text-muted py-4">No hay documentos registrados.</td></tr>`;
                    this.renderPagination(null);
                    return;
                }

                this.tableBody.innerHTML = rows.map((row) => this.renderRow(row)).join('');
                this.renderPagination(pagination);
            } catch (error) {
                this.tableBody.innerHTML = `<tr><td colspan="${(this.config.listColumns || []).length + 1}" class="text-center text-danger py-4">${error.message}</td></tr>`;
                this.renderPagination(null);
            }
        },

        renderPagination(pagination) {
            const container = document.getElementById('doc-tire-pagination');
            if (!container) return;
            if (!pagination || pagination.totalPages <= 1) {
                container.innerHTML = '';
                return;
            }
            const { page, totalPages, totalCount, perPage } = pagination;
            const from = (page - 1) * perPage + 1;
            const to = Math.min(page * perPage, totalCount);

            var html = '<div class="d-flex justify-content-between align-items-center flex-wrap">';
            html += `<small class="text-muted">Mostrando ${from}-${to} de ${totalCount} registros</small>`;
            html += '<nav><ul class="pagination pagination-sm mb-0">';

            // Previous
            html += `<li class="page-item ${page <= 1 ? 'disabled' : ''}"><a class="page-link doc-tire-pg" href="#" data-page="${page - 1}">&laquo;</a></li>`;

            // Page numbers (max 7)
            var start = Math.max(1, page - 3);
            var end = Math.min(totalPages, page + 3);
            if (end - start < 6) {
                if (start === 1) end = Math.min(totalPages, start + 6);
                else start = Math.max(1, end - 6);
            }
            for (var i = start; i <= end; i++) {
                html += `<li class="page-item ${i === page ? 'active' : ''}"><a class="page-link doc-tire-pg" href="#" data-page="${i}">${i}</a></li>`;
            }

            // Next
            html += `<li class="page-item ${page >= totalPages ? 'disabled' : ''}"><a class="page-link doc-tire-pg" href="#" data-page="${page + 1}">&raquo;</a></li>`;

            html += '</ul></nav></div>';
            container.innerHTML = html;

            // Bind click events
            container.querySelectorAll('.doc-tire-pg').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    var p = parseInt(this.dataset.page, 10);
                    if (!isNaN(p) && p >= 1 && p <= totalPages) {
                        window.DocTireIndexPage.currentFilter.page = p;
                        window.DocTireIndexPage.loadList();
                    }
                });
            });
        },

        renderRow(row) {
            const columns = (this.config.listColumns || []).map((column) => `<td>${this.formatValue(row[column.field], column)}</td>`).join('');
            const docentry = Number(row.docentry || 0);
            const status = row.status || '';
            const canceled = row.canceled || 'N';
            const isClosedOrCanceled = status === 'CLOSE' || canceled === 'Y';

            // Botón visible siempre: vista rápida
            var visibleBtns = '';
            visibleBtns += button('btn-outline-secondary', 'fa-eye', '', 'quick-view', docentry);

            // Botones en dropdown
            var dropdownItems = [
                { icon: 'fa-pen-to-square', label: 'Editar', action: 'edit' },
                { icon: 'fa-print', label: 'Preview', action: 'preview' },
                { icon: 'fa-file-pdf', label: 'PDF', action: 'pdf' },
                { icon: 'fa-paper-plane', label: 'Correo', action: 'send-mail' },
            ];

            // Cerrar (solo si no está cerrado/cancelado)
            if (!isClosedOrCanceled) {
                dropdownItems.push({ icon: 'fa-lock', label: 'Cerrar', action: 'close' });
            }
            // Cancelar (solo si no está cerrado/cancelado)
            if (!isClosedOrCanceled) {
                dropdownItems.push({ icon: 'fa-ban', label: 'Cancelar', action: 'cancel' });
            }

            var dropdownHtml = dropdownItems.map(function (item) {
                return '<a class="dropdown-item doc-tire-action" href="#" data-action="' + item.action + '" data-docentry="' + docentry + '">' +
                    '<i class="fa-solid ' + item.icon + ' me-2"></i>' + item.label +
                    '</a>';
            }).join('');

            var actions = '<div class="d-flex flex-wrap gap-1 justify-content-center align-items-center m-0 p-0">' +
                visibleBtns +
                '<div class="dropdown d-inline-block">' +
                '<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">' +
                '<i class="fa-solid fa-ellipsis-vertical"></i>' +
                '</button>' +
                '<ul class="dropdown-menu dropdown-menu-end">' + dropdownHtml + '</ul>' +
                '</div>' +
                '</div>';

            return `<tr>${columns}<td class="text-center m-0 p-0">${actions}</td></tr>`;
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
                        : rendered === 'Planeado'
                            ? 'info'
                            : rendered === 'Ejecutado'
                                ? 'primary'
                                : rendered === 'Validado'
                                    ? 'warning text-dark'
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
                this.currentFilter.page = 1;
                this.loadList();
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        },
    };
})();