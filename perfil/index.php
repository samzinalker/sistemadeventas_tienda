<?php
// 1. Iniciar sesión (si no está activa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Cargar configuración principal
require_once __DIR__ . '/../app/config.php'; // Define $pdo, $URL, $fechaHora

// 3. Cargar utilidades globales ANTES de cualquier script que las pueda usar (como layout/sesion.php o datos_perfil.php)
require_once __DIR__ . '/../app/utils/funciones_globales.php'; // Define sanear(), setMensaje(), redirigir()
require_once __DIR__ . '/../app/utils/Validator.php'; // Aunque no se use directamente aquí, es buena práctica tenerlo cerca

// 4. Incluir el manejador de sesión (valida la sesión y carga datos básicos del usuario en $_SESSION)
//    layout/sesion.php ahora puede usar setMensaje() si es necesario.
include __DIR__ . '/../layout/sesion.php';

// 5. Cargar el controlador que prepara los datos del perfil
//    datos_perfil.php ahora puede usar sanear() y otras funciones globales.
//    También puede usar $pdo y $URL de config.php, y $_SESSION de layout/sesion.php
require_once __DIR__ . '/../app/controllers/perfil/datos_perfil.php';

// --- Preparación para la vista ---
$titulo_pagina = 'Mi Perfil';
$modulo_abierto = 'perfil'; 
$pagina_activa = 'perfil_ver'; 

// 6. Incluir la parte superior del layout
include('../layout/parte1.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0"><?php echo sanear($titulo_pagina); // sanear() ya está disponible ?></h1>
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
                                <img class="profile-user-img img-fluid img-circle"
                                     src="<?php echo sanear($imagen_perfil_url); // Usar la variable preparada por datos_perfil.php ?>"
                                     alt="Imagen de perfil de <?php echo $nombres; // Usar $nombres saneado ?>"
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            </div>

                            <h3 class="profile-username text-center"><?php echo $nombres; ?></h3>
                            <p class="text-muted text-center"><?php echo $rol; ?></p>

                            <form action="<?php echo $URL; ?>/app/controllers/perfil/actualizar_imagen.php" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="imagen_perfil_upload">Cambiar imagen de perfil</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="imagen_perfil_upload" name="imagen_perfil" accept="image/jpeg,image/png,image/gif">
                                        <label class="custom-file-label" for="imagen_perfil_upload" data-browse="Elegir">Seleccionar archivo...</label>
                                    </div>
                                    <small class="form-text text-muted">Formatos: JPG, PNG, GIF. Máx 2MB.</small>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Actualizar Imagen</button>
                            </form>
                        </div>
                    </div>

                    <!-- Tarjeta de datos de usuario (Información general) -->
                    <div class="card card-info mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Información de la Cuenta</h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-user mr-1"></i> Nombre Completo</strong>
                            <p class="text-muted"><?php echo $nombres; ?></p>
                            <hr>

                            <strong><i class="fas fa-envelope mr-1"></i> Usuario / Email de Contacto</strong>
                            <p class="text-muted"><?php echo $email; ?></p>
                            <hr>

                            <strong><i class="fas fa-id-card mr-1"></i> Rol Asignado</strong>
                            <p class="text-muted"><?php echo $rol; ?></p>
                            <hr>
                            
                            <strong><i class="fas fa-calendar-alt mr-1"></i> Miembro Desde</strong>
                            <p class="text-muted"><?php echo htmlspecialchars(date('d/m/Y', strtotime($fyh_creacion))); ?></p>
                            <hr>

                            <strong><i class="fas fa-clock mr-1"></i> Última Actualización de Datos</strong>
                            <p class="text-muted">
                                <?php
                                if ($fyh_actualizacion && $fyh_actualizacion != '0000-00-00 00:00:00') {
                                    echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($fyh_actualizacion)));
                                } else {
                                    echo 'No registrada';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <!-- Tarjeta de actualización de datos personales -->
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Actualizar Datos Personales</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo $URL; ?>/app/controllers/perfil/actualizar_datos.php" method="post">
                                <div class="form-group">
                                    <label for="nombres_edit">Nombre Completo</label>
                                    <input type="text" class="form-control" id="nombres_edit" name="nombres"
                                           value="<?php echo $nombres_form; // Usar variable para repoblar ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email_edit">Usuario / Email de Contacto</label>
                                    <input type="email" class="form-control" id="email_edit" name="email"
                                           value="<?php echo $email_form; // Usar variable para repoblar ?>" required>
                                    <small class="form-text text-muted">Este es tu identificador para iniciar sesión.</small>
                                </div>
                                <button type="submit" class="btn btn-success">Guardar Cambios de Datos</button>
                            </form>
                        </div>
                    </div>

                    <!-- Tarjeta de cambio de contraseña -->
                    <div class="card card-danger card-outline mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Cambiar Contraseña</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo $URL; ?>/app/controllers/perfil/actualizar_password.php" method="post" id="form-password">
                                <div class="form-group">
                                    <label for="password_actual">Contraseña Actual</label>
                                    <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                                </div>
                                <div class="form-group">
                                    <label for="password_nueva">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_nueva" name="password_nueva" required>
                                    <small class="form-text text-muted">La contraseña debe tener al menos 6 caracteres.</small>
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmar">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required>
                                </div>
                                <button type="submit" class="btn btn-danger">Actualizar Contraseña</button>
                            </form>
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
// 7. Incluir mensajes y la parte final del layout
include('../layout/mensajes.php');
include('../layout/parte2.php');
?>

<!-- Script para bs-custom-file-input y validación de contraseña si es necesario -->
<script src="<?php echo $URL; ?>/public/templeates/AdminLTE-3.2.0/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script>
$(document).ready(function () {
  bsCustomFileInput.init();

  // Validación simple para el formulario de cambio de contraseña (opcional, ya que hay en servidor)
  $("#form-password").submit(function(e){
    var nueva = $("#password_nueva").val();
    var confirmar = $("#password_confirmar").val();
    if (nueva.length < 6) {
        e.preventDefault();
        Swal.fire('Error', 'La nueva contraseña debe tener al menos 6 caracteres.', 'error');
        return false;
    }
    if (nueva !== confirmar) {
        e.preventDefault();
        Swal.fire('Error', 'Las nuevas contraseñas no coinciden.', 'error');
        return false;
    }
  });
});
</script>