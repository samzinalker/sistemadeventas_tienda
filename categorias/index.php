<?php
include ('../app/config.php');
// Es crucial que funciones_globales.php y Validator.php se incluyan ANTES de sesion.php si este las usa.
// Y también antes de listado_de_categoria.php si este las usa.
include ('../app/utils/funciones_globales.php'); // Para sanear() y setMensaje()
include ('../app/utils/Validator.php');       // Si alguna lógica lo requiere aquí directamente
include ('../layout/sesion.php');             // Verifica y establece datos de sesión
include ('../layout/parte1.php');             // Cabecera HTML y menú

// Este controlador ahora debe usar CategoriaModel y obtener solo las categorías del usuario en sesión.
include ('../app/controllers/categorias/listado_de_categoria.php'); 
                                               // Define $categorias_datos
$modulo_abierto = 'categorias'; // Para el menú lateral
$pagina_activa = 'categorias_listado'; // Para resaltar en el menú
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Listado de Mis Categorías
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-create">
                           <i class="fa fa-plus"></i> Agregar Nueva
                        </button>
                    </h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- modal para registrar categorias -->
    <div class="modal fade" id="modal-create">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #1d36b6;color: white">
                    <h4 class="modal-title">Creación de una nueva categoría</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombre_categoria_create">Nombre de la categoría <b>*</b></label>
                        <input type="text" id="nombre_categoria_create" class="form-control">
                        <small style="color: red;display: none" id="lbl_create_error">Este campo es requerido</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn_create_categoria">Guardar categoría</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.modal -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-9"> {/* Ajustado para ocupar más espacio si es necesario */}
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Mis Categorías Registradas</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body" style="display: block;">
                            <table id="tabla_categorias" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th><center>Nro</center></th>
                                    <th><center>Nombre de la categoría</center></th>
                                    <th><center>Fecha Creación</center></th>
                                    <th><center>Acciones</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador = 0;
                                if (!empty($categorias_datos)) {
                                    foreach ($categorias_datos as $categorias_dato){
                                        $id_categoria = $categorias_dato['id_categoria'];
                                        // Asegurarse de que nombre_categoria y fyh_creacion existan y sanearlos
                                        $nombre_categoria = isset($categorias_dato['nombre_categoria']) ? sanear($categorias_dato['nombre_categoria']) : 'N/A';
                                        $fyh_creacion_raw = isset($categorias_dato['fyh_creacion']) ? $categorias_dato['fyh_creacion'] : null;
                                        $fyh_creacion_formateada = $fyh_creacion_raw ? date('d/m/Y H:i:s', strtotime($fyh_creacion_raw)) : 'N/A';
                                        ?>
                                        <tr id="fila_categoria_<?php echo $id_categoria; ?>">
                                            <td><center><?php echo ++$contador; ?></center></td>
                                            <td><?php echo $nombre_categoria; ?></td>
                                            <td><center><?php echo $fyh_creacion_formateada; ?></center></td>
                                            <td>
                                                <center>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-success btn-sm btn-edit" 
                                                                data-id="<?php echo $id_categoria;?>"
                                                                data-nombre="<?php echo $nombre_categoria; ?>"
                                                                data-toggle="modal"
                                                                data-target="#modal-update">
                                                            <i class="fa fa-pencil-alt"></i> Editar
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm btn-delete" 
                                                                data-id="<?php echo $id_categoria;?>"
                                                                data-nombre="<?php echo $nombre_categoria; ?>"
                                                                data-toggle="modal"
                                                                data-target="#modal-delete">
                                                            <i class="fa fa-trash"></i> Eliminar
                                                        </button>
                                                    </div>
                                                </center>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="4"><center>No tienes categorías registradas.</center></td></tr>';
                                }
                                ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th><center>Nro</center></th>
                                    <th><center>Nombre de la categoría</center></th>
                                    <th><center>Fecha Creación</center></th>
                                    <th><center>Acciones</center></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- Modal para actualizar categorías (un solo modal) -->
<div class="modal fade" id="modal-update">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #116f4a;color: white">
                <h4 class="modal-title">Actualización de la categoría</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id_categoria_update">
                <div class="form-group">
                    <label for="nombre_categoria_update">Nombre de la categoría</label>
                    <input type="text" id="nombre_categoria_update" class="form-control">
                    <small style="color: red;display: none" id="lbl_update_error">Este campo es requerido</small>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn_update_categoria">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para eliminar categoría (un solo modal) -->
<div class="modal fade" id="modal-delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title">Confirmar Eliminación</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id_categoria_delete">
                <p>¿Está seguro de eliminar la categoría: <strong id="nombre_categoria_delete_display"></strong>?</p>
                <p>Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn_delete_confirm_categoria">Eliminar</button>
            </div>
        </div>
    </div>
</div>


<?php include ('../layout/mensajes.php'); // Para mostrar mensajes de sesión si la página se recarga ?>
<?php include ('../layout/parte2.php'); // Scripts JS generales, cierre de HTML ?>

<script>
$(document).ready(function () {
    var tablaCategorias = $("#tabla_categorias").DataTable({
        "pageLength": 5,
        "language": {
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ Categorías",
            "infoEmpty": "Mostrando 0 a 0 de 0 Categorías",
            "infoFiltered": "(Filtrado de _MAX_ total Categorías)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ Categorías",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscador:",
            "zeroRecords": "Sin resultados encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "responsive": true, "lengthChange": true, "autoWidth": false,
        "buttons": [
            { extend: 'copy', text: 'Copiar', exportOptions: { columns: [0, 1, 2] }},
            { extend: 'csv', text: 'CSV', exportOptions: { columns: [0, 1, 2] }},
            { extend: 'excel', text: 'Excel', exportOptions: { columns: [0, 1, 2] }},
            { extend: 'pdf', text: 'PDF', exportOptions: { columns: [0, 1, 2] }, orientation: 'portrait', pageSize: 'A4', customize: function (doc) { doc.defaultStyle.fontSize = 10; doc.styles.tableHeader.fontSize = 12; doc.content[1].table.widths = ['10%','60%','30%'];}},
            { extend: 'print', text: 'Imprimir', exportOptions: { columns: [0, 1, 2] }},
            { extend: 'colvis', text: 'Visibilidad Columnas'}
        ]
    }).buttons().container().appendTo('#tabla_categorias_wrapper .col-md-6:eq(0)');

    // Función para mostrar alertas de SweetAlert
    function mostrarAlerta(title, text, icon) {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            timer: icon === 'success' ? 1500 : 3000,
            showConfirmButton: icon !== 'success'
        });
    }
    
    // Función para recargar DataTables (o la página completa como fallback)
    function recargarTabla() {
        // Idealmente, se recarga solo DataTables:
        // tablaCategorias.ajax.reload(null, false); // Si usas carga AJAX para DataTables
        // Como fallback simple, recargamos la página:
        location.reload();
    }

    // Crear Categoría
    $('#btn_create_categoria').click(function () {
        var nombre_categoria = $('#nombre_categoria_create').val().trim();
        $('#lbl_create_error').hide();

        if (nombre_categoria === "") {
            $('#nombre_categoria_create').focus();
            $('#lbl_create_error').text('Este campo es requerido').show();
            return;
        }

        $.post("../app/controllers/categorias/registro_de_categorias.php", { nombre_categoria: nombre_categoria })
            .done(function (response) {
                if (response.status === 'success') {
                    $('#modal-create').modal('hide');
                    mostrarAlerta('¡Éxito!', response.message, 'success');
                    recargarTabla(); 
                } else if (response.status === 'warning') {
                    // Mostrar advertencia dentro del modal si es por nombre duplicado, etc.
                    $('#lbl_create_error').text(response.message).show();
                     mostrarAlerta('Advertencia', response.message, 'warning');
                } else {
                    mostrarAlerta('Error', response.message || 'No se pudo crear la categoría.', 'error');
                }
                 if (response.redirectTo) { // Si la sesión expiró
                    window.location.href = response.redirectTo;
                }
            })
            .fail(function () {
                mostrarAlerta('Error de Conexión', 'No se pudo contactar al servidor.', 'error');
            });
    });

    // Editar Categoría - Cargar datos en el modal
    $('#tabla_categorias tbody').on('click', '.btn-edit', function () {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        $('#id_categoria_update').val(id);
        $('#nombre_categoria_update').val(nombre);
        $('#lbl_update_error').hide();
    });

    // Actualizar Categoría
    $('#btn_update_categoria').click(function () {
        var id_categoria = $('#id_categoria_update').val();
        var nombre_categoria = $('#nombre_categoria_update').val().trim();
        $('#lbl_update_error').hide();

        if (nombre_categoria === "") {
            $('#nombre_categoria_update').focus();
            $('#lbl_update_error').text('Este campo es requerido').show();
            return;
        }

        $.post("../app/controllers/categorias/update_de_categorias.php", { id_categoria: id_categoria, nombre_categoria: nombre_categoria })
            .done(function (response) {
                if (response.status === 'success') {
                    $('#modal-update').modal('hide');
                    mostrarAlerta('¡Éxito!', response.message, 'success');
                    recargarTabla();
                } else if (response.status === 'warning') {
                    $('#lbl_update_error').text(response.message).show();
                    mostrarAlerta('Advertencia', response.message, 'warning');
                } else {
                     mostrarAlerta('Error', response.message || 'No se pudo actualizar la categoría.', 'error');
                }
                 if (response.redirectTo) {
                    window.location.href = response.redirectTo;
                }
            })
            .fail(function () {
                mostrarAlerta('Error de Conexión', 'No se pudo contactar al servidor.', 'error');
            });
    });

    // Eliminar Categoría - Cargar datos en el modal
    $('#tabla_categorias tbody').on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        $('#id_categoria_delete').val(id);
        $('#nombre_categoria_delete_display').text(nombre);
    });

    // Confirmar Eliminación
    $('#btn_delete_confirm_categoria').click(function () {
        var id_categoria = $('#id_categoria_delete').val();

        $.post("../app/controllers/categorias/delete_de_categorias.php", { id_categoria: id_categoria })
            .done(function (response) {
                $('#modal-delete').modal('hide');
                if (response.status === 'success') {
                    mostrarAlerta('¡Eliminado!', response.message, 'success');
                    recargarTabla();
                } else {
                    mostrarAlerta('Error', response.message || 'No se pudo eliminar la categoría.', response.status || 'error');
                }
                 if (response.redirectTo) {
                    window.location.href = response.redirectTo;
                }
            })
            .fail(function () {
                $('#modal-delete').modal('hide');
                mostrarAlerta('Error de Conexión', 'No se pudo contactar al servidor.', 'error');
            });
    });
    
    // Limpiar campos de modales al cerrarlos
    $('#modal-create').on('hidden.bs.modal', function () {
        $('#nombre_categoria_create').val('');
        $('#lbl_create_error').hide().text('');
    });
    $('#modal-update').on('hidden.bs.modal', function () {
        $('#nombre_categoria_update').val('');
        $('#lbl_update_error').hide().text('');
    });

});
</script>