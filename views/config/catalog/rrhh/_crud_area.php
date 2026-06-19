<?php
/** 
 * Vista CRUD usando CrudWidget — Catálogo de Áreas
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Catálogo de Áreas',
    'description' => 'Administración de áreas organizacionales para asignación de empleados.',
    
    'endpoints' => [
        'list'   => ['area/list'],
        'save'   => ['area/save'],
        'delete' => ['area/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-area',
        'varName' => 'tbl_area',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
            ['data' => 'name', 'title' => 'Nombre del Área', 'className' => 'text-justify'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-area',
        'formId' => 'frm-area',
        'size' => 'md',
        'title' => 'Área',
        'titleIcon' => 'fa-solid fa-building',
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
                'label' => 'Nombre del Área',
                'icon' => 'fa-solid fa-building',
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
    'addButtonText' => 'Agregar Área',
    'addButtonIcon' => 'ti ti-circle-plus',
]);
