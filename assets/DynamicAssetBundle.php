<?php

namespace app\assets;

use Yii;
use yii\web\AssetBundle;
use yii\web\AppAsset;

class DynamicAssetBundle extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [];
    //public $jsOptions = ['position' => \yii\web\View::POS_END];

    /**
     * Mapa de acciones que heredan scripts de otra acción.
     * Ej: 'update' => 'create' → si no hay scripts en controller/update/,
     *     se cargan los de controller/create/.
     */
    public static array $actionAlias = [
        'update' => 'create',
    ];

    public function init()
    {
        // Obtener el controlador y la acción actual
        $controller = Yii::$app->controller->id;
        $action = Yii::$app->controller->action->id;

        // Cargar scripts del directorio de la acción actual
        $this->loadScriptsFrom($controller, $action);

        // Si la acción tiene un alias y no es la misma, cargar también esos scripts
        if (isset(self::$actionAlias[$action])) {
            $aliasAction = self::$actionAlias[$action];
            if ($aliasAction !== $action) {
                $this->loadScriptsFrom($controller, $aliasAction);
            }
        }

        parent::init();
    }

    /**
     * Carga los archivos JS desde un directorio controller/action
     */
    private function loadScriptsFrom(string $controller, string $action): void
    {
        $scriptDirectory = Yii::getAlias('@webroot/scripts/' . $controller . '/' . $action);

        if (is_dir($scriptDirectory)) {
            $files = scandir($scriptDirectory);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'js') {
                    $filePath = $scriptDirectory . '/' . $file;
                    $timestamp = filemtime($filePath);
                    $this->js[] = 'scripts/' . $controller . '/' . $action . '/' . $file . '?v=' . $timestamp;
                }
            }
        }
    }
    public $depends = [
        AdmindeskAsset::class,
    ];

}
