<?php
include('../app/config.php');
include('../layout/sesion.php');  // Validar sesión activa
include('../layout/permisos.php'); // Validar permisos de administrador



include('../layout/parte1.php');
include('../app/controllers/usuarios/listado_de_usuarios.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Listado de usuarios</h1>
                </div>
            </div>
        </div>
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
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th><center>Nro</center></th>
                                        <th><center>Nombres</center></th>
                                        <th><center>Email</center></th>
                                        <th><center>Rol del usuario</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $contador = 0;
                                    foreach ($usuarios_datos as $usuarios_dato) {
                                        $id_usuario = $usuarios_dato['id_usuario'];
                                    ?>
                                        <tr>
                                            <td><center><?php echo ++$contador; ?></center></td>
                                            <td><?php echo $usuarios_dato['nombres']; ?></td>
                                            <td><?php echo $usuarios_dato['email']; ?></td>
                                            <td><center><?php echo $usuarios_dato['rol']; ?></center></td>
                                            <td>
                                                <center>
                                                    <div class="btn-group">
                                                        <a href="show.php?id=<?php echo $id_usuario; ?>" class="btn btn-info"><i class="fa fa-eye"></i> Ver</a>
                                                        <a href="update.php?id=<?php echo $id_usuario; ?>" class="btn btn-success"><i class="fa fa-pencil-alt"></i> Editar</a>
                                                        <a href="delete.php?id=<?php echo $id_usuario; ?>" class="btn btn-danger"><i class="fa fa-trash"></i> Borrar</a>
                                                    </div>
                                                </center>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><center>Nro</center></th>
                                        <th><center>Nombres</center></th>
                                        <th><center>Email</center></th>
                                        <th><center>Rol del usuario</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include('../layout/mensajes.php'); ?>
<?php include('../layout/parte2.php'); ?>