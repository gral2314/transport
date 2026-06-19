<?php
/** @var yii\web\View $this */

$this->title = 'Catálogos de Inventario';

ob_start();
require_once('_crud_group_items.php');
$content_group_items = ob_get_clean();

ob_start();
require_once('_crud_warehouse.php');
$content_warehouse = ob_get_clean();

echo \app\components\widgets\TabsWidget::widget([
    'type' => 'pills',
    'orientation' => 'vertical',
    'navColClass' => 'col-md-2',
    'contentColClass' => 'col-md-10',
    'color' => 'bg-gray-300',
    'navOptions' => ['class' => 'nav nav-pills mb-3', 'id' => 'pills-tab-inventory'],
    'contentOptions' => ['class' => 'tab-content', 'id' => 'pills-tabContent-inventory'],
    'items' => [
        ['label' => 'Grupos de Artículos', 'id' => 'pills-group-items', 'content' => $content_group_items, 'active' => true],
        ['label' => 'Almacenes', 'id' => 'pills-warehouse', 'content' => $content_warehouse],
    ],
]);

$this->registerJs("if (typeof CrudHandlers !== 'undefined') { CrudHandlers.setDebug(false); }", \yii\web\View::POS_READY);