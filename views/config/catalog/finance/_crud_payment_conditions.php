<?php

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Condiciones de Pago',
    'description' => 'Términos comerciales para facturación y cobranza.',
    'endpoints' => [
        'list' => ['payment-conditions/list'],
        'save' => ['payment-conditions/save'],
        'delete' => ['payment-conditions/delete'],
    ],
    'table' => [
        'id' => 'tbl-payment-conditions',
        'varName' => 'tbl_payment_conditions',
        'pkField' => 'code',
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '120px'],
            ['data' => 'name', 'title' => 'Nombre', 'className' => 'text-start'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
    ],
    'form' => [
        'modalId' => 'mdl-payment-conditions',
        'formId' => 'frm-payment-conditions',
        'size' => 'md',
        'title' => 'Condición de Pago',
        'titleIcon' => 'fa-solid fa-calendar-check',
        'fields' => [
            ['name' => 'code', 'type' => 'number', 'label' => 'Código', 'required' => true, 'col' => 'col-md-4'],
            ['name' => 'name', 'type' => 'text', 'label' => 'Nombre', 'required' => true, 'maxlength' => 250, 'col' => 'col-md-8'],
            ['name' => 'active', 'type' => 'switch', 'label' => 'Activo', 'checked' => true, 'col' => 'col-12'],
        ],
    ],
    'addButton' => true,
    'addButtonText' => 'Agregar Condición',
]);
