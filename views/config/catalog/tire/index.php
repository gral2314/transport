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

$this->title = 'Catalogos de LLantas';



echo \app\components\widgets\TabsWidget::widget([
    'type' => 'pills',
    'orientation' => 'vertical',
    'navColClass' => 'col-md-2',
    'contentColClass' => 'col-md-10',
    'color' => 'bg-gray-300',
    'navOptions' => ['class' => 'nav nav-pills mb-3','id' => 'pills-tab',],
    'contentOptions' => ['class' => 'tab-content','id' => 'pills-tabContent',],
    'items' => [
        ['label' => 'Marcas de llantas','id' => 'pills-tire_brand','content' => $content_tire_brand, 'active'=>true],
        ['label' => 'Modelos de llantas','id' => 'pills-tire_model','content' => $content_tire_model, ],
        ['label' => 'Tamaños de llantas','id' => 'pills-tire_size','content' => $content_tire_size, ],
    ],
]);

// Activar debug mode para CrudHandlers (solo en desarrollo)
// Para desactivar, cambiar a: CrudHandlers.setDebug(false);
$this->registerJs("
    if (typeof CrudHandlers !== 'undefined') {
        CrudHandlers.setDebug(false);
        console.log('🐛 CrudHandlers debug mode activated');
    }
", \yii\web\View::POS_READY);
