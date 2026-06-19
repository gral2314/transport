<?php
/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Catálogos RRHH - Intercompany';

// Position
ob_start();
require_once('_crud_position.php');
$content_position = ob_get_clean();

// Area
ob_start();
require_once('_crud_area.php');
$content_area = ob_get_clean();

// Branch
ob_start();
require_once('_crud_branch.php');
$content_branch = ob_get_clean();

// Employee types
ob_start();
require_once('_crud_employee_type.php');
$content_employee_type = ob_get_clean();

// Document types
ob_start();
require_once('_crud_document_type.php');
$content_document_type = ob_get_clean();

// Role catalog
ob_start();
require_once('_crud_role_catalog.php');
$content_role_catalog = ob_get_clean();

echo \app\components\widgets\TabsWidget::widget([
    'type' => 'pills',
    'orientation' => 'vertical',
    'navColClass' => 'col-md-2',
    'contentColClass' => 'col-md-10',
    'color' => 'bg-gray-300',
    'navOptions' => ['class' => 'nav nav-pills mb-3','id' => 'pills-tab',],
    'contentOptions' => ['class' => 'tab-content','id' => 'pills-tabContent',],
    'items' => [
        ['label' => 'Puestos','id' => 'pills-position','content' => $content_position,'active' => true,],
        ['label' => 'Áreas','id' => 'pills-area','content' => $content_area, ],
        ['label' => 'Sucursales','id' => 'pills-branch','content' => $content_branch, ],
        ['label' => 'Tipos de empleado','id' => 'pills-employee_type','content' => $content_employee_type, ],
        ['label' => 'Tipos de documento','id' => 'pills-document_type','content' => $content_document_type, ],
        ['label' => 'Catálogo de roles','id' => 'pills-role_catalog','content' => $content_role_catalog, ],
    ],
]);

$this->registerJs("\n    if (typeof CrudHandlers !== 'undefined') {\n        CrudHandlers.setDebug(false);\n    }\n", \yii\web\View::POS_READY);
