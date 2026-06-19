<?php
/** 
 * Vista CRUD usando CrudWidget — Tipos de Documentos Vehiculares
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Tipos de Documentos Vehiculares',
    'description' => 'Catálogo de tipos de documentos requeridos para vehículos (permisos, pólizas, etc.).',
    
    'endpoints' => [
        'list'   => ['doc-type-vehicule/list'],
        'save'   => ['doc-type-vehicule/save'],
        'delete' => ['doc-type-vehicule/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-type-document',
        'varName' => 'tbl_type_document',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center'],
            ['data' => 'name', 'title' => 'Nombre', 'className' => 'text-center'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
    ],
    
    'form' => [
        'modalId' => 'mdl-type-document',
        'formId' => 'frm-type-document',
        'size' => 'lg',
        'title' => 'Tipo de Documento Vehicular',
        'titleIcon' => 'fa-solid fa-file-lines',
        'fields' => [
            [
                'name' => 'code',
                'type' => 'text',
                'label' => 'Código',
                'icon' => 'fa-solid fa-qrcode',
                'placeholder' => 'Ej. PERM',
                'required' => true,
                'maxlength' => 50,
                'col' => 'col-md-4',
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Nombre',
                'icon' => 'fa-solid fa-tag',
                'placeholder' => 'Nombre del tipo de documento',
                'required' => true,
                'maxlength' => 200,
                'col' => 'col-md-8',
            ],
            [
                'name' => 'alert_time',
                'type' => 'number',
                'label' => 'Días de Alerta',
                'icon' => 'fa-solid fa-bell',
                'placeholder' => 'Días antes de vencimiento',
                'min' => 0,
                'col' => 'col-md-6',
                'help' => 'Opcional. Días de anticipación para generar alerta.',
            ],
            [
                'name' => 'alert_repit',
                'type' => 'number',
                'label' => 'Repetición de Alerta (días)',
                'icon' => 'fa-solid fa-rotate',
                'placeholder' => 'Cada cuántos días repetir',
                'min' => 0,
                'col' => 'col-md-6',
                'help' => 'Opcional. Frecuencia de repetición de la alerta.',
            ],
            [
                'name' => 'active',
                'type' => 'switch',
                'label' => 'Activo',
                'checked' => true,
                'color' => 'success',
                'col' => 'col-12',
            ],
        ],
        'validations' => [
            'code' => ['required' => true, 'maxLength' => 50],
            'name' => ['required' => true, 'maxLength' => 200],
        ],
    ],
]);
