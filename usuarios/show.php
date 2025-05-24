<?php
// 1. Iniciar sesión (si no está activa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Cargar configuración principal (define $pdo, $URL, $fechaHora)
require_once __DIR__ . '/../app/config.php';

// 3. Cargar utilidades globales (usan $URL, pueden usar sesión)
require_once __DIR__ . '/../app/utils/funciones_globales.php'; // para sanear(), setMensaje(), redirigir()
// Validator.php no es estrictamente necesario aquí, pero mantener el orden de inclusión es bueno.
require_once __DIR__ . '/../app/utils/Validator.php'; 

// 4. Cargar modelos (usan $pdo)
require_once __DIR__ . '/../app/models/UsuarioModel.php';
// RolModel no es necesario aquí ya que getUsuarioById ya nos trae el nombre del rol.

// 5. Incluir el manejador de sesión (valida la sesión y carga datos del usuario logueado)
include __DIR__ . '/../layout/sesion.php'; 

// 6. Incluir manejador de permisos (asegura que solo administradores accedan)
include __DIR__ . '/../layout/permisos.php'; 

// --- Lógica específica de la página ---

// Obtener el ID del usuario a mostrar desde el parámetro GET
$id_usuario_get = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_usuario_get) {
    // Si el ID no es válido o no se proporciona
    setMensaje("ID de usuario no válido.", "error");
    redirigir('/usuarios/'); // Redirige al listado de usuarios
}

// Instanciar el modelo de usuario
$usuarioModel = new UsuarioModel($pdo, $URL);
// Obtener los datos del usuario específico
$usuario_detalle = $usuarioModel->getUsuarioById($id_usuario_get);

if (!$usuario_detalle) {
    // Si no se encuentra un usuario con ese ID
    setMensaje("Usuario no encontrado.", "error");
    redirigir('/usuarios/'); // Redirige al listado
}

// --- Preparación para la vista ---
$titulo_pagina = 'Detalles del Usuario: ' . sanear($usuario_detalle['nombres']);
$modulo_abierto = 'usuarios'; // Para el menú lateral
$pagina_activa = 'usuarios';    // Para resaltar en el menú

// Determinar la ruta completa de la imagen de perfil
$nombre_imagen_perfil = $usuario_detalle['imagen_perfil'] ?? 'user_default.png';
if (empty($nombre_imagen_perfil)) { // Doble seguro por si está vacío en la BD
    $nombre_imagen_perfil = 'user_default.png';
}
$ruta_imagen = $URL . '/public/images/perfiles/' . $nombre_imagen_perfil;


// 7. Incluir la parte superior del layout (HTML hasta el contenido principal)
include('../layout/parte1.php'); 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo $titulo_pagina; ?></h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/usuarios/">Usuarios</a></li>
                        <li class="breadcrumb-item active">Detalles del Usuario</li>
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
                <div class="col-md-8"> {/* Ajusta el ancho si es necesario */}
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Información Registrada</h3>
                            <div class="card-tools">
                                <a href="<?php echo $URL; ?>/usuarios/update.php?id=<?php echo sanear($usuario_detalle['id_usuario']); ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-edit"></i> Editar Usuario
                                </a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body" style="display: block;">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <img src="<?php echo sanear($ruta_imagen); ?>" alt="Imagen de perfil de <?php echo sanear($usuario_detalle['nombres']); ?>" 
                                         class="img-fluid img-thumbnail" style="max-height: 200px; border-radius: 10px;">
                                </div>
                                <div class="col-md-8">
                                    <table class="table table-bordered table-striped">
                                        <tbody>
                                            <tr>
                                                <th style="width: 30%;">ID Usuario:</th>
                                                <td><?php echo sanear($usuario_detalle['id_usuario']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nombres:</th>
                                                <td><?php echo sanear($usuario_detalle['nombres']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td><?php echo sanear($usuario_detalle['email']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Rol:</th>
                                                <td><?php echo sanear($usuario_detalle['nombre_rol']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Fecha de Creación:</th>
                                                <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($usuario_detalle['fyh_creacion']))); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Última Actualización:</th>
                                                <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($usuario_detalle['fyh_actualizacion']))); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group text-right">
                                <a href="<?php echo $URL; ?>/usuarios/" class="btn btn-secondary">Volver al Listado</a>
                            </div>
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
include('../layout/mensajes.php'); 
include('../layout/parte2.php'); 
?>