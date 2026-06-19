<?php

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Grupos de Socios de Negocio',
    'description' => 'Clasificación principal para clientes, proveedores y leads.',
    'endpoints' => [
        'list' => ['group-sn/list'],
        'save' => ['group-sn/save'],
        'delete' => ['group-sn/delete'],
    ],
    'table' => [
        'id' => 'tbl-group-sn',
        'varName' => 'tbl_group_sn',
        'pkField' => 'code',
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '120px'],
            ['data' => 'name', 'title' => 'Nombre', 'className' => 'text-start'],
            ['data' => 'cardtype', 'title' => 'Tipo de socio de negocios', 'className' => 'text-center', 'width' => '150px',
            
            'render' => 'function(d,t,r){return  (d=="C"?"Cliente":(d=="S"?"Proveedor":"Desconocido")) ;}'
            ],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
    ],
    'form' => [
        'modalId' => 'mdl-group-sn',
        'formId' => 'frm-group-sn',
        'size' => 'md',
        'title' => 'Grupo SN',
        'titleIcon' => 'fa-solid fa-people-group',
        'fields' => [
            ['name' => 'code', 'type' => 'number', 'label' => 'Código', 'required' => true, 'col' => 'col-md-4'],
            ['name' => 'name', 'type' => 'text', 'label' => 'Nombre', 'required' => true, 'maxlength' => 200, 'col' => 'col-md-8'],
            ['name' => 'cardtype', 'type' => 'select', 'required' => true, 'label' => 'Tipo de socio de negocios', 'items' => ['C' => 'Cliente', 'S' => 'Proveedor'], 'col' => 'col-md-4'],
            ['name' => 'active', 'type' => 'switch', 'label' => 'Activo', 'checked' => true, 'col' => 'col-12'],
        ],
    ],
    'addButton' => true,
    'addButtonText' => 'Agregar Grupo',
]);
