<?php
/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\widgets\AutoFieldFactory;
use app\components\widgets\TabsWidget;
use app\components\widgets\PillsWidget;
use app\components\widgets\DataTableWidget;
use app\components\widgets\MoneyInputWidget;
use app\components\widgets\AdminLteCardWidget;
use app\components\widgets\ActionButtonWidget;
use app\components\widgets\ModalWidget;

$this->title = 'Catalogos para Vehiculos - Intercompany';

//-- Tipo de vehiculos (con configuración de ejes)
ob_start();
require_once('_crud_vehicle_type.php');
$content_type_vehicule = ob_get_clean();

//Marcas
ob_start();
require_once('_crud_brand.php');
$content_brand = ob_get_clean();

//Tipo de documentos
ob_start();
require_once('_crud_type_documents.php');
$content_type_documents = ob_get_clean();

//Nom-012
ob_start();
require_once('_crud_nom012.php');
$content_nom012 = ob_get_clean();

//Catalogo SAT
ob_start();
require_once('_crud_catalog_sat.php');
$content_catalog_sat = ob_get_clean();

//Tipos de cargas
ob_start();
require_once('_crud_type_loads.php');
$content_type_loads = ob_get_clean();

//Tipos de servicio
ob_start();
require_once('_crud_type_service.php');
$content_type_service = ob_get_clean();

//Tipo de combustible
ob_start();
require_once('_crud_fuel_type.php');
$content_fuel_type = ob_get_clean();

//Tipos de eje
ob_start();
require_once('_crud_axle_type.php');
$content_axle_type = ob_get_clean();

//Marcas de llantas
ob_start();
require_once('_crud_tire_brand.php');
$content_tire_brand = ob_get_clean();

//Modelos de llantas
ob_start();
require_once('_crud_tire_model.php');
$content_tire_model = ob_get_clean();

//Tamaños de llantas
ob_start();
require_once('_crud_tire_size.php');
$content_tire_size = ob_get_clean();

//Tipos de llantas
ob_start();
require_once('_crud_tire_type.php');
$content_tire_type = ob_get_clean();

//Tipos de uso de llantas
ob_start();
require_once('_crud_tire_usage_type.php');
$content_tire_usage_type = ob_get_clean();

//Diseños de rodada
ob_start();
require_once('_crud_tire_tread_design.php');
$content_tire_tread_design = ob_get_clean();

echo \app\components\widgets\TabsWidget::widget([
    'type' => 'pills',
    'orientation' => 'vertical',
    'navColClass' => 'col-md-2',
    'contentColClass' => 'col-md-10',
    'color' => 'bg-gray-300',
    'navOptions' => ['class' => 'nav nav-pills mb-3','id' => 'pills-tab',],
    'contentOptions' => ['class' => 'tab-content','id' => 'pills-tabContent',],
    'items' => [
        ['label' => 'Tipos de vehiculos','id' => 'pills-vehicle_type','content' => $content_type_vehicule,'active' => true,],
        ['label' => 'Marcas','id' => 'pills-vehicle_brand','content' => $content_brand, ],
        ['label' => 'Tipo de documentos','id' => 'pills-doc_type_vehicule','content' => $content_type_documents, ],
        ['label' => 'Nom-012','id' => 'pills-nom012','content' => $content_nom012, ],
        ['label' => 'Catalogo SAT','id' => 'pills-sat_vehicle_config','content' => $content_catalog_sat, ],
        ['label' => 'Tipos de cargas','id' => 'pills-cargo_type','content' => $content_type_loads, ],
        ['label' => 'Tipos de servicio','id' => 'pills-service_type','content' => $content_type_service, ],
        ['label' => 'Tipos de combustible','id' => 'pills-fuel_type','content' => $content_fuel_type, ],
        ['label' => 'Tipos de eje','id' => 'pills-axle_type','content' => $content_axle_type, ],
        ['label' => 'Marcas de llantas','id' => 'pills-tire_brand','content' => $content_tire_brand, ],
        ['label' => 'Modelos de llantas','id' => 'pills-tire_model','content' => $content_tire_model, ],
        ['label' => 'Tamaños de llantas','id' => 'pills-tire_size','content' => $content_tire_size, ],
        ['label' => 'Tipos de llantas','id' => 'pills-tire_type','content' => $content_tire_type, ],
        ['label' => 'Tipos de uso de llantas','id' => 'pills-tire_usage_type','content' => $content_tire_usage_type, ],
        ['label' => 'Diseños de rodada','id' => 'pills-tire_tread_design','content' => $content_tire_tread_design, ],
    ],
]);

// Activar debug mode para CrudHandlers (solo en desarrollo)
// Para desactivar, cambiar a: CrudHandlers.setDebug(false);
$this->registerJs("
    if (typeof CrudHandlers !== 'undefined') {
        CrudHandlers.setDebug(false);
        // console.log('🐛 CrudHandlers debug mode activated');
    }
", \yii\web\View::POS_READY);
