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

$this->title = 'Configuración';

ob_start();
	$rows = [];
?>
<div class="col-12 row">
    <div class="card col-12">
        <div class="card-body p-1">
            <h5 class="card-title">Bases de datos de SAP</h5>
            <p class="card-text">
                <div>
                    <?= \app\components\widgets\ActionButtonWidget::widget([
                        'label'=>'Agregar Nueva Base',
                        'icon'=>'fa-solid fa-database',
                        'color'=>'success',
                        'modalTarget'=>'#mdl-new-db', 
                        'extraClass'=>'m-2'
                        ]) ?>
                </div>
                <div>
                <?php 
                    echo \app\components\widgets\DataTableWidget::widget([
                        'tableId' => 'tbl-dbsap',
                        'varName' => 'tbl_dbsap',
                        'dataUrl' => \yii\helpers\Url::to(['company-sap/list']),
                        'columns' => [
                            ['data' => 'id', 'title' => 'ID', 'className' => 'text-center'],
                            ['data' => 'name', 'title' => 'Nombre', 'className' => 'text-center'],
                            ['data' => 'email', 'title' => 'Correo', 'className' => 'text-center'],
                            ],
                    ]);
                ?>
                </div>
        </p>
    </div>    
</div>
<?php
$dbTable = ob_get_clean();

echo \app\components\widgets\TabsWidget::widget([
    'type' => 'pills',
    'navOptions' => ['class' => 'nav nav-pills mb-3','id' => 'pills-tab',],
    'contentOptions' => ['class' => 'tab-content','id' => 'pills-tabContent',],
    'items' => [
        ['label' => 'Bases Datos','id' => 'pills-dbsap','content' => $dbTable,'active' => true,],
        ['label' => 'Catalogos','id' => 'pills-profile','content' => '<p>Contenido Profile</p>',],
        ['label' => 'Contact','id' => 'pills-contact','content' => '<p>Contenido Contact</p>',],
    ],
]);

$model = new \app\models\tables\CompanySap();
$values = $model->attributeLabels();

\app\components\widgets\ModalWidget::begin([
    'modalId' => 'mdl-new-db',
    'title' => 'Crear base de datos SAP',
    'centered' => true,
    'titleIcon' => 'fa-solid fa-database',
    //'titleClass' => 'bg-cyan-200',
    'size' => 'lg',
    'footer' => '<button type="button" class="btn btn-outline-danger d-inline-flex btn-sm" data-bs-dismiss="modal"><i class="ti ti-alert-triangle me-1"></i> Cerrar</button>
                 <button type="button" class="btn btn-outline-warning d-inline-flex btn-sm" data-bs-dismiss="modal"><i class="ti ti-thumb-up"></i> Aceptar</button>',
    //'footerClass' => 'bg-gray-100',
]);
echo '<p>Definicion de la nueva base de datos SAP.</p> <br>';
echo \app\components\widgets\TextInputWidget::widget(['name' => "id",'label' => "demo_input",'labelPosition' => 'none','size' => 'sm','placeholder' => 'Placeholder','hidden' => true,]);?>

<div class="row col-12 mb-3">
    <div class="col-md-6">
        <?php 
        echo \app\components\widgets\TextInputWidget::widget([
                                    'name' => "code",
                                    'label' => "Código",
                                    'labelPosition' => 'icon',
                                    'labelIcon' => 'fa-solid fa-qrcode',
                                    'size' => 'sm',
                                    'placeholder' => 'Nombre de la base de datos',
                                    //'required' => false,
                                    //'invalidFeedback' => 'Campo requerido',
                                    //'options' => ['id' => "demo-input-{$i}"]
                                ]);

        ?>
    </div>
    <div class="col-md-6">
        <?php 
        echo \app\components\widgets\TextInputWidget::widget([
                                    'name' => "name",
                                    'label' => "Nombre",
                                    'labelPosition' => 'icon',
                                    'labelIcon' => 'fa-solid fa-t',
                                    'size' => 'sm',
                                    'placeholder' => 'Razon Social',
                                    //'required' => false,
                                    //'invalidFeedback' => 'Campo requerido',
                                    //'options' => ['id' => "demo-input-{$i}"]
                                ]);

        ?>
    </div>
</div>
<div class="row col-12 mb-3">
    <div class="col-md-6">
        <?php 
        echo \app\components\widgets\TextInputWidget::widget([
                                    'name' => "sap_user",
                                    'label' => "Código",
                                    'labelPosition' => 'icon',
                                    'labelIcon' => 'fa-solid fa-user',
                                    'size' => 'sm',
                                    'placeholder' => 'Usuario SAP',
                                    //'required' => false,
                                    //'invalidFeedback' => 'Campo requerido',
                                    //'options' => ['id' => "demo-input-{$i}"]
                                ]);

        ?>
    </div>
    <div class="col-md-6">
        <?php 
        echo \app\components\widgets\TextInputWidget::widget([
                                    'name' => "password",
                                    'label' => "Nombre",
                                    'labelPosition' => 'icon',
                                    'labelIcon' => 'fa-solid fa-key',
                                    'size' => 'sm',
                                    'placeholder' => 'Contraseña SAP',
                                    //'required' => false,
                                    //'invalidFeedback' => 'Campo requerido',
                                    //'options' => ['id' => "demo-input-{$i}"]
                                ]);

        ?>
    </div>
</div>
<div class="row ">
    <div class="d-flex align-items-end justify-content-end col-12">
        <?php 
            echo \app\components\widgets\BooleanSwitchWidget::widget([
                'name' => 'active',
                'attribute' => 'active',
                'options' => ['id' => 'active'],
                'label' => 'prymary',
            ]);
        ?>
    </div>
</div>

<?php 
\app\components\widgets\ModalWidget::end();

?>