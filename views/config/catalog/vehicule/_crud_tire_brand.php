<?php
/** 
 * Vista CRUD usando CrudWidget — Marcas de Llantas
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Marcas de Llantas',
    'description' => 'Catálogo de marcas de llantas disponibles para registro de inventario.',
    
    'endpoints' => [
        'list'   => ['tire-brand/list'],
        'save'   => ['tire-brand/save'],
        'delete' => ['tire-brand/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-tire-brand',
        'varName' => 'tbl_tire_brand',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
            ['data' => 'name', 'title' => 'Nombre', 'className' => 'text-justify'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-tire-brand',
        'formId' => 'frm-tire-brand',
        'size' => 'md',
        'title' => 'Marca de Llanta',
        'titleIcon' => 'ti ti-tag',
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
                'label' => 'Nombre',
                'icon' => 'ti ti-tag',
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
