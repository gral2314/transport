<?php
/** 
 * Vista CRUD usando CrudWidget — Catálogo de Tipos de Empleado
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Catálogo de Tipos de Empleado',
    'description' => 'Administración de clasificación de empleados (eventual, planta, temporal, etc.).',
    
    'endpoints' => [
        'list'   => ['employee-type/list'],
        'save'   => ['employee-type/save'],
        'delete' => ['employee-type/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-employee-type',
        'varName' => 'tbl_employee_type',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
            ['data' => 'name', 'title' => 'Tipo de Empleado', 'className' => 'text-justify'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-employee-type',
        'formId' => 'frm-employee-type',
        'size' => 'md',
        'title' => 'Tipo de Empleado',
        'titleIcon' => 'fa-solid fa-user-tag',
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
                'icon' => 'fa-solid fa-user-tag',
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
    'addButtonText' => 'Agregar Tipo',
    'addButtonIcon' => 'ti ti-circle-plus',
]);
