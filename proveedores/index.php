<?php
include ('../app/config.php'); // $URL, $pdo, $fechaHora
include ('../app/utils/funciones_globales.php'); // Para sanear()
// No es necesario incluir ProveedorModel aquí si los controladores AJAX lo hacen.
// CategoriaModel no es necesario para proveedores.

include ('../layout/sesion.php'); // Verifica sesión, establece $id_usuario_sesion, $nombres_sesion, etc.
include ('../layout/parte1.php'); // Cabecera HTML, CSS, y menú

// Controlador para listar proveedores (ya filtra por usuario)
include ('../app/controllers/proveedores/listado_de_proveedores.php'); // Define $proveedores_datos

$modulo_abierto = 'proveedores'; // Para el menú lateral (si tienes esta variable)
$pagina_activa = 'proveedores_listado'; // Para resaltar en el menú (si tienes esta variable)
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

    <!-- MODAL GENÉRICO PARA CREAR/ACTUALIZAR PROVEEDOR -->
    <div class="modal fade" id="modal-proveedor-form" tabindex="-1" role="dialog" aria-labelledby="modalProveedorFormLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProveedorFormLabel">Agregar Nuevo Proveedor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="form-proveedor" novalidate> <!-- AÑADIDO novalidate AQUÍ -->
                <input type="hidden" id="id_proveedor_form" name="id_proveedor_update">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="nombre_proveedor_form">Nombre Proveedor <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre_proveedor_form" name="nombre_proveedor_update" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="celular_form">Celular <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="celular_form" name="celular_update" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="telefono_form">Teléfono</label>
                            <input type="text" class="form-control" id="telefono_form" name="telefono_update">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="empresa_form">Empresa</label>
                            <input type="text" class="form-control" id="empresa_form" name="empresa_update">
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-6 form-group">
                            <label for="email_form">Email</label>
                            <input type="email" class="form-control" id="email_form" name="email_update">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="direccion_form">Dirección</label>
                            <textarea class="form-control" id="direccion_form" name="direccion_update" rows="2"></textarea>
                        </div>
                    </div>
                    <div id="error_message_form" class="alert alert-danger" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_proveedor_form">Guardar</button>
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
                    <h5 class="modal-title" id="modalDeleteProveedorLabel">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_proveedor_delete">
                    <p>¿Está seguro de eliminar al proveedor: <strong id="nombre_proveedor_delete_display"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btn_delete_confirm_proveedor">Eliminar</button>
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
                        <div class="card-header"><h3 class="card-title">Proveedores Registrados</h3></div>
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
                                                            <button type="button" class="btn btn-danger btn-xs btn-delete-proveedor" data-id="<?php echo $id_proveedor; ?>" data-nombre="<?php echo sanear($item['nombre_proveedor']); ?>" title="Eliminar">
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

<?php include ('../layout/mensajes.php'); // Para mensajes de sesión (si aún se usan para alguna redirección) ?>
<?php include ('../layout/parte2.php'); ?>

<script>
$(document).ready(function () {
    var tablaProveedores = $("#tabla_proveedores").DataTable({
        "pageLength": 5,
        "language": { /* ... tu config de idioma ... */ 
            "emptyTable": "No hay proveedores registrados",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ proveedores",
            "infoEmpty": "Mostrando 0 a 0 de 0 proveedores",
            // ... más traducciones ...
        },
        "responsive": true, "lengthChange": true, "autoWidth": false,
        "buttons": ["copy", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#tabla_proveedores_wrapper .col-md-6:eq(0)');

    function mostrarAlerta(title, text, icon, callback) {
        Swal.fire({
            title: title, text: text, icon: icon,
            timer: icon === 'success' ? 2500 : 4000,
            showConfirmButton: icon !== 'success',
            allowOutsideClick: false, allowEscapeKey: false
        }).then((result) => {
            if (callback && typeof callback === 'function') {
                callback();
            }
        });
    }

    function recargarPagina() { 
        location.reload(); 
    }

    // --- Lógica para ABRIR MODAL (Crear o Editar) ---
    $('[data-target="#modal-proveedor-form"]').click(function() { // Botón "Agregar Nuevo Proveedor"
        $('#form-proveedor')[0].reset();
        $('#id_proveedor_form').val(''); // Asegurar que no hay ID para modo creación
        $('#modalProveedorFormLabel').text('Agregar Nuevo Proveedor');
        $('#btn_submit_proveedor_form').text('Guardar').removeClass('btn-success').addClass('btn-primary');
        $('#error_message_form').hide();
        // El modal se abre por data-toggle="modal" data-target="..."
    });

    $('#tabla_proveedores tbody').on('click', '.btn-edit-proveedor', function () {
        var id_proveedor = $(this).data('id');
        $('#form-proveedor')[0].reset();
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
                
                $('#modalProveedorFormLabel').text('Actualizar Proveedor');
                $('#btn_submit_proveedor_form').text('Actualizar').removeClass('btn-primary').addClass('btn-success');
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
        $('#id_proveedor_form').val('');
        $('#error_message_form').hide().text('');
        $('#modalProveedorFormLabel').text('Agregar Nuevo Proveedor');
        $('#btn_submit_proveedor_form').text('Guardar').removeClass('btn-success').addClass('btn-primary');
    });


    // --- Lógica para ENVIAR FORMULARIO (Crear o Actualizar) ---
    $('#form-proveedor').submit(function (e) {
        e.preventDefault();
        $('#error_message_form').hide();
        var formData = $(this).serializeArray(); 
        var id_proveedor = $('#id_proveedor_form').val();
        var url = id_proveedor ? "../app/controllers/proveedores/update.php" : "../app/controllers/proveedores/create.php";
        
        // Convertir serializeArray a un objeto para pasarlo a $.post
        var postData = {};
        $.each(formData, function(i, field){
            // Si es para actualizar, y el campo ya tiene '_update', lo conservamos.
            // Si es para crear, o si el campo no tiene '_update' (ej. id_proveedor_form), lo limpiamos.
            // El controlador create.php espera los nombres sin sufijos.
            // El controlador update.php espera los nombres CON el sufijo _update.
            
            if (id_proveedor) { // Es una ACTUALIZACIÓN
                // Para la actualización, el controlador update.php espera los nombres con _update
                // y también 'id_proveedor_update'.
                // El input hidden id_proveedor_form ya se llama 'id_proveedor_update'
                postData[field.name] = field.value; 
            } else { // Es una CREACIÓN
                // Para la creación, el controlador create.php espera los nombres sin _update
                var key = field.name.replace('_form', '').replace('_update', '');
                postData[key] = field.value;
            }
        });


        $.ajax({
            url: url,
            type: "POST", 
            data: postData,
            dataType: "json",
            success: function(response) {
                if (response.status === 'success') {
                    $('#modal-proveedor-form').modal('hide');
                    mostrarAlerta('¡Éxito!', response.message, 'success', function() {
                        recargarPagina();
                    });
                } else {
                    $('#error_message_form').text(response.message || 'Error desconocido.').show();
                }
                if (response.redirectTo) {
                     mostrarAlerta('Sesión Expirada', response.message, 'warning', function() {
                        window.location.href = response.redirectTo;
                    });
                }
            },
            error: function() {
                $('#error_message_form').text('Error de conexión con el servidor.').show();
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
        $.post("../app/controllers/proveedores/delete.php", { id_proveedor: id_proveedor_val }, function (response) {
            $('#modal-delete-proveedor').modal('hide');
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
            mostrarAlerta('Error de Conexión', 'No se pudo contactar al servidor.', 'error');
        });
    });
});
</script>