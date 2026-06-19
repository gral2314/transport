<?php

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Vendedores',
    'description' => 'Catálogo de vendedores asignables en socios de negocio.',
    'endpoints' => [
        'list' => ['vendors/list'],
        'save' => ['vendors/save'],
        'delete' => ['vendors/delete'],
    ],
    'table' => [
        'id' => 'tbl-vendors',
        'varName' => 'tbl_vendors',
        'pkField' => 'code',
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '120px'],
            ['data' => 'name', 'title' => 'Nombre', 'className' => 'text-start'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
    ],
    'form' => [
        'modalId' => 'mdl-vendors',
        'formId' => 'frm-vendors',
        'size' => 'md',
        'title' => 'Vendedor',
        'titleIcon' => 'fa-solid fa-user-tie',
        'fields' => [
            ['name' => 'code', 'type' => 'number', 'label' => 'Código', 'required' => true, 'col' => 'col-md-4'],
            ['name' => 'name', 'type' => 'text', 'label' => 'Nombre', 'required' => true, 'maxlength' => 200, 'col' => 'col-md-8'],
            ['name' => 'active', 'type' => 'switch', 'label' => 'Activo', 'checked' => true, 'col' => 'col-12'],
        ],
    ],
    'addButton' => true,
    'addButtonText' => 'Agregar Vendedor',
]);
