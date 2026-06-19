<?php
/** @var yii\web\View $this */

$this->title = 'Catálogos de Finanzas';

ob_start();
require_once('_crud_currency.php');
$content_currency = ob_get_clean();

ob_start();
require_once('_crud_payment_conditions.php');
$content_payment_conditions = ob_get_clean();

ob_start();
require_once('_crud_payment_methods.php');
$content_payment_methods = ob_get_clean();

echo \app\components\widgets\TabsWidget::widget([
    'type' => 'pills',
    'orientation' => 'vertical',
    'navColClass' => 'col-md-2',
    'contentColClass' => 'col-md-10',
    'color' => 'bg-gray-300',
    'navOptions' => ['class' => 'nav nav-pills mb-3', 'id' => 'pills-tab-finance'],
    'contentOptions' => ['class' => 'tab-content', 'id' => 'pills-tabContent-finance'],
    'items' => [
        ['label' => 'Monedas', 'id' => 'pills-currency', 'content' => $content_currency, 'active' => true],
        ['label' => 'Condiciones de Pago', 'id' => 'pills-payment-conditions', 'content' => $content_payment_conditions],
        ['label' => 'Métodos de Pago', 'id' => 'pills-payment-methods', 'content' => $content_payment_methods],
    ],
]);

$this->registerJs("if (typeof CrudHandlers !== 'undefined') { CrudHandlers.setDebug(false); }", \yii\web\View::POS_READY);