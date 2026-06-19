<?php
/** 
 * Vista CRUD usando CrudWidget — Configuración NOM-012
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Configuración NOM-012',
    'description' => 'Catálogo de configuraciones NOM-012 para transporte de carga.',
    
    'endpoints' => [
        'list'   => ['nom012/list'],
        'save'   => ['nom012/save'],
        'delete' => ['nom012/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-nom012',
        'varName' => 'tbl_nom012',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center'],
            [
                'data' => 'name',
                'title' => 'Descripción',
                'className' => 'text-start',
                'render' => 'function(d,t,r){ return "<div style=\"max-width:250px;text-align:justify;\">" + d + "</div>"; }',
            ],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
    ],
    
    'form' => [
        'modalId' => 'mdl-nom012',
        'formId' => 'frm-nom012',
        'size' => 'md',
        'title' => 'Configuración NOM-012',
        'titleIcon' => 'fa-solid fa-certificate',
        'fields' => [
            [
                'name' => 'code',
                'type' => 'text',
                'label' => 'Código',
                'icon' => 'fa-solid fa-qrcode',
                'placeholder' => 'Ej. N01',
                'required' => true,
                'maxlength' => 50,
                'col' => 'col-md-4',
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Descripción',
                'icon' => 'fa-solid fa-tag',
                'placeholder' => 'Descripción de la configuración NOM-012',
                'required' => true,
                'maxlength' => 200,
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
            'name' => ['required' => true, 'maxLength' => 200],
        ],
    ],
]);
