<?php

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Grupos de Artículos',
    'description' => 'Clasificación de artículos para inventario.',
    'endpoints' => [
        'list' => ['group-items/list'],
        'save' => ['group-items/save'],
        'delete' => ['group-items/delete'],
    ],
    'table' => [
        'id' => 'tbl-group-items',
        'varName' => 'tbl_group_items',
        'pkField' => 'code',
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '120px'],
            ['data' => 'name', 'title' => 'Nombre', 'className' => 'text-start'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
    ],
    'form' => [
        'modalId' => 'mdl-group-items',
        'formId' => 'frm-group-items',
        'size' => 'md',
        'title' => 'Grupo de Artículos',
        'titleIcon' => 'fa-solid fa-layer-group',
        'fields' => [
            ['name' => 'code', 'type' => 'number', 'label' => 'Código', 'required' => true, 'col' => 'col-md-4'],
            ['name' => 'name', 'type' => 'text', 'label' => 'Nombre', 'required' => true, 'maxlength' => 200, 'col' => 'col-md-8'],
            ['name' => 'active', 'type' => 'switch', 'label' => 'Activo', 'checked' => true, 'col' => 'col-12'],
        ],
    ],
    'addButton' => true,
    'addButtonText' => 'Agregar Grupo',
]);
