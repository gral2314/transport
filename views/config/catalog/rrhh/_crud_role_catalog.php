<?php
/** 
 * Vista CRUD usando CrudWidget — Catálogo de Roles
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Catálogo de Roles',
    'description' => 'Administración de roles asignables a empleados (operador, supervisor, etc.).',
    
    'endpoints' => [
        'list'   => ['role-catalog/list'],
        'save'   => ['role-catalog/save'],
        'delete' => ['role-catalog/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-role-catalog',
        'varName' => 'tbl_role_catalog',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
            ['data' => 'name', 'title' => 'Nombre del Rol', 'className' => 'text-justify'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-role-catalog',
        'formId' => 'frm-role-catalog',
        'size' => 'md',
        'title' => 'Rol',
        'titleIcon' => 'fa-solid fa-user-shield',
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
                'label' => 'Nombre del Rol',
                'icon' => 'fa-solid fa-user-shield',
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
    'addButtonText' => 'Agregar Rol',
    'addButtonIcon' => 'ti ti-circle-plus',
]);
