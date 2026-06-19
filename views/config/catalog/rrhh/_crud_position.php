<?php
/** 
 * Vista CRUD usando CrudWidget — Catálogo de Puestos
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Catálogo de Puestos',
    'description' => 'Administración de puestos disponibles para asignación de empleados.',
    
    'endpoints' => [
        'list'   => ['position/list'],
        'save'   => ['position/save'],
        'delete' => ['position/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-position',
        'varName' => 'tbl_position',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
            ['data' => 'name', 'title' => 'Nombre del Puesto', 'className' => 'text-justify'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-position',
        'formId' => 'frm-position',
        'size' => 'md',
        'title' => 'Puesto',
        'titleIcon' => 'fa-solid fa-briefcase',
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
                'label' => 'Nombre del Puesto',
                'icon' => 'fa-solid fa-briefcase',
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
    'addButtonText' => 'Agregar Puesto',
    'addButtonIcon' => 'ti ti-circle-plus',
]);
