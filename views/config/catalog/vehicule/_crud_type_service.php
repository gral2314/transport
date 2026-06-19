<?php
/** 
 * Vista CRUD usando CrudWidget — Tipos de Servicio
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Tipos de Servicio',
    'description' => 'Catálogo de tipos de servicio de transporte (local, foráneo, internacional, etc.).',
    
    'endpoints' => [
        'list'   => ['service-type/list'],
        'save'   => ['service-type/save'],
        'delete' => ['service-type/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-type-services',
        'varName' => 'tbl_type_services',
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
        'modalId' => 'mdl-type-services',
        'formId' => 'frm-type-services',
        'size' => 'md',
        'title' => 'Tipo de Servicio',
        'titleIcon' => 'fa-solid fa-truck-ramp-box',
        'fields' => [
            [
                'name' => 'code',
                'type' => 'text',
                'label' => 'Código',
                'icon' => 'fa-solid fa-qrcode',
                'placeholder' => 'Ej. LOC',
                'required' => true,
                'maxlength' => 50,
                'col' => 'col-md-4',
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Nombre',
                'icon' => 'fa-solid fa-tag',
                'placeholder' => 'Nombre del tipo de servicio',
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
