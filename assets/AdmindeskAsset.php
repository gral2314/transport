<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Admindesk main asset bundle.
 * Publishes the template's CSS/JS from `admindesk/assets`.
 */
class AdmindeskAsset extends AssetBundle
{
    public $sourcePath = '@app/admindesk/assets';

    public $css = [
        'css/style-preset.css',
        'css/style.css',
        'css/plugins/phosphor-icons.css',
        'css/plugins/tabler-icons.min.css',
        'css/plugins/swiper-bundle.css',
        'css/plugins/filepond.min.css',
        'css/plugins/quill.snow.css',
        // plugins
        'css/plugins/flatpickr.min.css',
        'css/plugins/introjs.min.css',
        // CDN fallbacks for plugins not bundled with the template
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
        'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css',
        // Font Awesome (some templates/widgets use 'fas' classes)
        
    ];

    public $js = [
        // CRÍTICO: jQuery PRIMERO - todos los demás plugins dependen de él
        'js/plugins/jquery.min.js',
        'js/plugins/popper.min.js',
        'js/plugins/bootstrap.min.js',
        'js/plugins/simplebar.min.js',
        // DataTables core y extensiones - DESPUÉS de jQuery
        'js/plugins/dataTables.min.js',
        'js/plugins/dataTables.bootstrap5.min.js',
        'js/plugins/dataTables.buttons.min.js',
        'js/plugins/buttons.bootstrap5.min.js',
        'js/plugins/buttons.html5.min.js',
        'js/plugins/buttons.print.min.js',
        'js/plugins/jszip.min.js',
        'js/plugins/pdfmake.min.js',
        'js/plugins/vfs_fonts.js',
        // Otros plugins
        'js/plugins/flatpickr.min.js',
        'js/plugins/intro.min.js',
        // CDN fallbacks when not present locally
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js',
        // Template scripts - AL FINAL
        'js/script.js',
        'js/theme.js',
        //'js/layout-vertical.js',
        'js/plugins/sweetalert2.all.min.js',

    ];

    public $jsOptions = [
        'position' => View::POS_END,
    ];
    
    /**
     * NO depender de nada - este es el bundle base con jQuery
     */
    public $depends = [];
}
