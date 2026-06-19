/**
 * Fleet Tires Manager - Gestión de Llantas de Flotilla
 * Auto-loaded by DynamicAssetBundle for TireController::actionIndex()
 * Path: web/scripts/fleet/tires/fleet-tires-manager.js
 */
(function () {
    'use strict';
    
    console.log('═══════════════════════════════════════════════════════════');
    console.log('🚀 FLEET TIRES MANAGER - INICIANDO CARGA DEL SCRIPT');
    console.log('═══════════════════════════════════════════════════════════');
    
    if (typeof jQuery === 'undefined') {
        console.error('❌ jQuery not loaded - Fleet Tires Manager disabled');
        return;
    }
    
    console.log('✅ jQuery detectado:', jQuery.fn.jquery);
    
    const $ = jQuery;

    function esc(s) { 
        return String(s || '').replace(/[&<>"]/g, function (m) { 
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]; 
        }); 
    }

    $(function () {
        const modalEl = document.getElementById('tire-modal');
        const tireModal = modalEl ? new bootstrap.Modal(modalEl) : null;
        let formOptions = {};

        // ========================================
        // CARGAR OPCIONES DE CATÁLOGOS
        // ========================================
        function loadFormOptions() {
            console.log('📥 Cargando opciones de catálogos...');
            
            $.ajax({
                url: Urlhome +'tire/get-form-options',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.Success === 'Ok' && response.Data) {
                        formOptions = response.Data;
                        console.log('✅ Opciones cargadas:', formOptions);
                        
                        // Llenar selects con opciones
                        populateSelect('tire[brand_code]', formOptions.brands || []);
                        populateSelect('tire[model_code]', formOptions.models || []);
                        populateSelect('tire[size_code]', formOptions.sizes || []);
                        populateSelect('tire[type_code]', formOptions.types || []);
                        populateSelect('tire[tread_design_code]', formOptions.tread_designs || []);
                        populateSelect('tire[usage_type_code]', formOptions.usage_types || []);
                        populateSelect('tire[country_code]', formOptions.countries || []);
                        populateSelect('tire[assigned_unit_code]', formOptions.vehicles || []);
                    } else {
                        console.error('❌ Error al cargar opciones:', response.Msg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error AJAX al cargar opciones:', error);
                }
            });
        }

        function populateSelect(fieldId, options) {
            const $select = $(`#${fieldId.replace(/\[/g, '\\[').replace(/\]/g, '\\]')}`);
            if (!$select.length) return;
            
            // Guardar valor actual
            const currentVal = $select.val();
            
            // Limpiar opciones (excepto la primera)
            $select.find('option:not(:first)').remove();
            
            // Agregar nuevas opciones
            options.forEach(opt => {
                $select.append($('<option>').val(opt.code).text(opt.name));
            });
            
            // Restaurar valor si existe
            if (currentVal) $select.val(currentVal);
        }

        // ========================================
        // LIMPIAR FORMULARIO
        // ========================================
        function clearForm() {
            console.log('🧹 Limpiando formulario...');
            
            $('#tire_tire_code_hidden').val('');
            $('#tire-form').find('input[type="text"], input[type="number"], input[type="date"], textarea').val('');
            $('#tire-form').find('select').val('').trigger('change');
            
            // Valores por defecto
            $('#tire\\[operational_status\\]').val('AV');
            $('#tire\\[location_status\\]').val('WH');
            $('#tire\\[physical_condition\\]').val('NW');
            $('#tire\\[is_final\\]').val('N');
            $('#tire\\[repair_qty\\]').val('0');
            $('#tire\\[retread_qty\\]').val('0');
            
            // Limpiar campos calculados
            $('#tire_wear_percentage').val('');
            $('#tire_wear_progress').css('width', '0%').text('0%').removeClass('bg-success bg-warning bg-danger');
            $('#tire_current_km_display').val('');
            $('#tire_traveled_km').val('');
            $('#tire_retread_display').val('');
            $('#tire_assignment_status').val('Sin Asignar');
            $('#tire_location_display').val('');
            
            // Quitar clases de validación
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            // Volver al primer tab
            $('#tire-tabs button[data-bs-target="#pill-general"]').tab('show');
            
            console.log('✅ Formulario limpiado');
        }

        // ========================================
        // ABRIR MODAL (NUEVO O EDITAR)
        // ========================================
        $('#btn-add-tire').on('click', function() {
            console.log('➕ Abriendo modal para NUEVA llanta...');
            clearForm();
            $('#tire-modal-label').html('<i class="ph ph-tire me-2"></i>Nueva Llanta');
            $('#tire\\[tire_code\\]').prop('readonly', false);
        });

        // Editar llanta (desde tabla) - Capturar clicks de CrudWidget
        $(document).on('click', '#tbl-tires .dt-btn-action[data-action="edit"]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const tireCode = $(this).data('pk');
            console.log('✏️ Abriendo modal para EDITAR llanta:', tireCode);
            
            $.ajax({
                url: Urlhome + 'tire/get',
                method: 'GET',
                data: { pk: tireCode },
                dataType: 'json',
                success: function(response) {
                    if (response.Success === 'Ok' && response.Data) {
                        clearForm();
                        fillFormWithData(response.Data);
                        $('#tire-modal-label').html('<i class="ph ph-tire me-2"></i>Editar Llanta: ' + esc(tireCode));
                        $('#tire\\[tire_code\\]').prop('readonly', true);
                        tireModal.show();
                    } else {
                        Swal.fire('Error', response.Msg || 'No se pudo cargar la llanta', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error al cargar llanta:', error);
                    Swal.fire('Error', 'Error al cargar datos de la llanta', 'error');
                }
            });
        });

        function fillFormWithData(data) {
            console.log('📝 Llenando formulario con datos:', data);
            
            // Campos principales
            $('#tire_tire_code_hidden').val(data.tire_code);
            $('#tire\\[tire_code\\]').val(data.tire_code);
            $('#tire\\[tire_name\\]').val(data.tire_name);
            $('#tire\\[object\\]').val(data.object);
            $('#tire\\[operational_status\\]').val(data.operational_status);
            $('#tire\\[location_status\\]').val(data.location_status);
            $('#tire\\[physical_condition\\]').val(data.physical_condition);
            $('#tire\\[current_km\\]').val(data.current_km);
            
            // Tab General
            $('#tire\\[brand_code\\]').val(data.brand_code);
            $('#tire\\[model_code\\]').val(data.model_code);
            $('#tire\\[size_code\\]').val(data.size_code);
            $('#tire\\[type_code\\]').val(data.type_code);
            $('#tire\\[serial_no\\]').val(data.serial_no);
            $('#tire\\[dot_code\\]').val(data.dot_code);
            $('#tire\\[manufacture_date\\]').val(data.manufacture_date);
            $('#tire\\[purchase_date\\]').val(data.purchase_date);
            $('#tire\\[purchase_price\\]').val(data.purchase_price);
            $('#tire\\[max_km\\]').val(data.max_km);
            $('#tire\\[retread_qty\\]').val(data.retread_qty || 0);
            
            // Tab Técnicos
            $('#tire\\[tire_width\\]').val(data.tire_width);
            $('#tire\\[aspect_ratio\\]').val(data.aspect_ratio);
            $('#tire\\[structure_type\\]').val(data.structure_type);
            $('#tire\\[rim_size\\]').val(data.rim_size);
            $('#tire\\[tread_design_code\\]').val(data.tread_design_code);
            $('#tire\\[country_code\\]').val(data.country_code);
            $('#tire\\[load_idx\\]').val(data.load_idx);
            $('#tire\\[max_load\\]').val(data.max_load);
            $('#tire\\[max_press\\]').val(data.max_press);
            $('#tire\\[traction_rate\\]').val(data.traction_rate);
            $('#tire\\[temp_rate\\]').val(data.temp_rate);
            $('#tire\\[usage_type_code\\]').val(data.usage_type_code);
            
            // Tab Operación
            $('#tire\\[orig_tread_depth\\]').val(data.orig_tread_depth);
            $('#tire\\[init_tread_depth\\]').val(data.init_tread_depth);
            $('#tire\\[curr_tread_depth\\]').val(data.curr_tread_depth);
            $('#tire\\[tread_wear_factor\\]').val(data.tread_wear_factor);
            $('#tire\\[init_km\\]').val(data.init_km);
            $('#tire\\[repair_qty\\]').val(data.repair_qty || 0);
            
            // Tab Asignación
            $('#tire\\[assigned_unit_code\\]').val(data.assigned_unit_code);
            $('#tire\\[is_final\\]').val(data.is_final || 'N');
            
            // Tab Notas
            $('#tire\\[notes\\]').val(data.notes);
            
            // Calcular campos derivados
            calculateWearMetrics();
        }

        // ========================================
        // CALCULAR MÉTRICAS DE DESGASTE
        // ========================================
        function calculateWearMetrics() {
            const origDepth = parseFloat($('#tire\\[orig_tread_depth\\]').val()) || 0;
            const initDepth = parseFloat($('#tire\\[init_tread_depth\\]').val()) || 0;
            const currDepth = parseFloat($('#tire\\[curr_tread_depth\\]').val()) || 0;
            const initKm = parseFloat($('#tire\\[init_km\\]').val()) || 0;
            const currentKm = parseFloat($('#tire\\[current_km\\]').val()) || 0;
            
            // Km recorridos
            const traveledKm = currentKm - initKm;
            $('#tire_traveled_km').val(traveledKm > 0 ? traveledKm.toFixed(2) : '-');
            
            // Factor de desgaste
            if (traveledKm > 0 && initDepth > 0 && currDepth >= 0) {
                const wearFactor = (initDepth - currDepth) / traveledKm;
                $('#tire\\[tread_wear_factor\\]').val(wearFactor.toFixed(4));
            }
            
            // Porcentaje de desgaste
            if (initDepth > 0) {
                const wearPercent = ((initDepth - currDepth) / initDepth) * 100;
                $('#tire_wear_percentage').val(wearPercent.toFixed(2));
                
                // Actualizar barra de progreso
                const $progress = $('#tire_wear_progress');
                $progress.css('width', wearPercent + '%');
                $progress.text(wearPercent.toFixed(1) + '%');
                
                // Color según desgaste
                $progress.removeClass('bg-success bg-warning bg-danger');
                if (wearPercent < 50) {
                    $progress.addClass('bg-success');
                } else if (wearPercent < 80) {
                    $progress.addClass('bg-warning');
                } else {
                    $progress.addClass('bg-danger');
                }
            }
            
            // Mostrar Km actuales
            $('#tire_current_km_display').val(currentKm > 0 ? currentKm.toFixed(2) : '-');
            
            // Mostrar reencauches
            const retreadQty = parseInt($('#tire\\[retread_qty\\]').val()) || 0;
            $('#tire_retread_display').val(retreadQty);
        }

        // Auto-calcular al cambiar valores
        $('#tire\\[orig_tread_depth\\], #tire\\[init_tread_depth\\], #tire\\[curr_tread_depth\\], #tire\\[init_km\\], #tire\\[current_km\\]')
            .on('input change', calculateWearMetrics);

        // Botón copiar profundidad original a inicial
        $('#btn-copy-orig-to-init').on('click', function() {
            const origDepth = $('#tire\\[orig_tread_depth\\]').val();
            $('#tire\\[init_tread_depth\\]').val(origDepth);
            calculateWearMetrics();
        });

        // Botón calcular desgaste
        $('#btn-calc-wear').on('click', function() {
            calculateWearMetrics();
            Swal.fire({
                icon: 'info',
                title: 'Métricas Calculadas',
                text: 'Se han actualizado las métricas de desgaste',
                timer: 2000
            });
        });

        // ========================================
        // GUARDAR LLANTA
        // ========================================
        $('#tire-form').on('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }
            
            const formData = $(this).serializeArray();
            const data = {};
            
            formData.forEach(item => {
                // Convertir tire[field] a objeto anidado
                const match = item.name.match(/tire\[([^\]]+)\]/);
                if (match) {
                    data[match[1]] = item.value;
                }
            });
            
            console.log('💾 Guardando llanta:', data);
            
            // Obtener token CSRF
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            
            $.ajax({
                url: Urlhome +'tire/save',
                method: 'POST',
                data: { 
                    tire: data,
                    [$("meta[name='csrf-param']").attr('content')]: csrfToken
                },
                dataType: 'json',
                success: function(response) {
                    if (response.Success === 'Ok') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.Msg || 'Llanta guardada correctamente',
                            timer: 2000
                        });
                        
                        tireModal.hide();
                        
                        // Recargar tabla
                        if (typeof tbl_tires !== 'undefined' && tbl_tires.ajax) {
                            tbl_tires.ajax.reload();
                        }
                    } else {
                        Swal.fire('Error', response.Msg || 'Error al guardar', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error al guardar:', error);
                    Swal.fire('Error', 'Error al guardar la llanta', 'error');
                }
            });
        });

        // ========================================
        // INICIALIZACIÓN
        // ========================================
        console.log('🔧 Inicializando Fleet Tires Manager...');
        loadFormOptions();
        
        console.log('✅ Fleet Tires Manager cargado correctamente');
    });
})();
