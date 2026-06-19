<?php

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Métodos de Pago',
    'description' => 'Métodos de pago permitidos para operaciones.',
    'endpoints' => [
        'list' => ['payment-methods/list'],
        'save' => ['payment-methods/save'],
        'delete' => ['payment-methods/delete'],
    ],
    'table' => [
        'id' => 'tbl-payment-methods',
        'varName' => 'tbl_payment_methods',
        'pkField' => 'code',
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '140px'],
            ['data' => 'name', 'title' => 'Nombre', 'className' => 'text-start'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
    ],
    'form' => [
        'modalId' => 'mdl-payment-methods',
        'formId' => 'frm-payment-methods',
        'size' => 'md',
        'title' => 'Método de Pago',
        'titleIcon' => 'fa-solid fa-credit-card',
        'fields' => [
            ['name' => 'code', 'type' => 'text', 'label' => 'Código', 'required' => true, 'maxlength' => 15, 'col' => 'col-md-4'],
            ['name' => 'name', 'type' => 'text', 'label' => 'Nombre', 'required' => true, 'maxlength' => 250, 'col' => 'col-md-8'],
            ['name' => 'active', 'type' => 'switch', 'label' => 'Activo', 'checked' => true, 'col' => 'col-12'],
        ],
    ],
    'addButton' => true,
    'addButtonText' => 'Agregar Método',
]);
