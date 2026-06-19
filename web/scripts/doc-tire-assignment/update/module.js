/**
 * Módulo de entrada para la vista de actualización (update).
 * Los scripts de create/ se cargan automáticamente vía DynamicAssetBundle::$actionAlias.
 * El module.js de create/ ya se auto-ejecuta y detecta el modo edición por
 * formConfig.isNewRecord === false, haciendo la carga AJAX correspondiente.
 * 
 * Este archivo solo sirve como puente por si el orden de carga requiere
 * verificar que DocTireAssignment esté disponible.
 */
(function () {
    function checkReady() {
        if (window.DocTireAssignment) {
            console.log('[update/module.js] DocTireAssignment disponible (inicializado por create/module.js).');
            return;
        }
        setTimeout(checkReady, 100);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkReady);
    } else {
        checkReady();
    }
})();