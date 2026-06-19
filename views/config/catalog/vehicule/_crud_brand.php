<?php
/** 
 * Vista CRUD usando CrudWidget — Marcas de Vehículos
 * 
 * EJEMPLO: Vista simplificada con menos campos.
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Marcas de Vehículos',
    'description' => 'Catálogo de marcas disponibles para registro de vehículos.',
    
    'endpoints' => [
        'list'   => ['vehicle-brand/list'],
        'save'   => ['vehicle-brand/save'],
        'delete' => ['vehicle-brand/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-brand',
        'varName' => 'tbl_brand',
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
        'modalId' => 'mdl-brand',
        'formId' => 'frm-brand',
        'size' => 'md',
        'title' => 'Marca de Vehículo',
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
                'col' => 'col-12',
            ],
        ],
        'validations' => [
            'code' => ['required' => true, 'maxLength' => 50],
            'name' => ['required' => true, 'maxLength' => 200],
        ],
    ],
]);
