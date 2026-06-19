<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace app\assets;

//use yii\bootstrap5\BootstrapAsset;
use yii\web\AssetBundle;
use yii\web\View;
use yii\web\YiiAsset;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        //'css/site.css',
        'plugins/fontawesome/css/all.min.css',
        'css/taller-validation.css',
        'css/table-dropdowns.css',
    ];
    public $js = [
        //'js/color-mode.js',
        'plugins/fontawesome/js/all.min.js',
    ];
    public $jsOptions = [
        'position' => View::POS_END,
    ];
    public $depends = [
        //YiiAsset::class,
        //BootstrapAsset::class,
        AdmindeskAsset::class,
    ];
}
