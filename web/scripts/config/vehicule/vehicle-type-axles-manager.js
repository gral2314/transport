/**
 * VehicleTypeAxlesManager
 * Gestor de configuración de ejes para tipos de vehículo
 * 
 * VERSIÓN 3.3: Toastr + Vista Previa + Modal Fix + Auto-load Ejes
 * - Notificaciones con toastr (success/error/warning)
 * - Vista previa dinámica de composición de ejes (col-3, ancho fijo)
 * - Fix: Modal se abre correctamente desde botón editar
 * - Auto-carga de ejes y vista previa en modo edición
 * - Sin código JavaScript en archivos PHP
 * 
 * @version 3.3
 */

console.log('✅ VehicleTypeAxlesManager v3.3 - CARGADO CON ÉXITO');

const VehicleTypeAxlesManager = {
    axlesData: [],
    axleTypesCache: [],
    isEditMode: false,
    isUsed: false,
    baseUrl: '',
    saveUrl: '',
    tableVar: '',
    
    /**
     * Mostrar notificación usando toastr
     */
    showNotification: function(message, type) {
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 3000
            };
            toastr[type](message);
        } else {
            alert(message);
        }
    },
    
    /**
     * Configura URLs y registra eventos
     */
    configure: function(config) {
        this.baseUrl = config.baseUrl || '';
        this.saveUrl = config.saveUrl || '';
        this.tableVar = config.tableVar || 'tbl_vehicle_type';
        
        console.log('⚙️ VehicleTypeAxlesManager configurado:', config);
        
        // Cargar tipos de ejes
        this.loadAxleTypes();
        
        // Registrar todos los eventos
        this.bindAllEvents();
    },
    
    /**
     * Registra TODOS los eventos (add, edit, save)
     * IMPORTANTE: Este método se ejecuta en configure() para garantizar
     * que los handlers se registren ANTES que los del CrudWidget
     */
    bindAllEvents: function() {
        const self = this;
        
        console.log('🔧 [VehicleTypeAxlesManager] Registrando eventos...');
        
        // Evento: Botón Agregar (nuevo registro)
        $('#btn-add-vehicle-type').off('click').on('click', function() {
            console.log('🆕 Nuevo tipo de vehículo');
            $('#frm-vehicle-type')[0].reset();
            $('#input-code').prop('readonly', false).css('background-color', '');
            $('#input-active').prop('checked', true);
            self.reset();
        });
        
        // Evento: Bot\u00f3n Editar desde DataTable
        // NOTA: NO usar stopImmediatePropagation porque el CrudWidget ya captur\u00f3 el evento
        // En su lugar, interceptamos DESPU\u00c9S del handler del CrudWidget usando un timeout de 0ms
        $('#tbl-vehicle-type').off('click', '.dt-btn-action[data-action="edit"]').on('click', '.dt-btn-action[data-action="edit"]', function(e) {
            console.log('\ud83d\udfe2 [VehicleTypeAxlesManager] Click en EDITAR interceptado');
            
            // NO prevenir default aqu\u00ed, dejar que el CrudWidget maneje primero
            // e.preventDefault();
            // e.stopPropagation();
            
            const btn = $(this);
            const pk = btn.data('pk');
            const tr = btn.closest('tr');
            const table = window[self.tableVar];
            
            if (!table) {
                console.error('❌ [VehicleTypeAxlesManager] Tabla no encontrada:', self.tableVar);
                return;
            }
            
            const rowData = table.row(tr).data();
            
            console.log('🟢 [VehicleTypeAxlesManager] PK:', pk);
            console.log('🟢 [VehicleTypeAxlesManager] RowData:', rowData);
            console.log('🟢 [VehicleTypeAxlesManager] Ejes recibidos:', rowData.axles);
            
            // Cargar datos en formulario
            $('#input-code').val(rowData.code).prop('readonly', true).css('background-color', '#e9ecef');
            $('#input-name').val(rowData.name);
            $('#input-active').prop('checked', rowData.active === 'Y' || rowData.active === '1' || rowData.active === 1);
            
            // Cargar ejes (esto también actualiza la vista previa)
            self.loadAxles(rowData.axles || []);
            self.isEditMode = true;
            self.isUsed = rowData.is_used || false;
            
            console.log('🎨 [VehicleTypeAxlesManager] Vista previa actualizada con', (rowData.axles || []).length, 'ejes');
            
            // Abrir modal
            const modalEl = document.getElementById('mdl-vehicle-type');
            if (!modalEl) {
                console.error('❌ [VehicleTypeAxlesManager] Modal no encontrado: mdl-vehicle-type');
                return;
            }
            
            console.log('\ud83d\udfe2 [VehicleTypeAxlesManager] Abriendo modal manualmente...');
            
            // Usar setTimeout para ejecutar DESPU\u00c9S del handler del CrudWidget
            setTimeout(() => {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
                console.log('\u2705 [VehicleTypeAxlesManager] Modal abierto correctamente');
            }, 50);
        });
        
        // Evento: Botón Guardar
        $('#btn-save-vehicle-type').off('click').on('click', function() {
            self.handleSave();
        });
        
        // Evento: Agregar eje
        $(document).off('click', '#btn-add-axle').on('click', '#btn-add-axle', function(e) {
            e.preventDefault();
            self.addAxleRow();
        });
        
        // Evento: Eliminar eje
        $(document).off('click', '.btn-remove-axle').on('click', '.btn-remove-axle', function(e) {
            e.preventDefault();
            const lineNum = $(this).data('line-num');
            self.removeAxleRow(lineNum);
        });
        
        // Evento: Cambio de tipo de eje
        $(document).off('change', '.axle-type-select').on('change', '.axle-type-select', function(e) {
            const lineNum = $(this).data('line-num');
            self.updateTireQty(lineNum, this.value);
        });
        
        console.log('✅ Todos los eventos vinculados');
        console.log('🔍 [VehicleTypeAxlesManager] Handlers registrados en este orden:');
        console.log('   1. btn-add-vehicle-type (click)');
        console.log('   2. .dt-btn-action[data-action="edit"] (click) - CON stopImmediatePropagation');
        console.log('   3. btn-save-vehicle-type (click)');
        console.log('   4. btn-add-axle (click)');
        console.log('   5. btn-remove-axle (click)');
        console.log('   6. axle-type-select (change)');
    },
    
    /**
     * Maneja el guardado con validación y envío AJAX
     */
    handleSave: function() {
        console.log('💾 Guardando tipo de vehículo...');
        
        const code = $('#input-code').val().trim();
        const name = $('#input-name').val().trim();
        const active = $('#input-active').is(':checked') ? 'Y' : 'N';
        
        // Validar campos básicos
        if (!code || !name) {
            this.showNotification('Complete todos los campos obligatorios', 'warning');
            return;
        }
        
        // Recolectar ejes
        const axles = this.collectAxles();
        if (axles.length === 0) {
            this.showNotification('Debe configurar al menos 1 eje', 'warning');
            return;
        }
        
        // Preparar datos con CSRF token manual
        const csrfParam = $('meta[name="csrf-param"]').attr('content');
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        const postData = {
            code: code,
            name: name,
            active: active,
            axles: axles
        };
        
        // Agregar CSRF token si existe
        if (csrfParam && csrfToken) {
            postData[csrfParam] = csrfToken;
            console.log('🔐 CSRF token agregado:', csrfParam);
        }
        
        console.log('📤 Datos a enviar:', postData);
        
        // Enviar con $.ajax
        const self = this;
        $.ajax({
            url: this.saveUrl,
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                console.log('📥 Respuesta:', response);
                if (response.Success === 'Ok') {
                    self.showNotification(response.Msg || 'Tipo de vehículo guardado correctamente', 'success');
                    
                    // Cerrar modal
                    const modalEl = document.getElementById('mdl-vehicle-type');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Recargar tabla
                    const table = window[self.tableVar];
                    if (table) {
                        table.ajax.reload(null, false);
                    }
                    
                    self.reset();
                } else {
                    self.showNotification(response.Msg || 'No se pudo guardar', 'error');
                }
            },
            error: function(xhr) {
                console.error('❌ Error AJAX:', xhr.responseText);
                const msg = xhr.responseJSON?.Msg || xhr.statusText || 'Error desconocido';
                self.showNotification('Error de conexión: ' + msg, 'error');
            }
        });
    },
    
    /**
     * Carga los tipos de ejes activos vía AJAX
     */
    loadAxleTypes: function() {
        const url = this.baseUrl + '/axle-type/list?active=Y';
        console.log('🔍 Cargando tipos de ejes desde:', url);
        
        const self = this;
        fetch(url)
            .then(resp => {
                console.log('📡 Respuesta HTTP:', resp.status, resp.statusText);
                if (!resp.ok) {
                    throw new Error(`HTTP ${resp.status}: ${resp.statusText}`);
                }
                return resp.json();
            })
            .then(result => {
                console.log('📦 Datos JSON recibidos:', result);
                if (result.Success === 'Ok') {
                    self.axleTypesCache = result.Data;
                    console.log(`✅ ${self.axleTypesCache.length} tipos de ejes cargados`);
                } else {
                    console.error('❌ Error en respuesta:', result.Msg);
                }
            })
            .catch(err => {
                console.error('❌ Error al cargar tipos de ejes:', err);
            });
    },
    
    /**
     * Agrega una nueva fila de eje
     */
    addAxleRow: function() {
        const tbody = $('#axles-tbody');
        const emptyRow = $('#axles-empty-row');
        
        if (emptyRow.length) {
            emptyRow.hide();
        }
        
        // Calcular nuevo line_num
        const newLineNum = this.axlesData.length > 0 
            ? Math.max(...this.axlesData.map(a => a.line_num || 0)) + 1 
            : 1;
        
        const row = this.createAxleRow(newLineNum, '', 0);
        tbody.append(row);
        
        // Agregar al array temporal
        this.axlesData.push({ line_num: newLineNum, axle_type_code: '', tire_qty: 0 });
        console.log('➕ Fila de eje agregada:', newLineNum);
        
        // Actualizar vista previa
        this.updateAxlePreview();
    },
    
    /**
     * Elimina una fila de eje
     */
    removeAxleRow: function(lineNum) {
        // Validar mínimo 1 eje
        const currentRows = $('#axles-tbody tr').not('#axles-empty-row');
        if (currentRows.length <= 1) {
            this.showNotification('Debe tener al menos 1 eje configurado', 'warning');
            return;
        }
        
        if (confirm('¿Eliminar este eje?')) {
            // Eliminar del array
            this.axlesData = this.axlesData.filter(a => a.line_num != lineNum);
            
            // Eliminar de DOM
            $(`tr[data-line-num="${lineNum}"]`).remove();
            
            // Mostrar empty row si no quedan ejes
            const remainingRows = $('#axles-tbody tr').not('#axles-empty-row');
            if (remainingRows.length === 0) {
                $('#axles-empty-row').show();
            }
            
            console.log('➖ Fila de eje eliminada:', lineNum);
            
            // Actualizar vista previa
            this.updateAxlePreview();
        }
    },
    
    /**
     * Crea el HTML de una fila de eje
     */
    createAxleRow: function(lineNum, axleTypeCode, tireQty) {
        const options = this.axleTypesCache.map(axle => 
            `<option value="${axle.code}" ${axle.code === axleTypeCode ? 'selected' : ''}>
                ${axle.name}
            </option>`
        ).join('');
        
        const html = `
            <tr data-line-num="${lineNum}">
                <td class="text-center align-middle">${lineNum}</td>
                <td>
                    <select class="form-select form-select-sm axle-type-select" 
                            data-line-num="${lineNum}" required>
                        <option value="">-- Seleccionar Tipo de Eje --</option>
                        ${options}
                    </select>
                </td>
                <td class="text-center align-middle">
                    <span class="badge bg-info tire-qty-badge" data-line-num="${lineNum}">
                        ${tireQty > 0 ? tireQty + ' llantas' : '-'}
                    </span>
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-axle" 
                            data-line-num="${lineNum}">
                        <i class="ti ti-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        return html;
    },
    
    /**
     * Actualiza la cantidad de llantas al seleccionar tipo de eje
     */
    updateTireQty: function(lineNum, axleTypeCode) {
        const axle = this.axleTypesCache.find(a => a.code === axleTypeCode);
        const tireQty = axle ? axle.tire_qty : 0;
        
        // Actualizar badge
        const badge = $(`.tire-qty-badge[data-line-num="${lineNum}"]`);
        if (badge.length) {
            badge.text(tireQty > 0 ? tireQty + ' llantas' : '-');
        }
        
        // Actualizar en array
        const axleData = this.axlesData.find(a => a.line_num == lineNum);
        if (axleData) {
            axleData.axle_type_code = axleTypeCode;
            axleData.tire_qty = tireQty;
        }
        
        console.log(`🔄 Actualizado eje ${lineNum}:`, axleTypeCode, `(${tireQty} llantas)`);
        
        // Actualizar vista previa
        this.updateAxlePreview();
    },
    
    /**
     * Carga ejes existentes (modo edición)
     */
    loadAxles: function(axles) {
        this.axlesData = axles || [];
        const tbody = $('#axles-tbody');
        const emptyRow = $('#axles-empty-row');
        
        if (!tbody.length) {
            console.error('❌ No se encontró tbody de ejes');
            return;
        }
        
        // Limpiar tbody (excepto empty row)
        tbody.find('tr').not('#axles-empty-row').remove();
        
        if (this.axlesData.length === 0) {
            emptyRow.show();
            console.log('ℹ️ No hay ejes para cargar');
            return;
        }
        
        emptyRow.hide();
        
        this.axlesData.forEach(axle => {
            // Buscar tire_qty del cache de axle_types
            const axleType = this.axleTypesCache.find(at => at.code === axle.axle_type_code);
            const tireQty = axleType ? axleType.tire_qty : 0;
            
            const row = this.createAxleRow(axle.line_num, axle.axle_type_code, tireQty);
            tbody.append(row);
        });
        
        console.log(`📥 ${this.axlesData.length} ejes cargados`);
        
        // Actualizar vista previa
        this.updateAxlePreview();
    },
    
    /**
     * Recolecta datos de ejes para enviar al servidor
     */
    collectAxles: function() {
        const axles = [];
        
        $('.axle-type-select').each((index, select) => {
            const $select = $(select);
            if ($select.val()) {
                const lineNum = parseInt($select.data('line-num'));
                const axle = this.axleTypesCache.find(a => a.code === $select.val());
                
                axles.push({
                    line_num: lineNum,
                    axle_type_code: $select.val(),
                    tire_qty: axle ? axle.tire_qty : 0
                });
            }
        });
        
        console.log('📤 Ejes recolectados:', axles);
        return axles;
    },
    
    /**
     * Resetea el estado del manager
     */
    reset: function() {
        this.axlesData = [];
        this.isEditMode = false;
        this.isUsed = false;
        
        const tbody = $('#axles-tbody');
        const emptyRow = $('#axles-empty-row');
        
        if (tbody.length) {
            tbody.find('tr').not('#axles-empty-row').remove();
            if (emptyRow.length) {
                emptyRow.show();
            }
        }
        
        console.log('🔄 VehicleTypeAxlesManager reseteado');
        
        // Limpiar vista previa
        this.updateAxlePreview();
    },
    
    /**
     * Actualiza la vista previa de composición de ejes
     */
    updateAxlePreview: function() {
        const previewContainer = $('#axle-composition-preview');
        if (!previewContainer.length) return;
        
        // Limpiar contenedor
        previewContainer.empty();
        
        // Obtener ejes configurados
        const axles = this.collectAxles();
        
        if (axles.length === 0) {
            previewContainer.html('<p class="text-muted text-center"><i class="ti ti-info-circle"></i> Sin ejes configurados</p>');
            return;
        }
        
        // Generar imágenes
        axles.forEach((axle, index) => {
            const axleData = this.axleTypesCache.find(a => a.code === axle.axle_type_code);
            const tireQty = axleData ? axleData.tire_qty : 0;
            
            let imageSrc = '';
            let altText = '';
            
            if (tireQty === 2) {
                imageSrc = '../images/eje_2_llantas.png';
                altText = 'Eje de 2 llantas';
            } else if (tireQty === 4) {
                imageSrc = '../images/eje_4_llantas.png';
                altText = 'Eje de 4 llantas';
            } else if (tireQty === 8) {
                imageSrc = '../images/eje_8_llantas.png';
                altText = 'Eje tandem dual (8 llantas)';
            } else if (tireQty === 12) {
                imageSrc = '../images/eje_12_llantas.png';
                altText = 'Eje tandem triple (12 llantas)';
            }
            
            if (imageSrc) {
                const imgHtml = `
                    <div class="mb-2">
                        <small class="text-muted d-block">Eje ${index + 1} - ${axleData ? axleData.name : 'N/A'}</small>
                        <img src="${imageSrc}" alt="${altText}" class="img-thumbnail" style="width: 100%; height: auto; object-fit: contain;">
                    </div>
                `;
                previewContainer.append(imgHtml);
            }
        });
    }
};

console.log('🎯 VehicleTypeAxlesManager listo para usar');


