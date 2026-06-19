<?php

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Uso de CFDI',
    'description' => 'Catálogo de usos de CFDI para socios de negocio.',
    'endpoints' => [
        'list' => ['cfdi-use-sn/list'],
        'save' => ['cfdi-use-sn/save'],
        'delete' => ['cfdi-use-sn/delete'],
    ],
    'table' => [
        'id' => 'tbl-cfdi-use-sn',
        'varName' => 'tbl_cfdi_use_sn',
        'pkField' => 'code',
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '140px'],
            ['data' => 'name', 'title' => 'Descripción', 'className' => 'text-start'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
    ],
    'form' => [
        'modalId' => 'mdl-cfdi-use-sn',
        'formId' => 'frm-cfdi-use-sn',
        'size' => 'md',
        'title' => 'Uso de CFDI',
        'titleIcon' => 'fa-solid fa-file-invoice',
        'fields' => [
            ['name' => 'code', 'type' => 'text', 'label' => 'Código', 'required' => true, 'maxlength' => 50, 'col' => 'col-md-4'],
            ['name' => 'name', 'type' => 'text', 'label' => 'Nombre', 'required' => true, 'maxlength' => 250, 'col' => 'col-md-8'],
            ['name' => 'active', 'type' => 'switch', 'label' => 'Activo', 'checked' => true, 'col' => 'col-12'],
        ],
    ],
    'addButton' => true,
    'addButtonText' => 'Agregar Uso CFDI',
]);
