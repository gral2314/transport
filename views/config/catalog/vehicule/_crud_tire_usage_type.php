<?php
/** 
 * Vista CRUD usando CrudWidget — Tipos de Uso de Llanta
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Tipos de Uso de Llanta',
    'description' => 'Catálogo de tipos de uso para clasificación operacional de llantas (Dirección, Tracción, Remolque, Refacción, Mixto).',
    
    'endpoints' => [
        'list'   => ['tire-usage-type/list'],
        'save'   => ['tire-usage-type/save'],
        'delete' => ['tire-usage-type/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-tire-usage-type',
        'varName' => 'tbl_tire_usage_type',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
            ['data' => 'name', 'title' => 'Tipo de Uso', 'className' => 'text-justify'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-tire-usage-type',
        'formId' => 'frm-tire-usage-type',
        'size' => 'md',
        'title' => 'Tipo de Uso de Llanta',
        'titleIcon' => 'ti ti-steering-wheel',
        'fields' => [
            [
                'name' => 'code',
                'type' => 'text',
                'label' => 'Código',
                'icon' => 'ti ti-qrcode',
                'required' => true,
                'maxlength' => 20,
                'col' => 'col-md-12',
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Tipo de Uso',
                'icon' => 'ti ti-category',
                'required' => true,
                'maxlength' => 100,
                'col' => 'col-md-12',
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
            'code' => ['required' => true, 'maxLength' => 20],
            'name' => ['required' => true, 'maxLength' => 100],
        ],
    ],
    
    'addButton' => true,
    'addButtonText' => 'Agregar',
    'addButtonIcon' => 'ti ti-circle-plus',
]);
