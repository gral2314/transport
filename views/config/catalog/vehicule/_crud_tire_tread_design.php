<?php
/** 
 * Vista CRUD usando CrudWidget — Diseños de Rodada
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Diseños de Rodada',
    'description' => 'Catálogo de diseños de rodada (patrones de banda de rodadura) disponibles para clasificación técnica de llantas.',
    
    'endpoints' => [
        'list'   => ['tire-tread-design/list'],
        'save'   => ['tire-tread-design/save'],
        'delete' => ['tire-tread-design/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-tire-tread-design',
        'varName' => 'tbl_tire_tread_design',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
            ['data' => 'name', 'title' => 'Diseño de Rodada', 'className' => 'text-justify'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-tire-tread-design',
        'formId' => 'frm-tire-tread-design',
        'size' => 'md',
        'title' => 'Diseño de Rodada',
        'titleIcon' => 'ti ti-texture',
        'fields' => [
            [
                'name' => 'code',
                'type' => 'text',
                'label' => 'Código',
                'icon' => 'ti ti-qrcode',
                'required' => true,
                'maxlength' => 20,
                'col' => 'col-md-12',
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Diseño de Rodada',
                'icon' => 'ti ti-texture',
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
            'code' => ['required' => true, 'maxLength' => 20],
            'name' => ['required' => true, 'maxLength' => 100],
        ],
    ],
    
    'addButton' => true,
    'addButtonText' => 'Agregar',
    'addButtonIcon' => 'ti ti-circle-plus',
]);
