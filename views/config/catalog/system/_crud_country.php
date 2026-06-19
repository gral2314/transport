<?php
/** 
 * Vista CRUD usando CrudWidget — Países
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Países',
    'description' => 'Catálogo de países según estándar ISO 3166-1 alpha-2. Utilizado para clasificación de fabricantes, proveedores y ubicaciones geográficas.',
    
    'endpoints' => [
        'list'   => ['country/list'],
        'save'   => ['country/save'],
        'delete' => ['country/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-country',
        'varName' => 'tbl_country',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código ISO', 'className' => 'text-center', 'width' => '120px'],
            ['data' => 'name', 'title' => 'País', 'className' => 'text-justify'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-country',
        'formId' => 'frm-country',
        'size' => 'md',
        'title' => 'País',
        'titleIcon' => 'ti ti-world',
        'fields' => [
            [
                'name' => 'code',
                'type' => 'text',
                'label' => 'Código ISO (ISO 3166-1 alpha-2)',
                'icon' => 'ti ti-qrcode',
                'required' => true,
                'maxlength' => 10,
                'col' => 'col-md-12',
                'placeholder' => 'Ej: MX, US, CA',
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Nombre del País',
                'icon' => 'ti ti-world',
                'required' => true,
                'maxlength' => 100,
                'col' => 'col-md-12',
                'placeholder' => 'Ej: México, Estados Unidos',
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
            'code' => ['required' => true, 'maxLength' => 10],
            'name' => ['required' => true, 'maxLength' => 100],
        ],
    ],
    
    'addButton' => true,
    'addButtonText' => 'Agregar',
    'addButtonIcon' => 'ti ti-circle-plus',
]);
