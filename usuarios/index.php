<?php
// Incluir configuraciones y sesión primero
include('../app/config.php'); 
include('../layout/sesion.php'); 
// Auth::requireAdmin($URL); // O tu sistema de permisos
include('../layout/permisos.php'); // Si manejas permisos aquí

// Título y módulo (esto puede variar)
$titulo_pagina = 'Listado de usuarios';
$modulo_abierto = 'usuarios';
$pagina_activa = 'usuarios';

// Cargar los datos de los usuarios usando el controlador refactorizado
include('../app/controllers/usuarios/listado_de_usuarios.php'); 

// Incluir la parte superior del layout
include('../layout/parte1.php'); 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- ... (content header sin cambios) ... -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0"><?php echo htmlspecialchars($titulo_pagina); ?></h1>
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
                            <h3 class="card-title">Usuarios registrados</h3>
                            <div class="card-tools">
                                <a href="create.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Nuevo usuario
                                </a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="tabla-usuarios" class="table table-bordered table-striped"> <!-- Considera cambiar el ID si usas DataTables y ya existe "example1" -->
                                <thead>
                                    <tr>
                                        <th width="5%"><center>Nro</center></th>
                                        <th width="25%">Nombres</th> <!-- Quité <center> para consistencia, se puede manejar con CSS -->
                                        <th width="30%">Email</th>
                                        <th width="15%"><center>Rol</center></th>
                                        <th width="25%"><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $contador = 0;
                                    if (!empty($usuarios_datos)) { // Buena práctica verificar si hay datos
                                        foreach ($usuarios_datos as $usuario_dato) { // Cambié $usuario por $usuario_dato para evitar confusión con el modelo
                                            $id_usuario = $usuario_dato['id_usuario'];
                                    ?>
                                        <tr>
                                            <td><center><?php echo ++$contador; ?></center></td>
                                            <td><?php echo htmlspecialchars($usuario_dato['nombres']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario_dato['email']); ?></td>
                                            <td><center><?php echo htmlspecialchars($usuario_dato['nombre_rol']); ?></center></td> {/* Cambio aquí */}
                                            // ... (dentro del bucle foreach en la tabla) ...
                                            <td>
                                                <center>
                                                    <div class="btn-group">
                                                        <a href="show.php?id=<?php echo $id_usuario; ?>" class="btn btn-info btn-sm" title="Ver Detalles">
                                                            <i class="fa fa-eye"></i> Ver
                                                        </a>
                                                        <a href="update.php?id=<?php echo $id_usuario; ?>" class="btn btn-success btn-sm" title="Editar Usuario">
                                                            <i class="fa fa-pencil-alt"></i> Editar
                                                        </a>
                                                        
                                                        <form action="<?php echo htmlspecialchars($URL . '/app/controllers/usuarios/delete_controller.php'); ?>" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de que desea eliminar a este usuario? Esta acción no se puede deshacer.');">
                                                            <input type="hidden" name="id_usuario_a_eliminar" value="<?php echo htmlspecialchars($id_usuario); ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar Usuario">
                                                                <i class="fa fa-trash"></i> Borrar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </center>
                                            </td>
                                        </tr>
                                    <?php 
                                        } // Fin foreach
                                    } else { // Si no hay usuarios
                                    ?>
                                        <tr>
                                            <td colspan="5"><center>No hay usuarios registrados.</center></td>
                                        </tr>
                                    <?php
                                    } // Fin if-else empty
                                    ?>
                                </tbody>
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

<?php 
// Incluir mensajes y parte final del layout
include('../layout/mensajes.php'); 
include('../layout/parte2.php'); 
?>

<!-- Scripts para DataTables (si los usas aquí) -->
<script>
$(function () {
    $("#tabla-usuarios").DataTable({
        // Configuraciones de DataTables
        "responsive": true, "lengthChange": false, "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
        "language": { // Ejemplo de localización
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    }).buttons().container().appendTo('#tabla-usuarios_wrapper .col-md-6:eq(0)');
});
</script>