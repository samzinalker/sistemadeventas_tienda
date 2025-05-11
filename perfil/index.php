<?php
include('../app/config.php');
include('../layout/sesion.php');

include('../layout/parte1.php');
include('../app/controllers/perfil/datos_perfil.php');

// Variables para el menú
$modulo_abierto = 'perfil';
$pagina_activa = 'perfil_datos';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Mi Perfil</h1>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <!-- Tarjeta de imagen de perfil -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <?php
                                $ruta_imagen = !empty($imagen_perfil) ? $URL.'/public/images/perfiles/'.$imagen_perfil : $URL.'/public/images/perfiles/user_default.png';
                                ?>
                                <img class="profile-user-img img-fluid img-circle" 
                                     src="<?php echo $ruta_imagen; ?>" 
                                     alt="Imagen de perfil de usuario"
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            </div>

                            <h3 class="profile-username text-center"><?php echo $nombres; ?></h3>
                            <p class="text-muted text-center"><?php echo $rol; ?></p>

                            <form action="../app/controllers/perfil/actualizar_imagen.php" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="imagen">Cambiar imagen de perfil</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="imagen" name="imagen" accept="image/jpeg,image/png,image/gif">
                                            <label class="custom-file-label" for="imagen">Elegir archivo</label>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.</small>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Actualizar imagen</button>
                            </form>
                        </div>
                    </div>

                    <!-- Tarjeta de datos de usuario -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Datos de Usuario</h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-user mr-1"></i> Nombre</strong>
                            <p class="text-muted"><?php echo $nombres; ?></p>
                            <hr>

                            <strong><i class="fas fa-envelope mr-1"></i> Usuario/Email</strong>
                            <p class="text-muted"><?php echo $email; ?></p>
                            <hr>

                            <strong><i class="fas fa-id-card mr-1"></i> Rol</strong>
                            <p class="text-muted"><?php echo $rol; ?></p>
                            <hr>

                            <strong><i class="fas fa-clock mr-1"></i> Último acceso</strong>
                            <p class="text-muted"><?php echo $fyh_actualizacion != '0000-00-00 00:00:00' ? $fyh_actualizacion : 'No disponible'; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <!-- Tarjeta de actualización de datos -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Actualizar Datos Personales</h3>
                        </div>
                        <div class="card-body">
                            <form action="../app/controllers/perfil/actualizar_datos.php" method="post">
                                <div class="form-group">
                                    <label for="nombres">Nombre Completo</label>
                                    <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo $nombres; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Usuario/Email</label>
                                    <input type="text" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </form>
                        </div>
                    </div>

                    <!-- Tarjeta de cambio de contraseña -->
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Cambiar Contraseña</h3>
                        </div>
                        <div class="card-body">
                            <form action="../app/controllers/perfil/actualizar_password.php" method="post" id="form-password">
                                <div class="form-group">
                                    <label for="password_actual">Contraseña Actual</label>
                                    <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                                </div>
                                <div class="form-group">
                                    <label for="password_nueva">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_nueva" name="password_nueva" required>
                                    <small class="form-text text-muted">La contraseña debe tener al menos 6 caracteres</small>
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmar">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required>
                                </div>
                                <button type="submit" class="btn btn-danger">Cambiar Contraseña</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../layout/mensajes.php'); ?>
<?php include('../layout/parte2.php'); ?>

<!-- Añadir script para la previsualización de la imagen -->
<script src="<?php echo $URL; ?>/public/templeates/AdminLTE-3.2.0/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script src="<?php echo $URL; ?>/public/js/perfil.js"></script>
<script>
$(document).ready(function () {
    bsCustomFileInput.init();
});
</script>
