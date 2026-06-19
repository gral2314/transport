<?php
/** @var yii\web\View $this */

$this->title = 'Catálogos de Ventas';

ob_start();
require_once('_crud_group_sn.php');
$content_group_sn = ob_get_clean();

ob_start();
require_once('_crud_vendors.php');
$content_vendors = ob_get_clean();

echo \app\components\widgets\TabsWidget::widget([
    'type' => 'pills',
    'orientation' => 'vertical',
    'navColClass' => 'col-md-2',
    'contentColClass' => 'col-md-10',
    'color' => 'bg-gray-300',
    'navOptions' => ['class' => 'nav nav-pills mb-3', 'id' => 'pills-tab-sales'],
    'contentOptions' => ['class' => 'tab-content', 'id' => 'pills-tabContent-sales'],
    'items' => [
        ['label' => 'Grupos SN', 'id' => 'pills-group-sn', 'content' => $content_group_sn, 'active' => true],
        ['label' => 'Vendedores', 'id' => 'pills-vendors', 'content' => $content_vendors],
    ],
]);

$this->registerJs("if (typeof CrudHandlers !== 'undefined') { CrudHandlers.setDebug(false); }", \yii\web\View::POS_READY);