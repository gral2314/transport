<?php
/** 
 * Vista CRUD usando CrudWidget — Tipos de Carga
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Tipos de Carga',
    'description' => 'Catálogo de tipos de carga transportable (general, refrigerada, peligrosa, etc.).',
    
    'endpoints' => [
        'list'   => ['cargo-type/list'],
        'save'   => ['cargo-type/save'],
        'delete' => ['cargo-type/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-type-loads',
        'varName' => 'tbl_type_loads',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center'],
            ['data' => 'name', 'title' => 'Nombre', 'className' => 'text-center'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
    ],
    
    'form' => [
        'modalId' => 'mdl-type-loads',
        'formId' => 'frm-type-loads',
        'size' => 'md',
        'title' => 'Tipo de Carga',
        'titleIcon' => 'fa-solid fa-boxes-stacked',
        'fields' => [
            [
                'name' => 'code',
                'type' => 'text',
                'label' => 'Código',
                'icon' => 'fa-solid fa-qrcode',
                'placeholder' => 'Ej. GEN',
                'required' => true,
                'maxlength' => 50,
                'col' => 'col-md-4',
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Nombre',
                'icon' => 'fa-solid fa-tag',
                'placeholder' => 'Nombre del tipo de carga',
                'required' => true,
                'maxlength' => 100,
                'col' => 'col-md-8',
            ],
            [
                'name' => 'active',
                'type' => 'switch',
                'label' => 'Activo',
                'checked' => true,
                'color' => 'success',
                'col' => 'col-12',
            ],
        ],
        'validations' => [
            'code' => ['required' => true, 'maxLength' => 50],
            'name' => ['required' => true, 'maxLength' => 100],
        ],
    ],
]);
