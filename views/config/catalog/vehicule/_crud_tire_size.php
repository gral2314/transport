<?php
/** 
 * Vista CRUD usando CrudWidget — Tamaños de Llantas
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Tamaños de Llantas',
    'description' => 'Catálogo de medidas estándar de llantas para especificación técnica.',
    
    'endpoints' => [
        'list'   => ['tire-size/list'],
        'save'   => ['tire-size/save'],
        'delete' => ['tire-size/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-tire-size',
        'varName' => 'tbl_tire_size',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
            ['data' => 'name', 'title' => 'Medida', 'className' => 'text-center'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-tire-size',
        'formId' => 'frm-tire-size',
        'size' => 'md',
        'title' => 'Tamaño de Llanta',
        'titleIcon' => 'ti ti-ruler',
        'fields' => [
            [
                'name' => 'code',
                'type' => 'text',
                'label' => 'Código',
                'icon' => 'ti ti-qrcode',
                'required' => true,
                'maxlength' => 50,
                'col' => 'col-md-12',
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Medida (Ej: 295/75R22.5)',
                'icon' => 'ti ti-ruler',
                'placeholder' => 'Ej: 295/75R22.5',
                'required' => true,
                'maxlength' => 200,
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
            'code' => ['required' => true, 'maxLength' => 50],
            'name' => ['required' => true, 'maxLength' => 200],
        ],
    ],
    
    'addButton' => true,
    'addButtonText' => 'Agregar',
    'addButtonIcon' => 'ti ti-circle-plus',
]);
