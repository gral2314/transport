/**
 * Fleet Units Manager - Gestión de Unidades de Flotilla
 * Auto-loaded by DynamicAssetBundle for FleetController::actionUnits()
 * Path: web/scripts/fleet/units/fleet-units-manager.js
 */
(function () {
    'use strict';
    
    console.log('═══════════════════════════════════════════════════════════');
    console.log('🚀 FLEET UNITS MANAGER - INICIANDO CARGA DEL SCRIPT');
    console.log('═══════════════════════════════════════════════════════════');
    
    if (typeof jQuery === 'undefined') {
        console.error('❌ jQuery not loaded - Fleet Units Manager disabled');
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
        const modalEl = document.getElementById('unit-modal');
        const unitModal = modalEl ? new bootstrap.Modal(modalEl) : null;

        function clearForm() {
            console.log('🧹 Limpiando formulario completo...');
            
            // Limpiar campo de código oculto
            $('#vehicle_vehicle_code').val('');
            
            // Limpiar TODOS los inputs, selects y textareas del formulario
            $('#unit-modal').find('input[type="text"], input[type="number"], input[type="date"], textarea').val('');
            // ✅ CAMBIO: NO resetear selects a 0, solo limpiar la selección actual
            $('#unit-modal').find('select').val('').trigger('change');
            
            // Resetear switches/checkboxes a valores por defecto
            getById('vehicle[active]').val('Y');
            getById('vehicle[available]').val('Y');
            getById('vehicle[status]').val('A');
            getById('vehicle[acquisition]').val('P');
            
            // Limpiar tabla de documentos
            $('#docs-table tbody').html('<tr class="no-records"><td colspan="8" class="text-center text-muted">No hay documentos registrados</td></tr>');
            
            // Limpiar tabla de llantas
            $('#tires-table tbody').html('<tr class="no-tires-msg"><td colspan="7" class="text-center text-muted">No hay llantas instaladas</td></tr>');
            
            // Resetear imagen de llantas a placeholder genérico
            const cfg = window.fleetUnitsConfig || {};
            const imagesPath = cfg.imagesPath || '/web/images';
            $('#tire-config-image').attr('src', imagesPath + '/placeholder-tire.svg').attr('alt', 'Seleccione tipo de unidad');
            
            // Quitar clases de validación
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            // ✅ CAMBIO: Volver al primer tab (Datos Generales)
            $('#unit-tabs button[data-bs-target="#pill-general"]').tab('show');
            
            console.log('✅ Formulario limpiado');
        }

        function updateRowIndexes() {
            $('#docs-table tbody tr').each(function (i, tr) {
                $(tr).find('td:first').text(i + 1);
                $(tr).find('input,select,textarea').each(function () {
                    const name = $(this).attr('name');
                    if (name) $(this).attr('name', name.replace(/vehicle_document\[\d+\]/, 'vehicle_document[' + i + ']'));
                });
            });
            $('#tires-table tbody tr').each(function (i, tr) {
                $(tr).find('td:first').text(i + 1);
                $(tr).find('input,select,textarea').each(function () {
                    const name = $(this).attr('name');
                    if (name) $(this).attr('name', name.replace(/vehicle_tire\[\d+\]/, 'vehicle_tire[' + i + ']'));
                });
            });
        }

        function addDocRow(data) {
            const i = $('#docs-table tbody tr:not(.no-records)').length;
            const doc = data || {};
            
            // Remover mensaje "No hay documentos" si existe
            $('#docs-table tbody tr.no-records').remove();
            
            const rowId = 'doc-row-' + Date.now() + '-' + i;
            const isEditMode = !!doc.line_num;
            
            const $tr = $('<tr>').attr('id', rowId).attr('data-row-index', i);
            
            // Columna #
            $tr.append($('<td class="text-center align-middle">').text(i + 1));
            
            // Columna Tipo Documento (SELECT)
            const $tdType = $('<td>');
            const $selectType = $('<select>')
                .attr('name', 'vehicle_document[' + i + '][doc_type_code]')
                .addClass('form-control form-control-sm doc-type-select')
                .attr('required', true)
                .attr('data-row-index', i);
            
            // Agregar opción vacía
            $selectType.append($('<option>').val('').text('-- Seleccionar --'));
            
            // Opciones se cargarán dinámicamente desde window.docTypeOptions
            if (window.docTypeOptions && window.docTypeOptions.length > 0) {
                window.docTypeOptions.forEach(function(opt) {
                    const $opt = $('<option>').val(opt.code).text(opt.name);
                    if (doc.doc_type_code === opt.code) {
                        $opt.prop('selected', true);
                    }
                    $selectType.append($opt);
                });
            }
            
            $tdType.append($selectType);
            $tr.append($tdType);
            
            // Columna No. Documento
            $tr.append($('<td>').html(
                '<input type="text" name="vehicle_document[' + i + '][document_no]" ' +
                'class="form-control form-control-sm" value="' + esc(doc.document_no) + '" ' +
                'placeholder="Número" data-row-index="' + i + '">'
            ));
            
            // Columna Fecha Emisión
            $tr.append($('<td>').html(
                '<input type="date" name="vehicle_document[' + i + '][issue_date]" ' +
                'class="form-control form-control-sm" value="' + esc(doc.issue_date) + '" ' +
                'data-row-index="' + i + '">'
            ));
            
            // Columna Fecha Vencimiento
            $tr.append($('<td>').html(
                '<input type="date" name="vehicle_document[' + i + '][exp_date]" ' +
                'class="form-control form-control-sm" value="' + esc(doc.exp_date) + '" ' +
                'data-row-index="' + i + '">'
            ));
            
            // Columna Archivo
            const $tdFile = $('<td>');
            const fileInputId = 'doc-file-' + i;
            
            if (doc.attach && doc.attach.trim() !== '') {
                // Si ya existe archivo, mostrar nombre y botón de cambiar
                const fileName = doc.attach.split('/').pop();
                $tdFile.html(
                    '<div class="input-group input-group-sm">' +
                    '<input type="text" class="form-control form-control-sm" value="' + esc(fileName) + '" readonly>' +
                    '<button type="button" class="btn btn-sm btn-outline-primary btn-change-file" data-row-index="' + i + '" title="Cambiar archivo">' +
                    '<i class="ti ti-upload"></i></button>' +
                    '</div>' +
                    '<input type="file" id="' + fileInputId + '" name="vehicle_document[' + i + '][attach_file]" ' +
                    'class="d-none doc-file-input" data-row-index="' + i + '" ' +
                    'accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">' +
                    '<input type="hidden" name="vehicle_document[' + i + '][attach]" value="' + esc(doc.attach) + '">'
                );
            } else {
                // Si no existe archivo, mostrar botón de subir
                $tdFile.html(
                    '<button type="button" class="btn btn-sm btn-outline-primary btn-upload-file" data-row-index="' + i + '" title="Subir archivo">' +
                    '<i class="ti ti-upload"></i> Subir</button>' +
                    '<input type="file" id="' + fileInputId + '" name="vehicle_document[' + i + '][attach_file]" ' +
                    'class="d-none doc-file-input" data-row-index="' + i + '" ' +
                    'accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">'
                );
            }
            $tr.append($tdFile);
            
            // Columna Notas
            $tr.append($('<td>').html(
                '<input type="text" name="vehicle_document[' + i + '][notes]" ' +
                'class="form-control form-control-sm" value="' + esc(doc.notes) + '" ' +
                'placeholder="Observaciones" data-row-index="' + i + '">'
            ));
            
            // Columna Acciones
            const $tdActions = $('<td class="text-center">');
            const $btnGroup = $('<div class="btn-group btn-group-sm">');
            
            // Botón Preview (solo si existe archivo)
            if (doc.attach && doc.attach.trim() !== '') {
                $btnGroup.append(
                    $('<button type="button" class="btn btn-sm btn-info btn-preview-doc" data-row-index="' + i + '" title="Ver archivo">' +
                    '<i class="ti ti-eye"></i></button>')
                );
            }
            
            // Botón Eliminar
            $btnGroup.append(
                $('<button type="button" class="btn btn-sm btn-danger btn-remove-doc" data-row-index="' + i + '" title="Eliminar">' +
                '<i class="ti ti-trash"></i></button>')
            );
            
            $tdActions.append($btnGroup);
            $tr.append($tdActions);
            
            // Agregar hidden field para line_num si existe
            if (doc.line_num) {
                $tr.append('<input type="hidden" name="vehicle_document[' + i + '][line_num]" value="' + doc.line_num + '">');
            }
            
            $('#docs-table tbody').append($tr);
        }

        function addTireRow(data) {
            const i = $('#tires-table tbody tr').length;
            const t = data || {};
            
            // Remover fila de "No hay llantas" si existe
            $('#tires-table tbody tr.no-tires-msg').remove();
            
            // ✅ TABLA SOLO LECTURA - Mostrar NOMBRES en lugar de códigos para mejor UX
            const $tr = $('<tr>')
                .append($('<td class="text-center">').text(i + 1))
                .append($('<td>').text(t.tire_code || '-'))
                .append($('<td class="text-center">').text(t.axle_line_num || '-'))
                .append($('<td class="text-center">').text(t.axle_type_name || t.axle_type_code || '-'))  // ✅ NOMBRE del tipo de eje
                .append($('<td class="text-center">').text(t.position_name || t.position_code || '-'))    // ✅ NOMBRE de la posición
                .append($('<td class="text-center">').text(t.install_date || '-'))
                .append($('<td class="text-end">').text(t.install_km ? parseFloat(t.install_km).toFixed(2) : '-'));
            
            $('#tires-table tbody').append($tr);
        }

        // Load select options via AJAX for FK fields
        function loadSelectOptions() {
            console.log('═══════════════════════════════════════');
            console.log('🚀 INICIANDO loadSelectOptions()');
            console.log('═══════════════════════════════════════');
            
            const cfg = window.fleetUnitsConfig || {};
            
            if (!cfg.getFormOptions) {
                console.error('❌ ERROR: getFormOptions endpoint not configured');
                console.log('Config disponible:', cfg);
                return;
            }
            
            console.log('✅ Endpoint configurado:', cfg.getFormOptions);
            console.log('📡 Iniciando petición AJAX...');
            
            $.get(cfg.getFormOptions)
                .done(function (resp) {
                    console.log('✅ Respuesta recibida del servidor');
                    console.log('📦 Response completo:', resp);
                    
                    const data = resp.Data || resp.data || resp;
                    console.log('📊 Data extraída:', data);
                    
                    // Mapeo de selects a sus listas en el response
                    const selectMappings = [
                        // Encabezado y Datos Generales
                        { id: 'vehicle[vehicle_type_code]', dataKey: 'vehicle_type_list' },
                        { id: 'vehicle[brand_code]', dataKey: 'brand_list' },
                        { id: 'vehicle[sat_vehicle_config_code]', dataKey: 'sat_config_list' },
                        { id: 'vehicle[nom012_code]', dataKey: 'nom012_list' },
                        { id: 'vehicle[service_type_code]', dataKey: 'service_type_list' },
                        { id: 'vehicle[cargo_type_code]', dataKey: 'cargo_type_list' },
                        
                        // Datos Técnicos
                        { id: 'vehicle[fuel_type_code]', dataKey: 'fuel_type_list' },
                        
                        // Finanzas
                        { id: 'vehicle[cost_center_code]', dataKey: 'center_cost_list' },
                    ];
                    
                    console.log('🔄 Procesando', selectMappings.length, 'selects...');
                    
                    // Guardar opciones de tipos de documento en variable global para uso en addDocRow()
                    if (data && data.doc_type_list) {
                        window.docTypeOptions = [];
                        for (const code in data.doc_type_list) {
                            window.docTypeOptions.push({ code: code, name: data.doc_type_list[code] });
                        }
                        console.log('📄 Opciones de doc_type guardadas:', window.docTypeOptions.length, 'tipos');
                    }
                    
                    // Poblar cada select con sus datos correspondientes
                    selectMappings.forEach(function(mapping) {
                        if (data && data[mapping.dataKey]) {
                            console.log('  ➡️ Poblando:', mapping.id, 'con', mapping.dataKey);
                            // Pasar metadata extendida si es vehicle_type_code
                            if (mapping.id === 'vehicle[vehicle_type_code]') {
                                populateSelectById(mapping.id, data[mapping.dataKey], true);
                            } else {
                                populateSelectById(mapping.id, data[mapping.dataKey], false);
                            }
                        } else {
                            console.warn('  ⚠️ Missing data for:', mapping.dataKey);
                        }
                    });
                    
                    console.log('✅ Form options loaded successfully');
                    console.log('═══════════════════════════════════════');
                })
                .fail(function (xhr, status, error) {
                    console.error('❌ AJAX FAILED');
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('XHR:', xhr);
                    console.log('═══════════════════════════════════════');
                });
        }
        
        function populateSelectById(elementId, data, isExtended) {
            // Usar selector de atributo para IDs con caracteres especiales
            const $select = $('[id="' + elementId + '"]');
            
            console.log('    🔍 Buscando elemento:', elementId);
            
            if (!$select.length) {
                console.error('    ❌ Select NOT FOUND with id:', elementId);
                console.log('    💡 Intentando buscar en el DOM...');
                console.log('    📋 Elementos con name similar:', $('[name="vehicle[' + elementId.split('[')[1]).length);
                return;
            }
            
            console.log('    ✅ Elemento encontrado:', $select.prop('tagName'), 'con', $select.find('option').length, 'options actuales');
            
            const currentVal = $select.val();
            const firstOption = $select.find('option:first').clone();
            
            $select.empty().append(firstOption);
            
            // ✅ EXTENDED MODE: vehicle_type_code con metadata
            if (isExtended && typeof data === 'object' && !Array.isArray(data)) {
                console.log('    🔑 MODO EXTENDIDO activado - Agregando data-* attributes');
                
                Object.keys(data).forEach(function (code) {
                    const item = data[code];
                    
                    // item puede ser string (simple) o objeto (extendido)
                    if (typeof item === 'object') {
                        const $option = $('<option>')
                            .val(code)
                            .text(item.name || code)
                            .attr('data-axles', item.axles || 0)
                            .attr('data-tires', item.tires || 0)
                            .attr('data-type', item.type_unidad || '');
                        
                        console.log('      ✅ Option:', code, '| Ejes:', item.axles, '| Llantas:', item.tires, '| Tipo:', item.type_unidad);
                        $select.append($option);
                    } else {
                        // Fallback: formato simple
                        $select.append($('<option>').val(code).text(item));
                    }
                });
            }
            // Support both array and object formats (formato simple)
            else if (Array.isArray(data)) {
                console.log('    📋 Data es ARRAY con', data.length, 'items');
                data.forEach(function (item) {
                    $select.append($('<option>').val(item.code || item.id).text(item.name || item.text));
                });
            } else if (typeof data === 'object') {
                const keys = Object.keys(data);
                console.log('    📋 Data es OBJECT con', keys.length, 'keys');
                keys.forEach(function (key) {
                    $select.append($('<option>').val(key).text(data[key]));
                });
            }
            
            if (currentVal) {
                $select.val(currentVal);
            }
            
            const finalCount = $select.find('option').length - 1;
            console.log('    ✅ Populated:', elementId, 'con', finalCount, 'opciones');
        }
        
        // Helper para obtener elementos por ID con caracteres especiales
        function getById(id) {
            return $('[id="' + id + '"]');
        }
        
        // Variable para controlar si ya se cargaron los selects
        let selectsLoaded = false;
        
        // Función para inicializar selects cuando el modal esté listo
        function initializeSelectsWhenReady() {
            console.log('🎯 initializeSelectsWhenReady() - Verificando DOM...');
            
            // Verificar si el modal existe
            const $modal = $('#unit-modal');
            const $btnAdd = $('#btn-add-unit');
            
            console.log('Modal existe:', $modal.length > 0);
            console.log('Botón existe:', $btnAdd.length > 0);
            
            if ($modal.length && !selectsLoaded) {
                console.log('✅ Modal encontrado, cargando selects...');
                loadSelectOptions();
                selectsLoaded = true;
            } else if (!$modal.length) {
                console.warn('⚠️ Modal no encontrado, reintentando en 500ms...');
                setTimeout(initializeSelectsWhenReady, 500);
            }
        }
        
        // Ejecutar cuando el documento esté listo
        $(document).ready(function() {
            console.log('📄 Document Ready - Iniciando verificación...');
            initializeSelectsWhenReady();
        });
        
        // También intentar cuando se abra el modal por primera vez
        $(document).on('show.bs.modal', '#unit-modal', function() {
            console.log('🔓 Modal abierto - Evento show.bs.modal');
            if (!selectsLoaded) {
                console.log('⚡ Cargando selects por primera vez desde evento modal...');
                loadSelectOptions();
                selectsLoaded = true;
            }
        });
        
        // Events
        $('#btn-add-unit').on('click', function () {
            console.log('➕ Click en Agregar Unidad');
            
            // Asegurar que los selects estén cargados antes de abrir el modal
            if (!selectsLoaded) {
                console.log('⚡ Cargando selects antes de abrir modal...');
                loadSelectOptions();
                selectsLoaded = true;
            }
            
            clearForm();
            $('#unit-modal-label').html('<i class="ti ti-truck me-2"></i>Datos Maestros de Unidades');
            getById('vehicle[vehicle_code]').prop('readonly', false);
            if (unitModal) unitModal.show();
        });

        $('#btn-add-doc').on('click', function (e) { 
            e.preventDefault(); 
            addDocRow(); 
        });
        
        // Event handler para botón de upload de archivo
        $('#docs-table').on('click', '.btn-upload-file, .btn-change-file', function () {
            const rowIndex = $(this).data('row-index');
            $('#doc-file-' + rowIndex).trigger('click');
        });
        
        // Event handler para cambio de archivo
        $('#docs-table').on('change', '.doc-file-input', function () {
            const rowIndex = $(this).data('row-index');
            const file = this.files[0];
            
            if (file) {
                const fileName = file.name;
                const $row = $(this).closest('tr');
                const $fileCell = $row.find('td:eq(5)'); // Columna de archivo
                
                // Actualizar UI para mostrar archivo seleccionado
                $fileCell.html(
                    '<div class="input-group input-group-sm">' +
                    '<input type="text" class="form-control form-control-sm" value="' + esc(fileName) + '" readonly>' +
                    '<button type="button" class="btn btn-sm btn-outline-primary btn-change-file" data-row-index="' + rowIndex + '" title="Cambiar archivo">' +
                    '<i class="ti ti-upload"></i></button>' +
                    '</div>' +
                    '<input type="file" id="doc-file-' + rowIndex + '" name="vehicle_document[' + rowIndex + '][attach_file]" ' +
                    'class="d-none doc-file-input" data-row-index="' + rowIndex + '" ' +
                    'accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">'
                );
                
                // Copiar el archivo al nuevo input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById('doc-file-' + rowIndex).files = dataTransfer.files;
                
                // Agregar botón de preview si no existe
                const $actionsCell = $row.find('td:eq(7)'); // Columna de acciones
                if ($actionsCell.find('.btn-preview-doc').length === 0) {
                    const $previewBtn = $('<button type="button" class="btn btn-sm btn-info btn-preview-doc" data-row-index="' + rowIndex + '" title="Ver archivo">' +
                        '<i class="ti ti-eye"></i></button>');
                    $actionsCell.find('.btn-group').prepend($previewBtn);
                }
            }
        });
        
        // Event handler para preview de documento
        $('#docs-table').on('click', '.btn-preview-doc', function () {
            const rowIndex = $(this).data('row-index');
            const $row = $(this).closest('tr');
            const $fileInput = $row.find('.doc-file-input')[0];
            const $attachHidden = $row.find('input[name$="[attach]"]');
            
            if ($fileInput && $fileInput.files && $fileInput.files.length > 0) {
                // Preview de archivo local seleccionado (no subido aún)
                const file = $fileInput.files[0];
                const fileURL = URL.createObjectURL(file);
                
                if (file.type.startsWith('image/')) {
                    // Mostrar imagen en modal
                    const imgPreview = '<img src="' + fileURL + '" class="img-fluid" style="max-height: 500px;">';
                    showPreviewModal('Vista previa: ' + file.name, imgPreview);
                } else if (file.type === 'application/pdf') {
                    // Abrir PDF en nueva pestaña
                    window.open(fileURL, '_blank');
                } else {
                    alert('Vista previa no disponible para este tipo de archivo. Se descargará al guardar.');
                }
            } else if ($attachHidden.length > 0 && $attachHidden.val()) {
                // Preview de archivo ya subido (desde servidor)
                const cfg = window.fleetUnitsConfig || {};
                const baseUrl = cfg.baseUrl || '';
                const fileUrl = baseUrl + '/' + $attachHidden.val(); // ✅ Usar baseUrl dinámico
                window.open(fileUrl, '_blank');
            } else {
                alert('No hay archivo para previsualizar');
            }
        });
        
        // Event handler para eliminar documento
        $('#docs-table').on('click', '.btn-remove-doc', function () { 
            $(this).closest('tr').remove(); 
            updateRowIndexes();
            
            // Si no quedan documentos, mostrar mensaje
            if ($('#docs-table tbody tr').length === 0) {
                $('#docs-table tbody').html('<tr class="no-records"><td colspan="8" class="text-center text-muted">No hay documentos registrados</td></tr>');
            }
        });
        
        // Función auxiliar para mostrar modal de preview
        function showPreviewModal(title, content) {
            const modalHtml = 
                '<div class="modal fade" id="preview-modal" tabindex="-1">' +
                '<div class="modal-dialog modal-lg modal-dialog-centered">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<h5 class="modal-title">' + title + '</h5>' +
                '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                '</div>' +
                '<div class="modal-body text-center">' + content + '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
            
            // Remover modal previo si existe
            $('#preview-modal').remove();
            $('body').append(modalHtml);
            const previewModal = new bootstrap.Modal(document.getElementById('preview-modal'));
            previewModal.show();
            
            // Destruir modal al cerrarse
            $('#preview-modal').on('hidden.bs.modal', function () {
                $(this).remove();
            });
        }
        
        $('#btn-add-tire').on('click', function (e) { 
            e.preventDefault(); 
            addTireRow(); 
        });

        $('#docs-table').on('click', '.btn-remove-doc', function () { 
            $(this).closest('tr').remove(); 
            updateRowIndexes();
            
            // Si no quedan documentos, mostrar mensaje
            if ($('#docs-table tbody tr').length === 0) {
                $('#docs-table tbody').html('<tr class="no-records"><td colspan="8" class="text-center text-muted">No hay documentos registrados</td></tr>');
            }
        });
        
        $('#tires-table').on('click', '.btn-remove-tire', function () { 
            $(this).closest('tr').remove(); 
            updateRowIndexes(); 
        });

        function markInvalid($el, msg) { 
            $el.addClass('is-invalid'); 
            $el.next('.invalid-feedback').text(msg || 'Campo requerido'); 
        }

        // Submit handler
        $('#unit-form').on('submit', function (e) {
            e.preventDefault();
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            let valid = true;
            const $code = getById('vehicle[vehicle_code]');
            const $name = getById('vehicle[vehicle_name]');
            const $type = getById('vehicle[vehicle_type_code]');
            
            if (!$code.val() || !$code.val().trim()) { markInvalid($code, 'Código requerido'); valid = false; }
            if (!$name.val() || !$name.val().trim()) { markInvalid($name, 'Nombre requerido'); valid = false; }
            if (!$type.val() || !$type.val().trim()) { markInvalid($type, 'Tipo requerido'); valid = false; }
            
            if (!valid) {
                $('#unit-tabs button:first').trigger('click'); // switch to general tab
                return;
            }

            const vehicle = {};
            $('#unit-form').find('[name^="vehicle["]').each(function () {
                const nm = $(this).attr('name').replace(/^vehicle\[(.*)\]$/, '$1');
                if ($(this).attr('type') === 'checkbox') { 
                    vehicle[nm] = $(this).is(':checked') ? 'Y' : 'N'; 
                } else {
                    vehicle[nm] = $(this).val();
                }
            });

            // Recolectar documentos CON archivos
            const formData = new FormData();
            let docIndex = 0;
            
            $('#docs-table tbody tr:not(.no-records)').each(function () {
                const $row = $(this);
                const rowData = {};
                
                // Recolectar datos de campos de texto/select
                $row.find('input[type!="file"], select, textarea').each(function () {
                    const $input = $(this);
                    const name = $input.attr('name');
                    if (name) {
                        const m = name.match(/vehicle_document\[\d+\]\[(.+)\]/);
                        if (m) {
                            rowData[m[1]] = $input.val();
                        }
                    }
                });
                
                // Agregar datos de documento al FormData
                if (Object.keys(rowData).length > 0) {
                    for (const key in rowData) {
                        formData.append('vehicle_document[' + docIndex + '][' + key + ']', rowData[key] || '');
                    }
                    
                    // Agregar archivo si existe
                    const $fileInput = $row.find('.doc-file-input')[0];
                    if ($fileInput && $fileInput.files && $fileInput.files.length > 0) {
                        formData.append('vehicle_document[' + docIndex + '][attach_file]', $fileInput.files[0]);
                    }
                    
                    docIndex++;
                }
            });

            const tires = [];
            $('#tires-table tbody tr:not(.no-records)').each(function () {
                const row = {};
                $(this).find('input,select,textarea').each(function () {
                    const m = $(this).attr('name').match(/vehicle_tire\[\d+\]\[(.+)\]/);
                    if (m) row[m[1]] = $(this).val();
                });
                if (Object.keys(row).length) tires.push(row);
            });

            // CSRF token
            const csrfParam = $('meta[name="csrf-param"]').attr('content');
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Agregar vehicle data al FormData
            for (const key in vehicle) {
                formData.append('vehicle[' + key + ']', vehicle[key] || '');
            }
            
            // Agregar tires al FormData (JSON string)
            formData.append('vehicle_tire', JSON.stringify(tires));
            
            // Agregar CSRF dinámicamente
            if (csrfParam && csrfToken) {
                formData.append(csrfParam, csrfToken);
            }
            
            const cfg = window.fleetUnitsConfig || {};
            
            console.log('📦 FormData a enviar (vehicle + documentos con archivos + llantas)');
            console.log('🔐 CSRF:', csrfParam, '=', csrfToken);
            console.log('📄 Documentos:', docIndex);
                        
            if (cfg.save) {
                const $btn = $('#btn-save-unit');
                $btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin me-1"></i>Guardando...');
                
                $.ajax({
                    url: cfg.save,
                    type: 'POST',
                    data: formData,
                    processData: false,  // ✅ CRÍTICO para FormData
                    contentType: false,  // ✅ CRÍTICO para FormData
                    dataType: 'json'
                })
                    .done(function (resp) {
                        console.log('📥 Respuesta del servidor:', resp);
                        
                        if (resp && resp.Success === 'Ok') {
                            // Éxito: mostrar toastr y cerrar modal
                            if (typeof toastr !== 'undefined') {
                                toastr.success(resp.Msg || 'Unidad guardada correctamente', 'Éxito');
                            } else {
                                alert(resp.Msg || 'Guardado correctamente');
                            }
                            
                            if (unitModal) unitModal.hide();
                            
                            // Reload table if CrudWidget is available
                            if (window.tbl_units) {
                                window.tbl_units.ajax.reload(null, false);
                            } else {
                                location.reload();
                            }
                        } else {
                            // Error: mostrar toastr y marcar campos
                            const errorMsg = resp && resp.Msg ? resp.Msg : 'Error al guardar';
                            
                            if (typeof toastr !== 'undefined') {
                                toastr.error(errorMsg, 'Error de Validación', {
                                    timeOut: 8000,
                                    extendedTimeOut: 5000
                                });
                            } else {
                                alert('Error: ' + errorMsg);
                            }
                            
                            // Marcar campos con error si están disponibles
                            if (resp.Errors && typeof resp.Errors === 'object') {
                                console.log('❌ Errores de validación:', resp.Errors);
                                
                                let firstErrorField = null;
                                
                                for (const field in resp.Errors) {
                                    const fieldMsg = resp.Errors[field];
                                    const $field = getById('vehicle[' + field + ']');
                                    
                                    if ($field.length) {
                                        markInvalid($field, fieldMsg);
                                        if (!firstErrorField) {
                                            firstErrorField = $field;
                                        }
                                    }
                                }
                                
                                // Focus en el primer campo con error
                                if (firstErrorField) {
                                    console.log('🎯 Focus en primer campo con error');
                                    firstErrorField.focus();
                                    // Scroll hasta el campo
                                    firstErrorField[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                                
                                // Ir a la primera pestaña donde está el error
                                $('#unit-tabs button:first').trigger('click');
                            }
                        }
                    })
                    .fail(function (xhr) {
                        console.error('❌ Error de red:', xhr);
                        
                        let msg = 'Error de red al guardar';
                        if (xhr.responseJSON && xhr.responseJSON.Msg) {
                            msg = xhr.responseJSON.Msg;
                        } else if (xhr.status === 400) {
                            msg = 'Error 400: Datos inválidos o CSRF token faltante';
                        } else if (xhr.status === 500) {
                            msg = 'Error 500: Error interno del servidor';
                        }
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.error(msg, 'Error de Red', { timeOut: 8000 });
                        } else {
                            alert(msg);
                        }
                    })
                    .always(function () {
                        $btn.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>Guardar');
                    });
            } else {
                console.log('fleet.units payload (simulado):', payload);
                alert('Validación OK. Endpoint no configurado.');
                if (unitModal) unitModal.hide();
            }
        });

        // Public API for editing (called by CrudWidget edit button)
        window.FleetUnitsManager = window.FleetUnitsManager || {};
        window.FleetUnitsManager.edit = function (vehicleCode) {
            console.log('═══════════════════════════════════════');
            console.log('✏️ EDIT MODE - Vehicle Code:', vehicleCode);
            console.log('═══════════════════════════════════════');
            
            const cfg = window.fleetUnitsConfig || {};
            if (!cfg.get) {
                console.error('❌ FleetUnitsManager: get endpoint not configured');
                return;
            }
            
            // Asegurar que los selects estén cargados
            if (!selectsLoaded) {
                console.log('⚡ Cargando selects antes de editar...');
                loadSelectOptions();
                selectsLoaded = true;
                
                // Esperar un momento para que se carguen los selects
                setTimeout(function() {
                    loadVehicleData(vehicleCode, cfg);
                }, 500);
            } else {
                loadVehicleData(vehicleCode, cfg);
            }
        };
        
        function loadVehicleData(vehicleCode, cfg) {
            console.log('📡 Cargando datos del vehículo:', vehicleCode);
            
            $.get(cfg.get, { pk: vehicleCode })
                .done(function (resp) {
                    console.log('✅ Datos recibidos del servidor');
                    console.log('📦 Response:', resp);
                    
                    if (resp && resp.Success === 'Ok' && resp.Data) {
                        const v = resp.Data;
                        
                        console.log('📋 Datos del vehículo:', v);
                        
                        // Helper para setear valores de forma segura
                        function setVal(elementId, value) {
                            const $el = getById(elementId);
                            if ($el.length) {
                                $el.val(value || '');
                                if (value) {
                                    console.log('  ✓', elementId, '=', value);
                                }
                            } else {
                                console.warn('  ✗ Element not found:', elementId);
                            }
                        }
                        
                        // Encabezado
                        $('#vehicle_vehicle_code').val(v.vehicle_code || '');
                        setVal('vehicle[vehicle_code]', v.vehicle_code);
                        getById('vehicle[vehicle_code]').prop('readonly', true);
                        setVal('vehicle[vehicle_name]', v.vehicle_name);
                        setVal('vehicle[vehicle_type_code]', v.vehicle_type_code);
                        setVal('vehicle[available]', v.available || 'Y');
                        setVal('vehicle[current_km]', v.current_km);
                        setVal('vehicle[current_fuel]', v.current_fuel);
                        
                        // ✅ Guardar tipo de vehículo actual como previous para validación de cambios
                        previousVehicleTypeCode = v.vehicle_type_code;
                        
                        // Tab Datos Generales
                        setVal('vehicle[brand_code]', v.brand_code);
                        setVal('vehicle[model]', v.model);
                        setVal('vehicle[unit_year]', v.unit_year);
                        setVal('vehicle[plate_no]', v.plate_no);
                        setVal('vehicle[economic_no]', v.economic_no);
                        setVal('vehicle[iave]', v.iave);
                        setVal('vehicle[sat_vehicle_config_code]', v.sat_vehicle_config_code);
                        setVal('vehicle[nom012_code]', v.nom012_code);
                        setVal('vehicle[acquisition]', v.acquisition || 'P');
                        setVal('vehicle[purchase_date]', v.purchase_date);
                        setVal('vehicle[purchase_price]', v.purchase_price);
                        setVal('vehicle[service_type_code]', v.service_type_code);
                        setVal('vehicle[cargo_type_code]', v.cargo_type_code);
                        setVal('vehicle[notes]', v.notes);
                        
                        // Tab Datos Técnicos
                        setVal('vehicle[engine_no]', v.engine_no);
                        setVal('vehicle[serial_no]', v.serial_no);
                        setVal('vehicle[vin]', v.vin);
                        setVal('vehicle[unit_length]', v.unit_length);
                        setVal('vehicle[unit_width]', v.unit_width);
                        setVal('vehicle[unit_height]', v.unit_height);
                        setVal('vehicle[cargo_length]', v.cargo_length);
                        setVal('vehicle[cargo_width]', v.cargo_width);
                        setVal('vehicle[cargo_height]', v.cargo_height);
                        setVal('vehicle[fuel_type_code]', v.fuel_type_code);
                        setVal('vehicle[fuel_capacity]', v.fuel_capacity);
                        setVal('vehicle[fuel_performance]', v.fuel_performance);
                        setVal('vehicle[initial_km]', v.initial_km);
                        setVal('vehicle[gps_id]', v.gps_id);
                        setVal('vehicle[gps_model]', v.gps_model);
                        setVal('vehicle[gps_provider]', v.gps_provider);
                        setVal('vehicle[weight_capacity]', v.weight_capacity);
                        setVal('vehicle[volume_capacity]', v.volume_capacity);
                        
                        // Tab Asignaciones
                        setVal('vehicle[default_driver_code]', v.default_driver_code);
                        setVal('vehicle[default_driver2_code]', v.default_driver2_code);
                        setVal('vehicle[default_trailer1_code]', v.default_trailer1_code);
                        setVal('vehicle[default_trailer2_code]', v.default_trailer2_code);
                        setVal('vehicle[default_dolly_code]', v.default_dolly_code);
                        
                        // Tab Finanzas
                        setVal('vehicle[fixed_asset_code]', v.fixed_asset_code);
                        setVal('vehicle[gl_account]', v.gl_account);
                        setVal('vehicle[cost_center_code]', v.cost_center_code);
                        
                        // ✅ MEJORA 3: Actualizar imagen de llantas según el tipo de vehículo cargado
                        if (v.vehicle_type_code) {
                            const $typeSelect = getById('vehicle[vehicle_type_code]');
                            const $selectedOption = $typeSelect.find('option[value="' + v.vehicle_type_code + '"]');
                            
                            if ($selectedOption.length > 0) {
                                const axles = $selectedOption.attr('data-axles');
                                const tires = $selectedOption.attr('data-tires');
                                const typeUnidad = $selectedOption.attr('data-type');
                                
                                if (typeUnidad && axles && tires) {
                                    const imageName = typeUnidad.toLowerCase() + '-' + axles + '-' + tires + '.png';
                                    const cfg = window.fleetUnitsConfig || {};
                                    const imagesPath = cfg.imagesPath || '/web/images';
                                    const imagePath = imagesPath + '/' + imageName;
                                    
                                    console.log('🖼️ Cargando imagen de tipo:', imagePath);
                                    $('#tire-config-image').attr('src', imagePath).attr('alt', v.vehicle_type_code + ' - ' + tires + ' llantas');
                                } else {
                                    console.warn('⚠️ Metadatos de tipo de vehículo no disponibles');
                                }
                            }
                        }
                        
                        // Documentos y Llantas
                        $('#docs-table tbody').empty();
                        if (resp.Data.documents && resp.Data.documents.length > 0) {
                            resp.Data.documents.forEach(function (d) { addDocRow(d); });
                        } else {
                            $('#docs-table tbody').html('<tr class="no-records"><td colspan="8" class="text-center text-muted">No hay documentos registrados</td></tr>');
                        }
                        
                        $('#tires-table tbody').empty();
                        if (resp.Data.tires && resp.Data.tires.length > 0) {
                            resp.Data.tires.forEach(function (t) { addTireRow(t); });
                        } else {
                            $('#tires-table tbody').html('<tr class="no-tires-msg"><td colspan="7" class="text-center text-muted">No hay llantas instaladas</td></tr>');
                        }
                        
                        console.log('✅ Todos los valores cargados');
                        console.log('🔓 Abriendo modal...');
                        
                        // ✅ CAMBIO: Seleccionar primer tab (Datos Generales)
                        $('#unit-tabs button[data-bs-target="#pill-general"]').tab('show');
                        
                        $('#unit-modal-label').html('<i class="ti ti-truck me-2"></i>Editar Unidad: ' + (v.vehicle_code || ''));
                        if (unitModal) unitModal.show();
                        
                        console.log('═══════════════════════════════════════');
                    } else {
                        console.error('❌ Respuesta inválida del servidor');
                        alert('Error al cargar datos: ' + (resp && resp.Msg ? resp.Msg : 'Respuesta inválida'));
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('❌ Error al cargar datos del vehículo');
                    console.error('Status:', status);
                    console.error('Error:', error);
                    alert('Error de red al cargar la unidad');
                });
        }

        // ═══════════════════════════════════════════════════════════════
        // Hook CrudWidget edit button - Interceptar clicks de edición
        // ═══════════════════════════════════════════════════════════════
        // ✅ SOLUCIÓN DEFINITIVA: addEventListener NATIVO con capture: true
        // Esto ejecuta en fase de CAPTURA (antes que bubbling de jQuery)
        const tableContainer = document.getElementById('tbl-units');
        if (tableContainer) {
            tableContainer.addEventListener('click', function (e) {
                // Buscar el botón edit más cercano
                const editBtn = e.target.closest('.dt-btn-action[data-action="edit"]');
                
                if (editBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation(); // ✅ Bloquear completamente CrudWidget
                    
                    const pk = editBtn.getAttribute('data-pk');
                    console.log('═══════════════════════════════════════════════════════════');
                    console.log('✏️ EDITAR UNIDAD (CAPTURE PHASE) - PK:', pk);
                    console.log('═══════════════════════════════════════════════════════════');
                    
                    if (pk && window.FleetUnitsManager && window.FleetUnitsManager.edit) {
                        window.FleetUnitsManager.edit(pk);
                    } else {
                        console.error('❌ FleetUnitsManager.edit no está disponible');
                    }
                    
                    return false;
                }
            }, true); // ✅ true = CAPTURE PHASE (se ejecuta ANTES que jQuery)
        } else {
            console.error('❌ Tabla #tbl-units no encontrada para hook de edición');
        }
        
        // ═══════════════════════════════════════════════════════════════
        // Event Listener: Capturar cambio de Tipo de Vehículo
        // ═══════════════════════════════════════════════════════════════
        let previousVehicleTypeCode = null; // Track para revertir si hay error
        
        $(document).on('change', '[id="vehicle[vehicle_type_code]"]', function () {
            const $select = $(this);
            const $selectedOption = $select.find('option:selected');
            const vehicleTypeCode = $select.val();
            const vehicleTypeName = $selectedOption.text();
            const axles = $selectedOption.attr('data-axles');
            const tires = $selectedOption.attr('data-tires');
            const typeUnidad = $selectedOption.attr('data-type');
            
            console.log('═══════════════════════════════════════════════════════════');
            console.log('🚗 CAMBIO DE TIPO DE VEHÍCULO');
            console.log('═══════════════════════════════════════════════════════════');
            console.log('📋 Código:', vehicleTypeCode);
            console.log('📝 Nombre:', vehicleTypeName);
            console.log('🔧 Total Ejes (data-axles):', axles);
            console.log('🛞 Total Llantas (data-tires):', tires);
            console.log('🏷️  Tipo Unidad (data-type):', typeUnidad);
            
            if (!vehicleTypeCode || !typeUnidad || !axles || !tires) {
                console.warn('⚠️ Faltan metadatos en la opción seleccionada');
                return;
            }
            
            // ✅ 1. Cargar imagen dinámica: {type}-{axles}-{tires}.png (minúsculas)
            const imageName = typeUnidad.toLowerCase() + '-' + axles + '-' + tires + '.png';
            const cfg = window.fleetUnitsConfig || {};
            const imagesPath = cfg.imagesPath || '/web/images'; // ✅ Usar imagesPath dinámico
            const imagePath = imagesPath + '/' + imageName;
            
            console.log('🖼️ Cargando imagen:', imagePath);
            $('#tire-config-image').attr('src', imagePath).attr('alt', vehicleTypeName + ' - ' + tires + ' llantas');
            
            // ✅ 2. Auto-llenar tabla de llantas con validación
            const vehicleCode = $('#vehicle_vehicle_code').val() || null;
            // ✅ Reutilizar cfg del scope superior (ya declarado arriba)
            
            if (!cfg.getAxleConfig) {
                console.error('❌ Endpoint getAxleConfig no configurado');
                return;
            }
            
            console.log('📡 Consultando configuración de ejes...');
            
            const url = cfg.getAxleConfig + '?vehicleTypeCode=' + encodeURIComponent(vehicleTypeCode) + 
                        (vehicleCode ? '&vehicleCode=' + encodeURIComponent(vehicleCode) : '');
            
            $.get(url)
                .done(function (resp) {
                    console.log('✅ Respuesta recibida:', resp);
                    
                    if (resp.Success === 'Error') {
                        // ❌ Error: No puede cambiar tipo de unidad (tiene llantas asignadas)
                        console.error('❌', resp.Msg);
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.error(resp.Msg, 'No se puede cambiar tipo de unidad', { timeOut: 8000 });
                        } else {
                            alert(resp.Msg);
                        }
                        
                        // Revertir select al valor anterior
                        if (previousVehicleTypeCode) {
                            $select.val(previousVehicleTypeCode);
                            console.log('⏮️ Tipo de unidad revertido a:', previousVehicleTypeCode);
                        }
                        
                        return;
                    }
                    
                    if (resp.Success === 'Ok' && resp.Data && resp.Data.positions) {
                        const positions = resp.Data.positions;
                        
                        console.log('🛞 Llenando tabla con', positions.length, 'posiciones');
                        
                        // Limpiar tabla
                        $('#tires-table tbody').empty();
                        
                        // Agregar filas con los datos de configuración
                        positions.forEach(function (pos) {
                            addTireRow({
                                line_num: pos.line_num,
                                axle_line_num: pos.axle_line_num,
                                axle_type_code: pos.axle_type_code,
                                axle_type_name: pos.axle_type_name,      // ✅ NUEVO: Nombre del tipo de eje
                                position_code: pos.position_code,
                                position_name: pos.position_name,        // ✅ NUEVO: Nombre de la posición
                                tire_code: null,
                                install_date: null,
                                install_km: null,
                                record_km: null
                            });
                        });
                        
                        // Actualizar valor anterior (cambio exitoso)
                        previousVehicleTypeCode = vehicleTypeCode;
                        
                        console.log('✅ Tabla de llantas actualizada correctamente');
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('❌ Error AJAX:', status, error);
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Error al obtener configuración de ejes', 'Error de Red');
                    } else {
                        alert('Error al obtener configuración de ejes');
                    }
                });
            
            console.log('═══════════════════════════════════════════════════════════');
        });

        console.log('✅ FleetUnitsManager initialized');
        console.log('📋 Config disponible:', window.fleetUnitsConfig);
        console.log('🎯 FleetUnitsManager.edit disponible:', typeof window.FleetUnitsManager.edit);
        console.log('═══════════════════════════════════════════════════════════');
    });

})();
