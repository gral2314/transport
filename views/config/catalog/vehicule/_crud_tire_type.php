<?php
/** 
 * Vista CRUD usando CrudWidget — Tipos de Llanta
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Tipos de Llanta',
    'description' => 'Catálogo de tipos de llanta (Direccional, Tracción, Remolque, etc.).',
    
    'endpoints' => [
        'list'   => ['tire-type/list'],
        'save'   => ['tire-type/save'],
        'delete' => ['tire-type/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-tire-type',
        'varName' => 'tbl_tire_type',
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
        'modalId' => 'mdl-tire-type',
        'formId' => 'frm-tire-type',
        'size' => 'md',
        'title' => 'Tipo de Llanta',
        'titleIcon' => 'ti ti-wheel',
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
                'label' => 'Nombre del Tipo',
                'icon' => 'ti ti-tag',
                'placeholder' => 'Ej: Direccional, Tracción, Remolque',
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
            'code' => ['required' => true, 'maxLength' => 50],
            'name' => ['required' => true, 'maxLength' => 100],
        ],
    ],
    
    'addButton' => true,
    'addButtonText' => 'Agregar',
    'addButtonIcon' => 'ti ti-circle-plus',
]);
