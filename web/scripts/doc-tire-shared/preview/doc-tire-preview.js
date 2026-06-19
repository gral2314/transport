(function () {
    if (window.DocTirePreviewPage) {
        return;
    }

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const createModal = (id) => {
        const element = document.getElementById(id);
        return element && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(element) : null;
    };

    window.DocTirePreviewPage = {
        initialized: false,
        init() {
            if (this.initialized) {
                return;
            }

            const payload = window.DocTirePreviewConfig || null;
            if (!payload || !payload.config || !payload.document?.docentry) {
                return;
            }

            this.initialized = true;
            this.routes = payload.config.routes || {};
            this.docentry = payload.document.docentry;

            document.getElementById('preview-send-mail')?.addEventListener('click', () => this.openSendMail());

            if (payload.autoPrint) {
                setTimeout(() => window.print(), 300);
            }
        },

        async openSendMail() {
            const target = document.getElementById('doc-tire-send-mail-body');
            const modal = createModal('doc-tire-send-mail-modal');
            if (!target) {
                return;
            }

            modal?.show();
            target.innerHTML = '<div class="text-center py-3 text-muted">Cargando...</div>';

            try {
                const response = await fetch(`${this.routes.sendMailBase}?docentry=${this.docentry}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                target.innerHTML = await response.text();
                const form = target.querySelector('#doc-tire-send-mail-form');
                if (!form) {
                    return;
                }

                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const formData = new FormData(form);
                    formData.append('_csrf', csrfToken());
                    try {
                        const sendResponse = await fetch(`${this.routes.sendMailBase}?docentry=${this.docentry}`, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: formData,
                        });
                        const payload = await sendResponse.json();
                        if (payload.Success !== 'Ok') {
                            throw new Error(payload.Msg || 'No se pudo enviar el correo');
                        }
                        modal?.hide();
                        Swal.fire('Correo enviado', payload.Msg || 'Operacion completada', 'success');
                    } catch (error) {
                        Swal.fire('Error', error.message, 'error');
                    }
                }, { once: true });
            } catch (error) {
                target.innerHTML = `<div class="alert alert-danger mb-0">${error.message}</div>`;
            }
        },
    };
})();