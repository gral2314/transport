<?php
/** 
 * Vista CRUD usando CrudWidget — Modelos de Llantas
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Modelos de Llantas',
    'description' => 'Catálogo de modelos de llantas por marca para gestión de inventario.',
    
    'endpoints' => [
        'list'   => ['tire-model/list'],
        'save'   => ['tire-model/save'],
        'delete' => ['tire-model/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-tire-model',
        'varName' => 'tbl_tire_model',
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
        'modalId' => 'mdl-tire-model',
        'formId' => 'frm-tire-model',
        'size' => 'md',
        'title' => 'Modelo de Llanta',
        'titleIcon' => 'ti ti-box-model',
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
                'label' => 'Nombre del Modelo',
                'icon' => 'ti ti-box-model',
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
