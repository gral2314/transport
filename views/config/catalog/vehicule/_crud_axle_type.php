<?php
/** 
 * Vista CRUD usando CrudWidget — Tipos de Eje
 */

use app\components\widgets\crud\CrudWidget;

echo CrudWidget::widget([
    'title' => 'Tipos de Eje',
    'description' => 'Catálogo de tipos de eje con cantidad de llantas por eje.',
    
    'endpoints' => [
        'list'   => ['axle-type/list'],
        'save'   => ['axle-type/save'],
        'delete' => ['axle-type/delete'],
    ],
    
    'table' => [
        'id' => 'tbl-axle-type',
        'varName' => 'tbl_axle_type',
        'pkField' => 'code',
        'paging' => true,
        'pageLength' => 10,
        'columns' => [
            ['data' => 'code', 'title' => 'Código', 'className' => 'text-center', 'width' => '150px'],
            ['data' => 'name', 'title' => 'Nombre', 'className' => 'text-justify'],
            ['data' => 'tire_qty', 'title' => 'Cant. Llantas', 'className' => 'text-center', 'width' => '120px'],
        ],
        'includeActiveColumn' => true,
        'actions' => ['edit', 'delete'],
        'editButtonColor' => 'success',
        'deleteButtonColor' => 'danger',
        'exportButtons' => ['copy', 'excel', 'csv'],
    ],
    
    'form' => [
        'modalId' => 'mdl-axle-type',
        'formId' => 'frm-axle-type',
        'size' => 'lg',
        'title' => 'Tipo de Eje',
        'titleIcon' => 'ti ti-axle',
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
                'label' => 'Nombre del Tipo de Eje',
                'icon' => 'ti ti-tag',
                'required' => true,
                'maxlength' => 100,
                'col' => 'col-md-12',
            ],
            [
                'name' => 'tire_qty',
                'type' => 'select',
                'label' => 'Cantidad de Llantas',
                'icon' => 'ti ti-number',
                'required' => true,
                'items' => [
                    2 => '2 llantas (LS, RS)',
                    4 => '4 llantas (LI, LO, RI, RO)',
                    8 => '8 llantas - Tandem Dual (LI1-RO1, LI2-RO2)',
                    12 => '12 llantas - Tandem Triple (LI1-RO1, LI2-RO2, LI3-RO3)',
                ],
                'value' => 2,
                'prompt' => false,
                'col' => 'col-md-6',
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
            'tire_qty' => [
                'required' => true, 
                'min' => 2,
                'max' => 12,
                'custom' => [
                    'method' => 'validateTireQty',
                    'message' => 'Solo se permiten 2, 4, 8 o 12 llantas por eje'
                ]
            ],
        ],
    ],
    
    'addButton' => true,
    'addButtonText' => 'Agregar',
    'addButtonIcon' => 'ti ti-circle-plus',
]);

