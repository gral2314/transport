<?php

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Almacenes',
    'description' => 'Catálogo de almacenes para manejo de stock.',
    'endpoints' => [
        'list' => ['warehouse/list'],
        'save' => ['warehouse/save'],
        'delete' => ['warehouse/delete'],
    ],
    'table' => [
        'id' => 'tbl-warehouse',
        'varName' => 'tbl_warehouse',
        'pkField' => 'code',
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
            ['data' => 'name', 'title' => 'Nombre', 'className' => 'text-start'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
    ],
    'form' => [
        'modalId' => 'mdl-warehouse',
        'formId' => 'frm-warehouse',
        'size' => 'md',
        'title' => 'Almacén',
        'titleIcon' => 'fa-solid fa-warehouse',
        'fields' => [
            ['name' => 'code', 'type' => 'text', 'label' => 'Código', 'required' => true, 'maxlength' => 50, 'col' => 'col-md-4'],
            ['name' => 'name', 'type' => 'text', 'label' => 'Nombre', 'required' => true, 'maxlength' => 250, 'col' => 'col-md-8'],
            ['name' => 'active', 'type' => 'switch', 'label' => 'Activo', 'checked' => true, 'col' => 'col-12'],
        ],
    ],
    'addButton' => true,
    'addButtonText' => 'Agregar Almacén',
]);
