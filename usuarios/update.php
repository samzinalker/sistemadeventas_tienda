<?php
// 1. Iniciar sesión (si no está activa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Cargar configuración principal (define $pdo, $URL, $fechaHora)
require_once __DIR__ . '/../app/config.php';

// 3. Cargar utilidades globales (usan $URL, pueden usar sesión)
require_once __DIR__ . '/../app/utils/funciones_globales.php';
require_once __DIR__ . '/../app/utils/Validator.php';

// 4. Cargar modelos (usan $pdo)
require_once __DIR__ . '/../app/models/UsuarioModel.php';
require_once __DIR__ . '/../app/models/RolModel.php';

// 5. Incluir el manejador de sesión (usa $pdo, $URL, y maneja la sesión)
// Ahora layout/sesion.php es seguro de incluir incluso si la sesión ya empezó.
include __DIR__ . '/../layout/sesion.php'; 

// 6. Incluir manejador de permisos (usa variables de sesión seteadas por layout/sesion.php)
include __DIR__ . '/../layout/permisos.php'; 

// Obtener el ID del usuario a editar
$id_usuario_get = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_usuario_get) {
    setMensaje("ID de usuario no válido.", "error");
    redirigir('/usuarios/');
}

// Instanciar modelos
$usuarioModel = new UsuarioModel($pdo, $URL);
$rolModel = new RolModel($pdo);

// Obtener datos del usuario a editar
$usuario_a_editar = $usuarioModel->getUsuarioById($id_usuario_get);

if (!$usuario_a_editar) {
    setMensaje("Usuario no encontrado.", "error");
    redirigir('/usuarios/');
}

// Obtener todos los roles para el dropdown
$roles_disponibles = $rolModel->getAllRoles();

// Título y módulo (esto puede variar)
$titulo_pagina = 'Actualizar Usuario: ' . sanear($usuario_a_editar['nombres']);
$modulo_abierto = 'usuarios';
$pagina_activa = 'usuarios'; // O podrías tener 'usuarios_update'

// Incluir la parte superior del layout
include('../layout/parte1.php'); 

// Repoblar formulario si hay datos en sesión por un error previo
$form_data = $_SESSION['form_data_usuario_update'][$id_usuario_get] ?? [];
unset($_SESSION['form_data_usuario_update'][$id_usuario_get]); // Limpiar después de usar
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12"><h1 class="m-0"><?php echo $titulo_pagina; ?></h1></div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-success">
                        <div class="card-header"><h3 class="card-title">Actualice los datos con cuidado</h3></div>
                        <div class="card-body">
                            <form action="<?php echo $URL; ?>/app/controllers/usuarios/update.php" method="post">
                                <input type="hidden" name="id_usuario" value="<?php echo sanear($usuario_a_editar['id_usuario']); ?>">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nombres">Nombres completos</label>
                                            <input type="text" name="nombres" id="nombres" class="form-control" 
                                                   value="<?php echo sanear($form_data['nombres'] ?? $usuario_a_editar['nombres']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" name="email" id="email" class="form-control" 
                                                   value="<?php echo sanear($form_data['email'] ?? $usuario_a_editar['email']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="rol">Rol del usuario</label>
                                            <select name="rol" id="rol" class="form-control" required>
                                                <option value="">Seleccione un rol...</option>
                                                <?php 
                                                $idRolSeleccionado = $form_data['rol'] ?? $usuario_a_editar['id_rol'];
                                                foreach ($roles_disponibles as $rol_dato): ?>
                                                    <option value="<?php echo sanear($rol_dato['id_rol']); ?>" 
                                                            <?php if ($rol_dato['id_rol'] == $idRolSeleccionado) echo 'selected'; ?>>
                                                        <?php echo sanear($rol_dato['rol']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                <p class="text-muted">Deje los campos de contraseña en blanco si no desea cambiarla.</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_user">Nueva Contraseña</label>
                                            <input type="password" name="password_user" id="password_user" class="form-control">
                                            <small class="form-text text-muted">Mínimo 6 caracteres.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_repeat">Repetir Nueva Contraseña</label>
                                            <input type="password" name="password_repeat" id="password_repeat" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <a href="<?php echo $URL; ?>/usuarios/" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-success">Actualizar Usuario</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include('../layout/mensajes.php'); 
include('../layout/parte2.php'); 
?>