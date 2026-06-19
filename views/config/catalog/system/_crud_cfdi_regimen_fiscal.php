<?php

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Régimen Fiscal CFDI',
    'description' => 'Catálogo de regímenes fiscales para socios de negocio.',
    'endpoints' => [
        'list' => ['cfdi-regimen-fiscal/list'],
        'save' => ['cfdi-regimen-fiscal/save'],
        'delete' => ['cfdi-regimen-fiscal/delete'],
    ],
    'table' => [
        'id' => 'tbl-cfdi-regimen',
        'varName' => 'tbl_cfdi_regimen',
        'pkField' => 'code',
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '140px'],
            ['data' => 'name', 'title' => 'Descripción', 'className' => 'text-start'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
    ],
    'form' => [
        'modalId' => 'mdl-cfdi-regimen',
        'formId' => 'frm-cfdi-regimen',
        'size' => 'md',
        'title' => 'Régimen Fiscal CFDI',
        'titleIcon' => 'fa-solid fa-receipt',
        'fields' => [
            ['name' => 'code', 'type' => 'text', 'label' => 'Código', 'required' => true, 'maxlength' => 50, 'col' => 'col-md-4'],
            ['name' => 'name', 'type' => 'text', 'label' => 'Nombre', 'required' => true, 'maxlength' => 250, 'col' => 'col-md-8'],
            ['name' => 'active', 'type' => 'switch', 'label' => 'Activo', 'checked' => true, 'col' => 'col-12'],
        ],
    ],
    'addButton' => true,
    'addButtonText' => 'Agregar Régimen',
]);
