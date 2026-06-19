/**
 * 09 — Fullscreen Toggle for Taller Visual
 * 
 * Permite maximizar/restaurar el área de trabajo del taller visual
 * dentro del navegador, sin usar la API Fullscreen del navegador.
 * 
 * Dependencias: 00doc-tire-assignment-state.js (Events)
 */

(function () {
    'use strict';

    const FULLSCREEN_CLASS = 'fullscreen-mode';
    const STORAGE_KEY = 'docTireAssignment_fullscreen';

    /** @type {HTMLElement|null} */
    let workspaceEl = null;

    /** @type {HTMLElement|null} */
    let btnToggle = null;

    // -----------------------------------------------------------------------
    // Estado interno
    // -----------------------------------------------------------------------
    let _isFullscreen = false;

    function getIsFullscreen() {
        return _isFullscreen;
    }

    // -----------------------------------------------------------------------
    // UI helpers
    // -----------------------------------------------------------------------

    /**
     * Actualiza la UI del botón según el estado actual.
     */
    function updateButtonUI() {
        if (!btnToggle) return;

        const icon = btnToggle.querySelector('i');
        if (_isFullscreen) {
            btnToggle.title = 'Restaurar área de trabajo';
            if (icon) {
                icon.className = 'fa-solid fa-compress';
            }
            btnToggle.innerHTML = '<i class="fa-solid fa-compress"></i> Restaurar';
        } else {
            btnToggle.title = 'Maximizar área de trabajo';
            if (icon) {
                icon.className = 'fa-solid fa-expand';
            }
            btnToggle.innerHTML = '<i class="fa-solid fa-expand"></i> Vista Completa';
        }
    }

    /**
     * Aplica o remueve la clase fullscreen y actualiza UI.
     */
    function applyFullscreenState(fullscreen) {
        _isFullscreen = fullscreen;

        if (!workspaceEl) return;

        if (fullscreen) {
            workspaceEl.classList.add(FULLSCREEN_CLASS);
        } else {
            workspaceEl.classList.remove(FULLSCREEN_CLASS);
        }

        updateButtonUI();

        // Emitir evento para que otros módulos reaccionen (ej. redibujar chasis)
        if (window.Events && typeof window.Events.emit === 'function') {
            window.Events.emit('fullscreen:toggle', { fullscreen });
        }

        // Guardar preferencia en sessionStorage (no persistir entre pestañas)
        try {
            if (fullscreen) {
                sessionStorage.setItem(STORAGE_KEY, '1');
            } else {
                sessionStorage.removeItem(STORAGE_KEY);
            }
        } catch (_) { /* ignore */ }
    }

    /**
     * Alterna entre fullscreen y normal.
     */
    function toggleFullscreen() {
        applyFullscreenState(!_isFullscreen);
    }

    /**
     * Restaura el estado guardado al cargar la página.
     */
    function restoreSavedState() {
        try {
            const saved = sessionStorage.getItem(STORAGE_KEY);
            if (saved === '1') {
                applyFullscreenState(true);
            }
        } catch (_) { /* ignore */ }
    }

    // -----------------------------------------------------------------------
    // Tecla Escape para salir de fullscreen
    // -----------------------------------------------------------------------
    function onKeyDown(e) {
        if (e.key === 'Escape' && _isFullscreen) {
            applyFullscreenState(false);
            e.preventDefault();
        }
    }

    // -----------------------------------------------------------------------
    // Inicialización
    // -----------------------------------------------------------------------
    function init() {
        workspaceEl = document.getElementById('visual-workspace');
        btnToggle = document.getElementById('btn-toggle-fullscreen');

        if (!workspaceEl || !btnToggle) {
            // Estos elementos son parte del taller visual; si no existen, no hay nada que hacer.
            return;
        }

        // Bind click
        btnToggle.addEventListener('click', toggleFullscreen);

        // Escape para salir
        document.addEventListener('keydown', onKeyDown);

        // Restaurar estado previo (si el usuario recarga en fullscreen)
        restoreSavedState();

        // Escuchar si el módulo de state indica un reset del documento
        if (window.Events && typeof window.Events.on === 'function') {
            window.Events.on('document:reset', function () {
                // Si se resetear el documento, salir de fullscreen
                applyFullscreenState(false);
            });
        }
    }

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
