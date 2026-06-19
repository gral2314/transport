<?php

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Catálogo de Estados',
    'description' => 'Estados por país para direcciones de socios de negocio.',
    'endpoints' => [
        'list' => ['states/list'],
        'save' => ['states/save'],
        'delete' => ['states/delete'],
        'formOptions' => ['states/get-form-options'],
    ],
    'table' => [
        'id' => 'tbl-states',
        'varName' => 'tbl_states',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '120px'],
            ['data' => 'name', 'title' => 'Estado', 'className' => 'text-start'],
            ['data' => 'country', 'title' => 'País', 'className' => 'text-center', 'width' => '120px'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-states',
        'formId' => 'frm-states',
        'size' => 'md',
        'title' => 'Estado',
        'titleIcon' => 'fa-solid fa-map-location-dot',
        'fields' => [
            ['name' => 'code', 'type' => 'text', 'label' => 'Código','icon' => 'ti ti-qrcode', 'required' => true, 'maxlength' => 50, 'col' => 'col-md-6', 'placeholder' => 'Ej: CDMX, QUE, MEX',],
            ['name' => 'country', 'type' => 'select', 'label' => 'País', 'required' => true, 'col' => 'col-md-6', 'options' => [], 'optionsFrom' => 'countries', 'optionValueField' => 'code', 'optionLabelField' => 'name'],
            ['name' => 'name', 'type' => 'text', 'label' => 'Nombre','icon' => 'ti ti-world', 'required' => true, 'maxlength' => 200, 'col' => 'col-md-12', 'placeholder' => 'Ej: Estado de México, Ciudad de México'],
            ['name' => 'active', 'type' => 'switch', 'label' => 'Activo', 'checked' => true, 'color' => 'warning', 'col' => 'col-12'],
        ],
        'validations' => [
            'code' => ['required' => true, 'maxLength' => 10],
            'name' => ['required' => true, 'maxLength' => 100],
            'country' => ['required' => true],
        ],
    ],
    'addButton' => true,
    'addButtonText' => 'Agregar Estado',
    'addButtonIcon' => 'ti ti-circle-plus',
        
]);
