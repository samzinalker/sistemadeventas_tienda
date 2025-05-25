<?php
include ('../app/config.php');
include ('../app/utils/funciones_globales.php');
include ('../layout/sesion.php');
include ('../layout/parte1.php');
include ('../app/controllers/proveedores/listado_de_proveedores.php');

$modulo_abierto = 'proveedores';
$pagina_activa = 'proveedores_listado';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Listado de Proveedores
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-proveedor-form">
                           <i class="fa fa-plus"></i> Agregar Nuevo Proveedor
                        </button>
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL MEJORADO PARA CREAR/ACTUALIZAR PROVEEDOR -->
    <div class="modal fade" id="modal-proveedor-form" tabindex="-1" role="dialog" aria-labelledby="modalProveedorFormLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalProveedorFormLabel">
                        <i class="fas fa-plus-circle"></i> Agregar Nuevo Proveedor
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form-proveedor" novalidate>
                    <input type="hidden" id="id_proveedor_form" name="id_proveedor_update">
                    <div class="modal-body">
                        <!-- Información Básica -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-user"></i> Información Básica
                                </h6>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="nombre_proveedor_form">
                                    <i class="fas fa-user-tie"></i> Nombre del Proveedor 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombre_proveedor_form" 
                                       name="nombre_proveedor_update" 
                                       placeholder="Ingrese el nombre completo del proveedor"
                                       required 
                                       minlength="2" 
                                       maxlength="100">
                                <div class="invalid-feedback">
                                    Por favor ingrese un nombre válido (mínimo 2 caracteres).
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="empresa_form">
                                    <i class="fas fa-building"></i> Empresa 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="empresa_form" 
                                       name="empresa_update" 
                                       placeholder="Nombre de la empresa"
                                       required 
                                       minlength="2" 
                                       maxlength="150">
                                <div class="invalid-feedback">
                                    Por favor ingrese el nombre de la empresa.
                                </div>
                            </div>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-primary mb-3 mt-3">
                                    <i class="fas fa-phone"></i> Información de Contacto
                                </h6>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="celular_form">
                                    <i class="fab fa-whatsapp text-success"></i> Celular 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="celular_form" 
                                       name="celular_update" 
                                       placeholder="Ej: +51 999 888 777"
                                       required 
                                       pattern="[+]?[0-9\s\-\(\)]{7,20}">
                                <div class="invalid-feedback">
                                    Por favor ingrese un número de celular válido.
                                </div>
                                <small class="form-text text-muted">
                                    Formato: +51 999 888 777 o 999888777
                                </small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="telefono_form">
                                    <i class="fas fa-phone"></i> Teléfono Fijo
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="telefono_form" 
                                       name="telefono_update" 
                                       placeholder="Ej: (01) 234-5678"
                                       pattern="[+]?[0-9\s\-\(\)]{7,20}">
                                <div class="invalid-feedback">
                                    Por favor ingrese un número de teléfono válido.
                                </div>
                                <small class="form-text text-muted">
                                    Campo opcional
                                </small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label for="email_form">
                                    <i class="fas fa-envelope"></i> Correo Electrónico 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email_form" 
                                       name="email_update" 
                                       placeholder="ejemplo@empresa.com"
                                       required>
                                <div class="invalid-feedback">
                                    Por favor ingrese un correo electrónico válido.
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-primary mb-3 mt-3">
                                    <i class="fas fa-map-marker-alt"></i> Información Adicional
                                </h6>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label for="direccion_form">
                                    <i class="fas fa-map-marker-alt"></i> Dirección Completa 
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="direccion_form" 
                                          name="direccion_update" 
                                          rows="3" 
                                          placeholder="Ingrese la dirección completa del proveedor"
                                          required 
                                          minlength="10" 
                                          maxlength="500"></textarea>
                                <div class="invalid-feedback">
                                    Por favor ingrese una dirección válida (mínimo 10 caracteres).
                                </div>
                                <small class="form-text text-muted">
                                    Incluya calle, número, distrito, ciudad, etc.
                                </small>
                            </div>
                        </div>

                        <!-- Alert para errores -->
                        <div id="error_message_form" class="alert alert-danger" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span id="error_text_form"></span>
                        </div>
                    </div>
                    
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn_submit_proveedor_form">
                            <i class="fas fa-save"></i> Guardar Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA ELIMINAR PROVEEDOR -->
    <div class="modal fade" id="modal-delete-proveedor" tabindex="-1" role="dialog" aria-labelledby="modalDeleteProveedorLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalDeleteProveedorLabel">
                        <i class="fas fa-trash-alt"></i> Confirmar Eliminación
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_proveedor_delete">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">¿Está seguro de eliminar este proveedor?</h5>
                        <p class="mt-3">
                            <strong>Proveedor:</strong> <span id="nombre_proveedor_delete_display" class="text-primary"></span>
                        </p>
                        <p class="text-muted">Esta acción no se puede deshacer.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="btn_delete_confirm_proveedor">
                        <i class="fas fa-trash"></i> Eliminar Proveedor
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-truck"></i> Proveedores Registrados
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabla_proveedores" class="table table-bordered table-striped table-sm">
                                    <thead>
                                    <tr>
                                        <th><center>Nro</center></th>
                                        <th>Nombre Proveedor</th>
                                        <th>Celular</th>
                                        <th>Teléfono</th>
                                        <th>Empresa</th>
                                        <th>Email</th>
                                        <th>Dirección</th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $contador_prov = 0;
                                    if (!empty($proveedores_datos)) {
                                        foreach ($proveedores_datos as $item){
                                            $id_proveedor = $item['id_proveedor'];
                                            ?>
                                            <tr id="fila_proveedor_<?php echo $id_proveedor; ?>">
                                                <td><center><?php echo ++$contador_prov; ?></center></td>
                                                <td><?php echo sanear($item['nombre_proveedor']);?></td>
                                                <td>
                                                    <?php if(!empty($item['celular'])): ?>
                                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $item['celular']);?>" target="_blank" class="btn btn-sm btn-success">
                                                        <i class="fab fa-whatsapp"></i> <?php echo sanear($item['celular']);?>
                                                    </a>
                                                    <?php else: echo 'N/A'; endif; ?>
                                                </td>
                                                <td><?php echo sanear($item['telefono'] ?: 'N/A');?></td>
                                                <td><?php echo sanear($item['empresa'] ?: 'N/A');?></td>
                                                <td><?php echo sanear($item['email'] ?: 'N/A');?></td>
                                                <td><?php echo sanear($item['direccion'] ?: 'N/A');?></td>
                                                <td>
                                                    <center>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-success btn-xs btn-edit-proveedor" data-id="<?php echo $id_proveedor; ?>" title="Editar">
                                                                <i class="fa fa-pencil-alt"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-xs btn-delete-proveedor" 
                                                                    data-id="<?php echo $id_proveedor; ?>" 
                                                                    data-nombre="<?php echo sanear($item['nombre_proveedor']); ?>" 
                                                                    title="Eliminar">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </center>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="8"><center>No tiene proveedores registrados.</center></td></tr>';
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->

<?php include ('../layout/mensajes.php'); ?>
<?php include ('../layout/parte2.php'); ?>

<script>
$(document).ready(function () {
    // Esperar a que todos los scripts estén cargados antes de inicializar DataTables
    setTimeout(function() {
        inicializarDataTable();
    }, 100);

    function inicializarDataTable() {
        // Verificar que la tabla existe antes de inicializar
        if ($('#tabla_proveedores').length === 0) {
            console.error('Tabla #tabla_proveedores no encontrada');
            return;
        }

        // Validar estructura de la tabla
        if (!validarEstructuraTabla()) {
            console.error('Error en la estructura de la tabla');
            return;
        }

        var tablaProveedores = $("#tabla_proveedores").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay proveedores registrados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ proveedores",
                "infoEmpty": "Mostrando 0 a 0 de 0 proveedores",
                "infoFiltered": "(filtrado de _MAX_ total proveedores)",
                "lengthMenu": "Mostrar _MENU_ proveedores",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron resultados",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true, 
            "lengthChange": true, 
            "autoWidth": false,
            "buttons": ["copy", "excel", "pdf", "print", "colvis"],
            "destroy": true,
            "order": [[1, 'asc']]
        }).buttons().container().appendTo('#tabla_proveedores_wrapper .col-md-6:eq(0)');
    }

    function validarEstructuraTabla() {
        var tabla = $('#tabla_proveedores');
        var filas = tabla.find('tbody tr');
        var numColumnas = tabla.find('thead th').length;
        
        var esValida = true;
        filas.each(function(index, fila) {
            var celdas = $(fila).find('td').length;
            if (celdas !== numColumnas) {
                console.error('Fila ' + index + ' tiene ' + celdas + ' celdas, esperadas: ' + numColumnas);
                esValida = false;
                return false;
            }
        });
        return esValida;
    }

    function mostrarAlerta(title, text, icon, callback) {
        Swal.fire({
            title: title, 
            text: text, 
            icon: icon,
            timer: icon === 'success' ? 2500 : 4000,
            showConfirmButton: icon !== 'success',
            allowOutsideClick: false, 
            allowEscapeKey: false
        }).then((result) => {
            if (callback && typeof callback === 'function') {
                callback();
            }
        });
    }

    function recargarPagina() { 
        location.reload(); 
    }

    // Función para validar formulario en tiempo real
    function validarCampoEnTiempoReal(input) {
        var isValid = input[0].checkValidity();
        
        if (isValid) {
            input.removeClass('is-invalid').addClass('is-valid');
        } else {
            input.removeClass('is-valid').addClass('is-invalid');
        }
        
        return isValid;
    }

    // Validación en tiempo real para todos los campos
    $('#form-proveedor input, #form-proveedor textarea').on('input blur', function() {
        validarCampoEnTiempoReal($(this));
    });

    // Validar email específicamente
    $('#email_form').on('input blur', function() {
        var email = $(this).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email === '' || emailRegex.test(email)) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });

    // Validar números de teléfono
    $('#celular_form, #telefono_form').on('input', function() {
        var value = $(this).val();
        // Permitir solo números, espacios, guiones, paréntesis y signo +
        var sanitized = value.replace(/[^0-9\s\-\(\)+]/g, '');
        $(this).val(sanitized);
        validarCampoEnTiempoReal($(this));
    });

    // --- Lógica para ABRIR MODAL (Crear o Editar) ---
    $('[data-target="#modal-proveedor-form"]').click(function() {
        $('#form-proveedor')[0].reset();
        $('#form-proveedor input, #form-proveedor textarea').removeClass('is-valid is-invalid');
        $('#id_proveedor_form').val('');
        $('#modalProveedorFormLabel').html('<i class="fas fa-plus-circle"></i> Agregar Nuevo Proveedor');
        $('#btn_submit_proveedor_form').html('<i class="fas fa-save"></i> Guardar Proveedor').removeClass('btn-success').addClass('btn-primary');
        $('#error_message_form').hide();
    });

    $('#tabla_proveedores tbody').on('click', '.btn-edit-proveedor', function () {
        var id_proveedor = $(this).data('id');
        $('#form-proveedor')[0].reset();
        $('#form-proveedor input, #form-proveedor textarea').removeClass('is-valid is-invalid');
        $('#error_message_form').hide();

        $.get("../app/controllers/proveedores/get_proveedor.php", { id_proveedor: id_proveedor }, function(response) {
            if (response.status === 'success' && response.data) {
                $('#id_proveedor_form').val(response.data.id_proveedor);
                $('#nombre_proveedor_form').val(response.data.nombre_proveedor);
                $('#celular_form').val(response.data.celular);
                $('#telefono_form').val(response.data.telefono);
                $('#empresa_form').val(response.data.empresa);
                $('#email_form').val(response.data.email);
                $('#direccion_form').val(response.data.direccion);
                
                // Marcar todos los campos como válidos después de cargar datos
                $('#form-proveedor input, #form-proveedor textarea').each(function() {
                    if ($(this).val()) {
                        $(this).addClass('is-valid');
                    }
                });
                
                $('#modalProveedorFormLabel').html('<i class="fas fa-edit"></i> Actualizar Proveedor');
                $('#btn_submit_proveedor_form').html('<i class="fas fa-sync-alt"></i> Actualizar Proveedor').removeClass('btn-primary').addClass('btn-success');
                $('#modal-proveedor-form').modal('show');
            } else {
                mostrarAlerta('Error', response.message || 'No se pudo cargar datos del proveedor.', 'error');
            }
        }, "json").fail(function() {
            mostrarAlerta('Error de Conexión', 'No se pudo obtener datos del proveedor.', 'error');
        });
    });
    
    // Limpiar modal al cerrarse
    $('#modal-proveedor-form').on('hidden.bs.modal', function () {
        $('#form-proveedor')[0].reset();
        $('#form-proveedor input, #form-proveedor textarea').removeClass('is-valid is-invalid');
        $('#id_proveedor_form').val('');
        $('#error_message_form').hide().text('');
        $('#modalProveedorFormLabel').html('<i class="fas fa-plus-circle"></i> Agregar Nuevo Proveedor');
        $('#btn_submit_proveedor_form').html('<i class="fas fa-save"></i> Guardar Proveedor').removeClass('btn-success').addClass('btn-primary');
    });

    // --- Lógica para ENVIAR FORMULARIO (Crear o Actualizar) ---
    $('#form-proveedor').submit(function (e) {
        e.preventDefault();
        $('#error_message_form').hide();

        // Validar todos los campos antes de enviar
        var formularioValido = true;
        $('#form-proveedor input[required], #form-proveedor textarea[required]').each(function() {
            if (!validarCampoEnTiempoReal($(this))) {
                formularioValido = false;
            }
        });

        if (!formularioValido) {
            $('#error_text_form').text('Por favor complete todos los campos requeridos correctamente.');
            $('#error_message_form').show();
            return;
        }

        var formData = $(this).serializeArray(); 
        var id_proveedor = $('#id_proveedor_form').val();
        var url = id_proveedor ? "../app/controllers/proveedores/update.php" : "../app/controllers/proveedores/create.php";
        
        var postData = {};
        $.each(formData, function(i, field){
            if (id_proveedor) {
                postData[field.name] = field.value; 
            } else {
                var key = field.name.replace('_form', '').replace('_update', '');
                postData[key] = field.value;
            }
        });

        // Deshabilitar botón de envío
        $('#btn_submit_proveedor_form').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        $.ajax({
            url: url,
            type: "POST", 
            data: postData,
            dataType: "json",
            success: function(response) {
                $('#btn_submit_proveedor_form').prop('disabled', false);
                
                if (response.status === 'success') {
                    $('#modal-proveedor-form').modal('hide');
                    mostrarAlerta('¡Éxito!', response.message, 'success', function() {
                        recargarPagina();
                    });
                } else {
                    $('#btn_submit_proveedor_form').html(id_proveedor ? '<i class="fas fa-sync-alt"></i> Actualizar Proveedor' : '<i class="fas fa-save"></i> Guardar Proveedor');
                    $('#error_text_form').text(response.message || 'Error desconocido.');
                    $('#error_message_form').show();
                }
                
                if (response.redirectTo) {
                     mostrarAlerta('Sesión Expirada', response.message, 'warning', function() {
                        window.location.href = response.redirectTo;
                    });
                }
            },
            error: function() {
                $('#btn_submit_proveedor_form').prop('disabled', false)
                    .html(id_proveedor ? '<i class="fas fa-sync-alt"></i> Actualizar Proveedor' : '<i class="fas fa-save"></i> Guardar Proveedor');
                $('#error_text_form').text('Error de conexión con el servidor.');
                $('#error_message_form').show();
            }
        });
    });

    // --- Lógica para ELIMINAR Proveedor ---
    $('#tabla_proveedores tbody').on('click', '.btn-delete-proveedor', function () {
        $('#id_proveedor_delete').val($(this).data('id'));
        $('#nombre_proveedor_delete_display').text($(this).data('nombre'));
        $('#modal-delete-proveedor').modal('show');
    });

    $('#btn_delete_confirm_proveedor').click(function () {
        var id_proveedor_val = $('#id_proveedor_delete').val();
        
        // Deshabilitar botón mientras se procesa
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Eliminando...');
        
        $.post("../app/controllers/proveedores/delete.php", { id_proveedor: id_proveedor_val }, function (response) {
            $('#modal-delete-proveedor').modal('hide');
            $('#btn_delete_confirm_proveedor').prop('disabled', false).html('<i class="fas fa-trash"></i> Eliminar Proveedor');
            
            if (response.status === 'success') {
                mostrarAlerta('¡Eliminado!', response.message, 'success', function() {
                    recargarPagina();
                });
            } else {
                mostrarAlerta(response.status === 'warning' ? 'Advertencia' : 'Error', response.message || 'No se pudo eliminar.', response.status || 'error');
            }
            if (response.redirectTo) {
                 mostrarAlerta('Sesión Expirada', response.message, 'warning', function() {
                    window.location.href = response.redirectTo;
                });
            }
        }, "json").fail(function() {
            $('#modal-delete-proveedor').modal('hide');
            $('#btn_delete_confirm_proveedor').prop('disabled', false).html('<i class="fas fa-trash"></i> Eliminar Proveedor');
            mostrarAlerta('Error de Conexión', 'No se pudo contactar al servidor.', 'error');
        });
    });
});
</script>