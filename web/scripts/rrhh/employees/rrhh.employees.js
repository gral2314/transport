/**
 * HR Employees Manager - Gestión de Empleados
 * Auto-loaded by DynamicAssetBundle for EmployeeController::actionIndex()
 * Path: web/scripts/rrhh/employees/rrhh.employees.js
 */
(function () {
    'use strict';
    
    console.log('═══════════════════════════════════════════════════════════');
    console.log('🚀 HR EMPLOYEES MANAGER - INICIANDO CARGA DEL SCRIPT');
    console.log('═══════════════════════════════════════════════════════════');
    
    if (typeof jQuery === 'undefined') {
        console.error('❌ jQuery not loaded - HR Employees Manager disabled');
        return;
    }
    
    const $ = jQuery;

    function esc(s) { 
        return String(s || '').replace(/[&<>"]/g, function (m) { 
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]; 
        }); 
    }

    $(function () {
        const modalEl = document.getElementById('employee-modal');
        const employeeModal = modalEl ? new bootstrap.Modal(modalEl) : null;
        let selectsLoaded = false;

        // Helper to select elements with brackets in IDs
        function getById(id) {
            return $('[id="' + id + '"]');
        }

        function clearForm() {
            console.log('🧹 Limpiando formulario de empleado...');
            
            $('#employee_employee_code').val('');
            $('#employee-modal').find('input[type="text"], input[type="number"], input[type="date"], input[type="email"], textarea').val('');
            $('#employee-modal').find('select').val('').trigger('change');
            
            // Set defaults
            getById('employee[active]').val('Y');
            getById('employee[employee_status]').val('ACTIVE');
            getById('employee[documentation_complete]').val('N');
            
            // Clear document table
            $('#docs-table tbody').html('<tr class="no-records"><td colspan="6" class="text-center text-muted">No hay documentos registrados</td></tr>');
            
            // Uncheck roles
            $('.role-checkbox').prop('checked', false);
            
            // Clear validations
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            // Go to general tab
            $('#employee-tabs button[data-bs-target="#pill-general"]').tab('show');
            
            console.log('✅ Formulario de empleado limpiado');
        }

        function updateRowIndexes() {
            $('#docs-table tbody tr:not(.no-records)').each(function (i, tr) {
                $(tr).find('td:first').text(i + 1);
                $(tr).find('input,select,textarea').each(function () {
                    const name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/documents\[\d+\]/, 'documents[' + i + ']'));
                    }
                });
            });
        }

        function addDocRow(data) {
            const i = $('#docs-table tbody tr:not(.no-records)').length;
            const doc = data || {};
            
            // Remove empty message row
            $('#docs-table tbody tr.no-records').remove();
            
            const rowId = 'doc-row-' + Date.now() + '-' + i;
            const $tr = $('<tr>').attr('id', rowId).attr('data-row-index', i);
            
            // 1. Column #
            $tr.append($('<td class="text-center align-middle">').text(i + 1));
            
            // 2. Column Document Type (Select)
            const $tdType = $('<td>');
            const $selectType = $('<select>')
                .attr('name', 'documents[' + i + '][document_type_code]')
                .addClass('form-control form-control-sm doc-type-select')
                .attr('required', true);
            
            $selectType.append($('<option>').val('').text('-- Seleccionar --'));
            
            if (window.docTypeOptions && window.docTypeOptions.length > 0) {
                window.docTypeOptions.forEach(function(opt) {
                    const $opt = $('<option>').val(opt.code).text(opt.name);
                    if (doc.document_type_code === opt.code) {
                        $opt.prop('selected', true);
                    }
                    $selectType.append($opt);
                });
            }
            
            $tdType.append($selectType);
            $tr.append($tdType);
            
            // 3. Column Delivered (Select Y/N)
            const $tdDelivered = $('<td class="text-center align-middle">');
            const $selectDelivered = $('<select>')
                .attr('name', 'documents[' + i + '][delivered]')
                .addClass('form-control form-control-sm');
            $selectDelivered.append($('<option>').val('Y').text('Sí'));
            $selectDelivered.append($('<option>').val('N').text('No'));
            
            if (doc.delivered) {
                $selectDelivered.val(doc.delivered);
            } else {
                $selectDelivered.val('N');
            }
            
            $tdDelivered.append($selectDelivered);
            $tr.append($tdDelivered);
            
            // 4. Column Expiration Date (Date Input)
            $tr.append($('<td>').html(
                '<input type="date" name="documents[' + i + '][expiration_date]" ' +
                'class="form-control form-control-sm" value="' + esc(doc.expiration_date) + '">'
            ));
            
            // 5. Column Notes (Text Input)
            $tr.append($('<td>').html(
                '<input type="text" name="documents[' + i + '][notes]" ' +
                'class="form-control form-control-sm" value="' + esc(doc.notes) + '" ' +
                'placeholder="Observaciones">'
            ));
            
            // 6. Column Action (Delete Button)
            const $tdAction = $('<td class="text-center">');
            const $btnDelete = $('<button type="button" class="btn btn-sm btn-danger btn-remove-doc" title="Eliminar">' +
                '<i class="ti ti-trash"></i></button>');
            $tdAction.append($btnDelete);
            $tr.append($tdAction);
            
            $('#docs-table tbody').append($tr);
        }

        function populateSelect(selectId, listData) {
            const $select = getById(selectId);
            if (!$select.length) return;
            
            const currentVal = $select.val();
            const firstOption = $select.find('option:first').clone();
            
            $select.empty().append(firstOption);
            
            if (Array.isArray(listData)) {
                listData.forEach(function(item) {
                    $select.append($('<option>').val(item.code).text(item.name));
                });
            }
            
            if (currentVal) {
                $select.val(currentVal);
            }
        }

        function loadSelectOptions() {
            const cfg = window.employeeConfig || {};
            if (!cfg.getFormOptions) {
                console.error('❌ ERROR: getFormOptions endpoint not configured');
                return;
            }

            $.get(cfg.getFormOptions)
                .done(function (resp) {
                    if (resp && resp.Success === 'Ok' && resp.Data) {
                        const d = resp.Data;
                        
                        // Populate Main Selects
                        populateSelect('employee[position_code]', d.positions);
                        populateSelect('employee[area_code]', d.areas);
                        populateSelect('employee[branch_code]', d.branches);
                        populateSelect('employee[employee_type_code]', d.employee_types);
                        populateSelect('employee[direct_manager_code]', d.managers);
                        populateSelect('employee[user_id]', d.users);
                        
                        // Save Doc Types for dynamic rows
                        window.docTypeOptions = d.document_types || [];
                        
                        // Render Roles checkboxes
                        const $rolesContainer = $('#roles-container');
                        $rolesContainer.empty();
                        if (d.roles && d.roles.length > 0) {
                            d.roles.forEach(function(role) {
                                const $col = $('<div class="col-md-4 col-sm-6">');
                                const $check = $('<div class="form-check form-check-inline mb-2">');
                                const $checkbox = $('<input>')
                                    .addClass('form-check-input role-checkbox')
                                    .attr('type', 'checkbox')
                                    .attr('id', 'role_' + role.code)
                                    .val(role.code);
                                const $label = $('<label>')
                                    .addClass('form-check-label')
                                    .attr('for', 'role_' + role.code)
                                    .text(role.name);
                                
                                $check.append($checkbox).append($label);
                                $col.append($check);
                                $rolesContainer.append($col);
                            });
                        }
                        
                        console.log('✅ Form options loaded successfully');
                    } else {
                        console.error('❌ Failed to load form options:', resp);
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('❌ getFormOptions AJAX failed:', error);
                });
        }

        function ensureSelectsLoaded(callback) {
            if (!selectsLoaded) {
                loadSelectOptions();
                selectsLoaded = true;
                if (callback) {
                    // Small delay to ensure rendering of roles/selects
                    setTimeout(callback, 300);
                }
            } else if (callback) {
                callback();
            }
        }

        // Initialize when document is ready
        $(document).ready(function() {
            ensureSelectsLoaded();
        });

        // Trigger on click Add Employee
        $('#btn-add-employee').on('click', function () {
            ensureSelectsLoaded(function() {
                clearForm();
                $('#employee-modal-label').html('<i class="fa-solid fa-user-tie me-2"></i>Datos Maestros del Empleado');
                getById('employee[employee_code]').prop('readonly', false);
                if (employeeModal) employeeModal.show();
            });
        });

        // Add Document row
        $('#btn-add-doc').on('click', function (e) {
            e.preventDefault();
            addDocRow();
        });

        // Remove Document row
        $('#docs-table').on('click', '.btn-remove-doc', function () {
            $(this).closest('tr').remove();
            updateRowIndexes();
            
            if ($('#docs-table tbody tr').length === 0) {
                $('#docs-table tbody').html('<tr class="no-records"><td colspan="6" class="text-center text-muted">No hay documentos registrados</td></tr>');
            }
        });

        function markInvalid($el, msg) { 
            $el.addClass('is-invalid'); 
            $el.next('.invalid-feedback').text(msg || 'Campo requerido'); 
        }

        // Form Submit
        $('#employee-form').on('submit', function(e) {
            e.preventDefault();
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            let valid = true;
            const $code = getById('employee[employee_code]');
            const $firstName = getById('employee[first_name]');
            const $lastName = getById('employee[last_name]');
            const $hireDate = getById('employee[hire_date]');
            const $position = getById('employee[position_code]');
            const $area = getById('employee[area_code]');
            const $status = getById('employee[employee_status]');

            if (!$code.val() || !$code.val().trim()) { markInvalid($code, 'Código de empleado requerido'); valid = false; }
            if (!$firstName.val() || !$firstName.val().trim()) { markInvalid($firstName, 'Nombre es requerido'); valid = false; }
            if (!$lastName.val() || !$lastName.val().trim()) { markInvalid($lastName, 'Apellido Paterno es requerido'); valid = false; }
            if (!$hireDate.val()) { markInvalid($hireDate, 'Fecha de Ingreso es requerida'); valid = false; }
            if (!$position.val()) { markInvalid($position, 'Puesto es requerido'); valid = false; }
            if (!$area.val()) { markInvalid($area, 'Área es requerida'); valid = false; }
            if (!$status.val()) { markInvalid($status, 'Estatus es requerido'); valid = false; }

            if (!valid) {
                // Focus first error page tab
                if (!$code.val() || !$firstName.val() || !$lastName.val()) {
                    $('#employee-tabs button[data-bs-target="#pill-general"]').tab('show');
                } else {
                    $('#employee-tabs button[data-bs-target="#pill-laboral"]').tab('show');
                }
                return;
            }

            // Gather Employee main data
            const employee = {};
            $('#employee-form').find('[name^="employee["]').each(function () {
                const nm = $(this).attr('name').replace(/^employee\[(.*)\]$/, '$1');
                employee[nm] = $(this).val();
            });

            // Gather Documents
            const documents = [];
            $('#docs-table tbody tr:not(.no-records)').each(function(idx) {
                const doc = {};
                $(this).find('input, select').each(function() {
                    const nameAttr = $(this).attr('name');
                    if (nameAttr) {
                        const m = nameAttr.match(/documents\[\d+\]\[(.+)\]/);
                        if (m) {
                            doc[m[1]] = $(this).val();
                        }
                    }
                });
                if (Object.keys(doc).length > 0) {
                    documents.push(doc);
                }
            });

            // Gather Roles
            const roles = [];
            $('.role-checkbox:checked').each(function() {
                roles.push($(this).val());
            });

            // Setup CSRF
            const csrfParam = $('meta[name="csrf-param"]').attr('content');
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            const payload = {
                employee: employee,
                documents: documents,
                roles: roles
            };

            if (csrfParam && csrfToken) {
                payload[csrfParam] = csrfToken;
            }

            const cfg = window.employeeConfig || {};
            if (cfg.save) {
                const $btn = $('#btn-save-employee');
                $btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin me-1"></i>Guardando...');

                $.ajax({
                    url: cfg.save,
                    type: 'POST',
                    data: payload,
                    dataType: 'json'
                })
                .done(function(resp) {
                    if (resp && resp.Success === 'Ok') {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(resp.Msg || 'Empleado guardado correctamente', 'Éxito');
                        } else {
                            alert(resp.Msg || 'Empleado guardado correctamente');
                        }
                        
                        if (employeeModal) employeeModal.hide();
                        
                        // Reload Grid
                        if (window.tbl_employees) {
                            window.tbl_employees.ajax.reload(null, false);
                        } else {
                            location.reload();
                        }
                    } else {
                        const errorMsg = resp && resp.Msg ? resp.Msg : 'Error al guardar';
                        if (typeof toastr !== 'undefined') {
                            toastr.error(errorMsg, 'Error');
                        } else {
                            alert('Error: ' + errorMsg);
                        }
                    }
                })
                .fail(function(xhr) {
                    let msg = 'Error de red al guardar';
                    if (xhr.responseJSON && xhr.responseJSON.Msg) {
                        msg = xhr.responseJSON.Msg;
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error(msg, 'Error de Red');
                    } else {
                        alert(msg);
                    }
                })
                .always(function() {
                    $btn.prop('disabled', false).text('OK');
                });
            }
        });

        // Edit employee (Public API called from Grid edit hook)
        window.HRManager = window.HRManager || {};
        window.HRManager.edit = function (employeeCode) {
            console.log('✏️ EDIT MODE - Employee Code:', employeeCode);
            
            const cfg = window.employeeConfig || {};
            if (!cfg.get) {
                console.error('❌ HRManager: get endpoint not configured');
                return;
            }

            ensureSelectsLoaded(function() {
                console.log('📡 Fetching employee data...');
                $.get(cfg.get, { pk: employeeCode })
                    .done(function(resp) {
                        if (resp && resp.Success === 'Ok' && resp.Data) {
                            const e = resp.Data;
                            
                            clearForm();
                            
                            // Load Header
                            $('#employee_employee_code').val(e.employee_code || '');
                            getById('employee[employee_code]').val(e.employee_code || '').prop('readonly', true);
                            
                            // Load General Details
                            getById('employee[first_name]').val(e.first_name || '');
                            getById('employee[last_name]').val(e.last_name || '');
                            getById('employee[second_last_name]').val(e.second_last_name || '');
                            getById('employee[birth_date]').val(e.birth_date || '');
                            getById('employee[gender]').val(e.gender || '');
                            getById('employee[curp]').val(e.curp || '');
                            getById('employee[phone_number]').val(e.phone_number || '');
                            getById('employee[email]').val(e.email || '');
                            getById('employee[address]').val(e.address || '');
                            
                            // Load Laboral Details
                            getById('employee[hire_date]').val(e.hire_date || '');
                            getById('employee[position_code]').val(e.position_code || '').trigger('change');
                            getById('employee[area_code]').val(e.area_code || '').trigger('change');
                            getById('employee[branch_code]').val(e.branch_code || '').trigger('change');
                            getById('employee[employee_type_code]').val(e.employee_type_code || '').trigger('change');
                            getById('employee[direct_manager_code]').val(e.direct_manager_code || '').trigger('change');
                            getById('employee[user_id]').val(e.user_id || '').trigger('change');
                            getById('employee[shift_type]').val(e.shift_type || '');
                            getById('employee[employee_status]').val(e.employee_status || 'ACTIVE');
                            getById('employee[documentation_complete]').val(e.documentation_complete || 'N');
                            getById('employee[active]').val(e.active || 'Y');
                            getById('employee[notes]').val(e.notes || '');

                            // Load Documents
                            $('#docs-table tbody').empty();
                            if (e.documents && e.documents.length > 0) {
                                e.documents.forEach(function(d) {
                                    addDocRow(d);
                                });
                            } else {
                                $('#docs-table tbody').html('<tr class="no-records"><td colspan="6" class="text-center text-muted">No hay documentos registrados</td></tr>');
                            }

                            // Load Roles
                            $('.role-checkbox').prop('checked', false);
                            if (e.roles && e.roles.length > 0) {
                                e.roles.forEach(function(r) {
                                    $('#role_' + r.role_code).prop('checked', true);
                                });
                            }

                            $('#employee-modal-label').html('<i class="fa-solid fa-user-tie me-2"></i>Editar Empleado: ' + esc(e.employee_code));
                            if (employeeModal) employeeModal.show();
                        } else {
                            alert('Error al cargar datos del empleado: ' + (resp.Msg || 'Formato de respuesta inválido'));
                        }
                    })
                    .fail(function() {
                        alert('Error de red al cargar el empleado.');
                    });
            });
        };

        // Capture grid edit click event (Native Phase)
        const tableContainer = document.getElementById('tbl-employees');
        if (tableContainer) {
            tableContainer.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.dt-btn-action[data-action="edit"]');
                if (editBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    const pk = editBtn.getAttribute('data-pk');
                    if (pk && window.HRManager && window.HRManager.edit) {
                        window.HRManager.edit(pk);
                    }
                    return false;
                }
            }, true);
        } else {
            console.error('❌ Table #tbl-employees not found for capturing edits');
        }

    });

})();
