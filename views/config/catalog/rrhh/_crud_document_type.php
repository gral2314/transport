<?php
/** 
 * Vista CRUD usando CrudWidget — Catálogo de Tipos de Documento
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Catálogo de Tipos de Documento',
    'description' => 'Administración de documentos requeridos en el expediente del empleado.',
    
    'endpoints' => [
        'list'   => ['document-type/list'],
        'save'   => ['document-type/save'],
        'delete' => ['document-type/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-document-type',
        'varName' => 'tbl_document_type',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
            ['data' => 'name', 'title' => 'Tipo de Documento', 'className' => 'text-justify'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-document-type',
        'formId' => 'frm-document-type',
        'size' => 'md',
        'title' => 'Tipo de Documento',
        'titleIcon' => 'fa-solid fa-file-lines',
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
                'label' => 'Nombre del Documento',
                'icon' => 'fa-solid fa-file-lines',
                'required' => true,
                'maxlength' => 100,
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
            'name' => ['required' => true, 'maxLength' => 100],
        ],
    ],
    
    'addButton' => true,
    'addButtonText' => 'Agregar Documento',
    'addButtonIcon' => 'ti ti-circle-plus',
]);
