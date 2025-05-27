<?php
// 1. Iniciar sesión (si no está activa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Cargar configuración principal (define $pdo, $URL, $fechaHora)
require_once __DIR__ . '/../app/config.php';

// 3. Cargar utilidades globales (usan $URL, pueden usar sesión)
require_once __DIR__ . '/../app/utils/funciones_globales.php';

// 4. Incluir el manejador de sesión (valida la sesión y carga datos del usuario logueado)
include __DIR__ . '/../layout/sesion.php'; 

// 5. Incluir manejador de permisos (asegura que solo administradores accedan)
include __DIR__ . '/../layout/permisos.php'; 

// --- Configuración de la página ---
$titulo_pagina = 'Listado de usuarios';
$modulo_abierto = 'usuarios';
$pagina_activa = 'usuarios';

// 6. Cargar los datos de los usuarios usando el controlador refactorizado
include __DIR__ . '/../app/controllers/usuarios/listado_de_usuarios.php'; 

// 7. Incluir la parte superior del layout
include __DIR__ . '/../layout/parte1.php'; 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo sanear($titulo_pagina); ?></h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Usuarios</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Usuarios registrados</h3>
                            <div class="card-tools">
                                <a href="<?php echo $URL; ?>/usuarios/create.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Nuevo usuario
                                </a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="tabla-usuarios" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="5%"><center>Nro</center></th>
                                        <th width="20%">Nombres</th>
                                        <th width="15%">Usuario</th>
                                        <th width="25%">Email</th>
                                        <th width="10%"><center>Rol</center></th>
                                        <th width="25%"><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $contador = 0;
                                    if (!empty($usuarios_datos)) {
                                        foreach ($usuarios_datos as $usuario_dato) {
                                            $id_usuario = $usuario_dato['id_usuario'];
                                    ?>
                                        <tr>
                                            <td><center><?php echo ++$contador; ?></center></td>
                                            <td><?php echo sanear($usuario_dato['nombres']); ?></td>
                                            <td><?php echo sanear($usuario_dato['usuario']); ?></td>
                                            <td><?php echo sanear($usuario_dato['email']); ?></td>
                                            <td><center><?php echo sanear($usuario_dato['nombre_rol']); ?></center></td>
                                            <td>
                                                <center>
                                                    <div class="btn-group">
                                                        <a href="<?php echo $URL; ?>/usuarios/show.php?id=<?php echo $id_usuario; ?>" class="btn btn-info btn-sm" title="Ver Detalles">
                                                            <i class="fa fa-eye"></i> Ver
                                                        </a>
                                                        <a href="<?php echo $URL; ?>/usuarios/update.php?id=<?php echo $id_usuario; ?>" class="btn btn-success btn-sm" title="Editar Usuario">
                                                            <i class="fa fa-pencil-alt"></i> Editar
                                                        </a>
                                                        
                                                        <form action="<?php echo $URL; ?>/app/controllers/usuarios/delete_controller.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de que desea eliminar este usuario?');">
                                                            <input type="hidden" name="id_usuario_a_eliminar" value="<?php echo sanear($id_usuario); ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar Usuario">
                                                                <i class="fa fa-trash"></i> Borrar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </center>
                                            </td>
                                        </tr>
                                    <?php 
                                        }
                                    } else {
                                    ?>
                                        <tr>
                                            <td colspan="6"><center>No hay usuarios registrados.</center></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php 
// 8. Incluir mensajes (si los hay) y la parte final del layout
include __DIR__ . '/../layout/mensajes.php'; 
include __DIR__ . '/../layout/parte2.php'; 
?>

<!-- Scripts para DataTables -->
<script>
$(function () {
    $("#tabla-usuarios").DataTable({
        "responsive": true, 
        "lengthChange": false, 
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    }).buttons().container().appendTo('#tabla-usuarios_wrapper .col-md-6:eq(0)');
});
</script>