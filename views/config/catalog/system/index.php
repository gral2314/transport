<?php
/** @var yii\web\View $this */

$this->title = 'Catálogos Generales';

// ─── Endpoints para CRUD de Series ────────────────────────────────────────
$seriesEndpoints = [
    'list' => ['series/list'],
    'get' => ['series/get'],
    'save' => ['series/save'],
    'delete' => ['series/delete'],
    'formOptions' => ['series/get-form-options'],
];

ob_start();
require_once('_crud_country.php');
$content_country = ob_get_clean();

ob_start();
require_once('_crud_states.php');
$content_states = ob_get_clean();

ob_start();
require_once('_crud_cfdi_use_sn.php');
$content_cfdi_use = ob_get_clean();

ob_start();
require_once('_crud_cfdi_regimen_fiscal.php');
$content_cfdi_regimen = ob_get_clean();

ob_start();
require_once('_crud_series.php');
$content_series = ob_get_clean();

echo \app\components\widgets\TabsWidget::widget([
    'type' => 'pills',
    'orientation' => 'vertical',
    'navColClass' => 'col-md-2',
    'contentColClass' => 'col-md-10',
    'color' => 'bg-gray-300',
    'navOptions' => ['class' => 'nav nav-pills mb-3', 'id' => 'pills-tab-system'],
    'contentOptions' => ['class' => 'tab-content', 'id' => 'pills-tabContent-system'],
    'items' => [
        ['label' => 'Países', 'id' => 'pills-country', 'content' => $content_country, 'active' => true],
        ['label' => 'Estados', 'id' => 'pills-states', 'content' => $content_states],
        ['label' => 'Uso CFDI', 'id' => 'pills-cfdi-use-sn', 'content' => $content_cfdi_use],
        ['label' => 'Régimen CFDI', 'id' => 'pills-cfdi-regimen', 'content' => $content_cfdi_regimen],
        ['label' => 'Series', 'id' => 'pills-series', 'content' => $content_series],
    ],
]);

$this->registerJs("if (typeof CrudHandlers !== 'undefined') { CrudHandlers.setDebug(false); }", \yii\web\View::POS_READY);
