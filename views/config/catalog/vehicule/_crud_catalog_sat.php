<?php
/** 
 * Vista CRUD usando CrudWidget — Catálogo de Configuración Vehicular del SAT
 * 
 * EJEMPLO DE MIGRACIÓN: Esta vista reemplaza _dt_catalog_sat.php + _frm_catalog_sat.php
 * usando el nuevo CrudWidget que orquesta todo el comportamiento CRUD.
 */

use app\components\widgets\crud\CrudWidget;
use yii\helpers\Url;

echo CrudWidget::widget([
    // Títulos
    'title' => 'Catálogo de Configuracion Vehicular del SAT',
    'description' => 'Información fiscal estandarizada requerida para facturación y cumplimiento ante el SAT.',
    
    // Endpoints CRUD
    'endpoints' => [
        'list'   => ['sat-vehicle-config/list'],
        'get'    => ['sat-vehicle-config/get'],   // opcional: si null usa datos de DataTable
        'save'   => ['sat-vehicle-config/save'],
        'delete' => ['sat-vehicle-config/delete'],
    ],
    
    // Configuración del DataTable
    'table' => [
        'id' => 'tbl-cat-sat',
        'varName' => 'tbl_cat_sat',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            [
                'data' => 'code',
                'title' => 'Código',
                'className' => 'text-center',
                'width' => '10%',
            ],
            [
                'data' => 'name',
                'title' => 'Nombre',
                'className' => 'text-justify',
                'width' => '60%',
                //'render' => 'function(d,t,r){ return "<div style=\"max-width: 250px; text-align: justify; word-wrap: break-word; overflow-wrap: break-word; word-break: break-all;\">" + d + "</div>"; }',
                'render' => 'function(d,t,r){ return "<p style=\"text-wrap: auto;\">" + d + "</p>"; }',
            ],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],         // botones en columna de acciones
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    // Configuración del Modal/Formulario
    'form' => [
        'modalId' => 'mdl-cat-sat',
        'formId' => 'frm-cat-sat',
        'size' => 'lg',
        'title' => 'Configuración Vehicular SAT',
        'titleIcon' => 'fa-solid fa-file-invoice',
        'fields' => [
            [
                'name' => 'code',
                'type' => 'text',
                'label' => 'Código SAT',
                'icon' => 'fa-solid fa-qrcode',
                'placeholder' => 'Ej. C02',
                'required' => true,
                'maxlength' => 50,
                'col' => 'col-md-4',
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Descripción',
                'icon' => 'fa-solid fa-tag',
                'placeholder' => 'Descripción de la configuración',
                'required' => true,
                'maxlength' => 200,
                'col' => 'col-md-8',
            ],
            [
                'name' => 'max_ejes',
                'type' => 'number',
                'label' => 'Máx. Ejes',
                'icon' => 'fa-solid fa-circle-nodes',
                'placeholder' => '0',
                'min' => 0,
                'col' => 'col-md-4',
            ],
            [
                'name' => 'max_tires',
                'type' => 'number',
                'label' => 'Máx. Llantas',
                'icon' => 'fa-solid fa-circle',
                'placeholder' => '0',
                'min' => 0,
                'col' => 'col-md-4',
            ],
            [
                'name' => 'max_remolque',
                'type' => 'number',
                'label' => 'Máx. Remolques',
                'icon' => 'fa-solid fa-trailer',
                'placeholder' => '0',
                'min' => 0,
                'col' => 'col-md-4',
            ],
            [
                'name' => 'active',
                'type' => 'switch',
                'label' => 'Activo',
                'checked' => true,
                'color' => 'warning',
                'col' => 'col-12',
            ],
        ],
        'validations' => [
            'code' => [
                'required' => true,
                'maxLength' => 50,
            ],
            'name' => [
                'required' => true,
                'maxLength' => 200,
            ],
        ],
        'saveButtonText' => 'Guardar',
        'closeButtonText' => 'Cerrar',
    ],
    
    // Opciones UI
    'addButton' => true,
    'addButtonText' => 'Agregar',
    'addButtonIcon' => 'fa-solid fa-circle-plus',
    
    // Callbacks personalizados (opcional)
    // 'onBeforeSave' => 'function(data){ console.log("Before save:", data); return data; }',
    // 'onAfterSave' => 'function(response){ console.log("After save:", response); }',
]);
