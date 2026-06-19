<?php

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Monedas',
    'description' => 'Catálogo de monedas para operaciones comerciales.',
    'endpoints' => [
        'list' => ['currency/list'],
        'save' => ['currency/save'],
        'delete' => ['currency/delete'],
    ],
    'table' => [
        'id' => 'tbl-currency',
        'varName' => 'tbl_currency',
        'pkField' => 'code',
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '100px'],
            ['data' => 'name', 'title' => 'Moneda', 'className' => 'text-start'],
            ['data' => 'symbol', 'title' => 'Símbolo', 'className' => 'text-center', 'width' => '120px'],
            ['data' => 'decimals', 'title' => 'Dec.', 'className' => 'text-center', 'width' => '90px'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
    ],
    'form' => [
        'modalId' => 'mdl-currency',
        'formId' => 'frm-currency',
        'size' => 'lg',
        'title' => 'Moneda',
        'titleIcon' => 'fa-solid fa-money-bill-wave',
        'fields' => [
            ['name' => 'code', 'type' => 'text', 'label' => 'Código', 'required' => true, 'maxlength' => 3, 'col' => 'col-md-3'],
            ['name' => 'name', 'type' => 'text', 'label' => 'Nombre', 'required' => true, 'maxlength' => 250, 'col' => 'col-md-6'],
            ['name' => 'symbol', 'type' => 'text', 'label' => 'Símbolo', 'required' => true, 'maxlength' => 10, 'col' => 'col-md-3'],
            ['name' => 'decimals', 'type' => 'number', 'label' => 'Decimales', 'required' => true, 'col' => 'col-md-3'],
            ['name' => 'txt_singular', 'type' => 'text', 'label' => 'Texto singular', 'required' => true, 'maxlength' => 250, 'col' => 'col-md-4'],
            ['name' => 'txt_plural', 'type' => 'text', 'label' => 'Texto plural', 'required' => true, 'maxlength' => 250, 'col' => 'col-md-5'],
            ['name' => 'active', 'type' => 'switch', 'label' => 'Activo', 'checked' => true, 'col' => 'col-12'],
        ],
    ],
    'addButton' => true,
    'addButtonText' => 'Agregar Moneda',
]);
