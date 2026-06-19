<?php

declare(strict_types=1);



use app\components\widgets\crud\CrudWidget;
use app\assets\DynamicAssetBundle;
use yii\helpers\Url;
use yii\helpers\Html;

// Registrar DynamicAssetBundle para auto-cargar JS
DynamicAssetBundle::register($this);


/**
 * Partial CRUD para la gestión de Series de numeración.
 *
 * @var array $seriesEndpoints Endpoints del CRUD de series
 */

echo CrudWidget::widget([
    'title' => 'Series',
    'description' => 'Gestión de series de numeración para documentos',
    'endpoints' => $seriesEndpoints,
    'table' => [
        'id' => 'tbl-series',
        'varName' => 'tbl_Series',
        'pkField' => 'id',
        'paging' => true,
        'pageLength' => 10,
        'autoWidth' => true,
        'columns' => [
            [
                'data' => 'id',
                'title' => 'ID',
                'width' => '60px',
                'className' => 'text-center',
            ],
            [
                'data' => 'name',
                'title' => 'Nombre',
            ],
            [
                'data' => 'object_name',
                'title' => 'Objeto',
                'width' => '180px',
            ],
            [
                'data' => 'prefix',
                'title' => 'Prefijo',
                'width' => '100px',
                'className' => 'text-center',
            ],
            [
                'data' => 'suffix',
                'title' => 'Sufijo',
                'width' => '100px',
                'className' => 'text-center',
            ],
            [
                'data' => 'padding_length',
                'title' => 'Relleno',
                'width' => '80px',
                'className' => 'text-center',
            ],
            [
                'data' => 'current_consecutive',
                'title' => 'Consecutivo',
                'width' => '120px',
                'className' => 'text-center',
            ],
            [
                'data' => 'max_consecutive',
                'title' => 'Máximo',
                'width' => '100px',
                'className' => 'text-center',
            ],
            /*[
                'data' => 'is_default',
                'title' => 'Default',
                'width' => '80px',
                'className' => 'text-center',
                'render' => 'isActiveRender',
            ],*/
        ],
        'includeActiveColumn' => true,
        'activeColumnTitle' => 'Activo',
        'actions' => ['edit', 'delete'],
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    'form' => [
        'modalId' => 'mdl-series',
        'formId' => 'frm-series',
        'size' => 'md',
        'title' => 'Serie',
        'titleIcon' => 'ti ti-numbers',
        'fields' => [
            ['name' => 'id','type' => 'hidden',],
            ['name' => 'name','type' => 'text','label' => 'Nombre','placeholder' => 'Nombre descriptivo de la serie','required' => true,],
            ['name' => 'object_name','type' => 'text','label' => 'Objeto','placeholder' => 'Ej: DocTireMovement, DocTireDisposal','required' => true,'help' => 'Nombre de la clase que usará esta serie',],
            ['name' => 'prefix','type' => 'text','label' => 'Prefijo','placeholder' => 'Ej: ASG, DSP, MNT','help' => 'Prefijo del número de documento',],
            ['name' => 'suffix','type' => 'text','label' => 'Sufijo','placeholder' => 'Ej: -A, -B','help' => 'Sufijo del número de documento (opcional)',],
            ['name' => 'padding_length','type' => 'number','label' => 'Relleno','placeholder' => '6','help' => 'Longitud del relleno con ceros (ej: 6 → 000001)',],
            ['name' => 'current_consecutive','type' => 'number','label' => 'Consecutivo actual','placeholder' => '0','help' => 'Valor actual del consecutivo',],
            ['name' => 'max_consecutive','type' => 'number','label' => 'Consecutivo máximo','placeholder' => '999999','help' => 'Valor máximo del consecutivo',],
            ['name' => 'is_active','type' => 'switch','label' => 'Activo','default' => 'Y',],
            ['name' => 'is_default','type' => 'switch','label' => 'Default','default' => 'N','help' => 'Serie por defecto para este objeto. Solo una puede ser default por objeto.',],
        ],
        'validations' => [
            ['field' => 'name', 'rule' => 'required'],
            ['field' => 'object_name', 'rule' => 'required'],
        ],
    ],
    'addButton' => true,
    'addButtonText' => 'Agregar Serie',
]);
