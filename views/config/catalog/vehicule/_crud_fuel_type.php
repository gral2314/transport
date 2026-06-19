<?php
/** 
 * Vista CRUD usando CrudWidget — Tipos de Combustible
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Tipos de Combustible',
    'description' => 'Catálogo de tipos de combustible para registro de vehículos y cálculo de consumos.',
    
    'endpoints' => [
        'list'   => ['fuel-type/list'],
        'save'   => ['fuel-type/save'],
        'delete' => ['fuel-type/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-fuel-type',
        'varName' => 'tbl_fuel_type',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
            ['data' => 'name', 'title' => 'Tipo de Combustible', 'className' => 'text-justify'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-fuel-type',
        'formId' => 'frm-fuel-type',
        'size' => 'md',
        'title' => 'Tipo de Combustible',
        'titleIcon' => 'ti ti-gas-station',
        'fields' => [
            [
                'name' => 'code',
                'type' => 'text',
                'label' => 'Código',
                'icon' => 'ti ti-qrcode',
                'required' => true,
                'maxlength' => 50,
                'col' => 'col-md-12',
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Tipo de Combustible',
                'icon' => 'ti ti-gas-station',
                'placeholder' => 'Ej: Diésel, Gasolina, Gas Natural',
                'required' => true,
                'maxlength' => 200,
                'col' => 'col-md-12',
            ],
            [
                'name' => 'active',
                'type' => 'switch',
                'label' => 'Activo',
                'checked' => true,
                'color' => 'warning',
                'col' => 'col-12',
            ],
        ],
        'validations' => [
            'code' => ['required' => true, 'maxLength' => 50],
            'name' => ['required' => true, 'maxLength' => 200],
        ],
    ],
    
    'addButton' => true,
    'addButtonText' => 'Agregar',
    'addButtonIcon' => 'ti ti-circle-plus',
]);