// Script para visualización de ejes
$this->registerJs("
// Configuración de visualización de ejes
const AxleVisualization = {
    previewImage: null,
    tireQtySelect: null,
    warningDiv: null,
    previewContainer: null,
    isAssigned: false,
    
    init: function() {
        this.tireQtySelect = document.querySelector('#frm-axle-type select[name=\"tire_qty\"]');
        
        // Crear contenedor de imagen si no existe
        if (!document.getElementById('axle-preview-container')) {
            this.createPreviewContainer();
        }
        
        this.previewImage = document.getElementById('axle-preview-image');
        this.warningDiv = document.getElementById('axle-assigned-warning');
        this.previewContainer = document.getElementById('axle-preview-container');
        
        this.bindEvents();
        
        // Actualizar preview con el valor actual del select
        if (this.tireQtySelect && this.tireQtySelect.value) {
            this.updatePreview(this.tireQtySelect.value);
        }
    },
    
    createPreviewContainer: function() {
        const tireQtyField = this.tireQtySelect?.closest('.col-md-6');
        if (!tireQtyField) return;
        
        const containerHtml = `
            <div class=\"col-md-6\">
                <label class=\"form-label\">Vista previa del eje</label>
                <div id=\"axle-preview-container\" class=\"text-center\" style=\"position: relative; min-height: 200px;\">
                    <img id=\"axle-preview-image\" class=\"img-fluid img-thumbnail mb-3\" src=\"../images/eje_2_llantas.png\" alt=\"Vista del eje\" style=\"max-height: 200px; object-fit: contain;\">
                </div>
                <div id=\"axle-assigned-warning\" class=\"alert alert-warning\" style=\"display:none;\">
                    <i class=\"ti ti-alert-triangle\"></i> <strong>Eje asignado:</strong> No se puede cambiar la cantidad de llantas porque está asignado a uno o más tipos de vehículo.
                </div>
            </div>
        `;
        
        tireQtyField.insertAdjacentHTML('afterend', containerHtml);
    },
    
    bindEvents: function() {
        if (this.tireQtySelect) {
            this.tireQtySelect.addEventListener('change', (e) => this.updatePreview(e.target.value));
        }
    },
    
    updatePreview: function(qty) {
        qty = parseInt(qty);
        if (!this.previewImage) return;
        
        // Cambiar imagen según cantidad de llantas
        if (qty === 2) {
            this.previewImage.src = '../images/eje_2_llantas.png';
            this.previewImage.alt = 'Eje de 2 llantas';
        } else if (qty === 4) {
            this.previewImage.src = '../images/eje_4_llantas.png';
            this.previewImage.alt = 'Eje de 4 llantas';
        } else if (qty === 8) {
            this.previewImage.src = '../images/eje_8_llantas.png';
            this.previewImage.alt = 'Eje tandem dual (8 llantas)';
        } else if (qty === 12) {
            this.previewImage.src = '../images/eje_12_llantas.png';
            this.previewImage.alt = 'Eje tandem triple (12 llantas)';
        }
    },
    
    setAssignedMode: function(isAssigned) {
        this.isAssigned = isAssigned;
        
        if (isAssigned && this.tireQtySelect) {
            // Deshabilitar select
            this.tireQtySelect.setAttribute('disabled', 'disabled');
            this.tireQtySelect.style.backgroundColor = '#e9ecef';
            
            // Mostrar alerta
            if (this.warningDiv) {
                this.warningDiv.style.display = 'block';
            }
        } else {
            // Habilitar select
            if (this.tireQtySelect) {
                this.tireQtySelect.removeAttribute('disabled');
                this.tireQtySelect.style.backgroundColor = '';
            }
            
            // Ocultar alerta
            if (this.warningDiv) {
                this.warningDiv.style.display = 'none';
            }
        }
    }
};

// Inicializar al abrir modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('mdl-axle-type');
    if (modal) {
        modal.addEventListener('shown.bs.modal', function() {
            setTimeout(() => {
                AxleVisualization.init();
            }, 100);
        });
    }
});

// Hook en el evento de carga de datos para edición
if (typeof window.tbl_axle_type_editCallback === 'undefined') {
    window.tbl_axle_type_editCallback = function(data) {
        setTimeout(() => {
            AxleVisualization.init();
            
            // Esperar a que el select tenga el valor cargado
            setTimeout(() => {
                const currentValue = document.querySelector('#frm-axle-type select[name=\"tire_qty\"]')?.value;
                if (currentValue) {
                    AxleVisualization.updatePreview(currentValue);
                } else if (data.tire_qty) {
                    AxleVisualization.updatePreview(data.tire_qty);
                }
                
                if (data.is_assigned) {
                    AxleVisualization.setAssignedMode(true);
                } else {
                    AxleVisualization.setAssignedMode(false);
                }
            }, 150);
        }, 300);
    };
}
", \yii\web\View::POS_END);

