<?php
/** 
 * Vista CRUD usando CrudWidget — Catálogo de Sucursales
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Catálogo de Sucursales',
    'description' => 'Administración de sucursales para asignación de empleados.',
    
    'endpoints' => [
        'list'   => ['branch/list'],
        'save'   => ['branch/save'],
        'delete' => ['branch/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-branch',
        'varName' => 'tbl_branch',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
            ['data' => 'name', 'title' => 'Nombre de la Sucursal', 'className' => 'text-justify'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-branch',
        'formId' => 'frm-branch',
        'size' => 'md',
        'title' => 'Sucursal',
        'titleIcon' => 'fa-solid fa-store',
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
                'label' => 'Nombre de la Sucursal',
                'icon' => 'fa-solid fa-store',
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
    'addButtonText' => 'Agregar Sucursal',
    'addButtonIcon' => 'ti ti-circle-plus',
]);
