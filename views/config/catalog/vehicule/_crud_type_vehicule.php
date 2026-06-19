<?php
/** 
 * Vista CRUD usando CrudWidget — Tipos de Vehículos
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Tipos de Vehículos',
    'description' => 'Catálogo de tipos de vehículos disponibles en el sistema.',
    
    'endpoints' => [
        'list'   => ['vehicle-type/list'],
        'save'   => ['vehicle-type/save'],
        'delete' => ['vehicle-type/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-type-vehicule',
        'varName' => 'tbl_type_vehicule',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width'=>'10%'],
            ['data' => 'name', 'title' => 'Nombre', 'className' => 'text-start ', 'width'=>'60%'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' =>'success',
        'deleteButtonColor' =>'danger',
    ],
    
    'form' => [
        'modalId' => 'mdl-type-vehicule',
        'formId' => 'frm-type-vehicule',
        'size' => 'md',
        'title' => 'Tipo de Vehículo',
        'titleIcon' => 'fa-solid fa-truck',
        'fields' => [
            [
                'name' => 'code',
                'type' => 'text',
                'label' => 'Código',
                'icon' => 'fa-solid fa-qrcode',
                'placeholder' => 'Ej. CAM',
                'required' => true,
                'maxlength' => 50,
                'col' => 'col-md-12',
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Nombre',
                'icon' => 'fa-solid fa-tag',
                'placeholder' => 'Nombre del tipo de vehículo',
                'required' => true,
                'maxlength' => 100,
                'col' => 'col-md-12',
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
