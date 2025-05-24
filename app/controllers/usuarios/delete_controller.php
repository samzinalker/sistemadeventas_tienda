<?php
// 1. Iniciar sesión si es necesario (para setMensaje y verificar sesión del admin)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir config.php PRIMERO
require_once __DIR__ . '/../../config.php';

// 3. Incluir las demás utilidades y modelos
require_once __DIR__ . '/../../utils/funciones_globales.php'; // Para setMensaje, redirigir, sanear
require_once __DIR__ . '/../../models/UsuarioModel.php';

// 4. Verificar sesión y permisos (esto podría estar en un include como layout/permisos.php, 
//    pero aquí lo hacemos explícito para el controlador de borrado)
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol'])) {
    // Si no hay sesión, no debería llegar aquí si las vistas están protegidas, pero es un seguro.
    setMensaje("Acceso denegado. Sesión no iniciada.", "error");
    redirigir('/login.php'); // O a donde corresponda
}

if (strtolower(trim($_SESSION['rol'])) !== 'administrador') {
    setMensaje("No tienes permisos para realizar esta acción.", "error");
    // Redirigir a una página principal o al listado si no es admin
    redirigir('/usuarios/'); // O a $URL.'/index.php' si es más general
}

// --- Lógica específica del controlador ---

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido. Método incorrecto.", "error");
    redirigir('/usuarios/');
}

// Obtener el ID del usuario a eliminar desde el POST
$id_usuario_eliminar = filter_input(INPUT_POST, 'id_usuario_a_eliminar', FILTER_VALIDATE_INT);

if (!$id_usuario_eliminar) {
    setMensaje("ID de usuario no válido para eliminación.", "error");
    redirigir('/usuarios/');
}

// Evitar que el administrador se elimine a sí mismo
if ($id_usuario_eliminar == $_SESSION['id_usuario']) {
    setMensaje("No puedes eliminar tu propia cuenta de administrador.", "warning");
    redirigir('/usuarios/');
}

// Instanciar el modelo de usuario
$usuarioModel = new UsuarioModel($pdo, $URL);

// Opcional: Obtener datos del usuario para eliminar su imagen de perfil si existe
$usuario_a_eliminar_datos = $usuarioModel->getUsuarioById($id_usuario_eliminar);

if ($usuario_a_eliminar_datos) {
    // Intentar eliminar al usuario de la base de datos
    if ($usuarioModel->eliminarUsuario($id_usuario_eliminar)) {
        // Si se eliminó de la BD, intentar eliminar su imagen de perfil (si no es la default)
        $imagen_perfil = $usuario_a_eliminar_datos['imagen_perfil'];
        if (!empty($imagen_perfil) && $imagen_perfil !== 'user_default.png') {
            $ruta_imagen_fisica = __DIR__ . '/../../../public/images/perfiles/' . $imagen_perfil;
            if (file_exists($ruta_imagen_fisica)) {
                @unlink($ruta_imagen_fisica); // @ para suprimir errores si no se puede borrar, aunque se debería loguear
            }
        }
        setMensaje("Usuario eliminado correctamente.", "success");
    } else {
        // Si hubo un error al eliminar de la BD (ej. por restricciones de FK si no están en cascada)
        setMensaje("Error al eliminar el usuario de la base de datos. Puede estar asociado a otros registros.", "error");
    }
} else {
    // Si el usuario a eliminar no fue encontrado (ya fue borrado o ID incorrecto)
    setMensaje("El usuario que intentas eliminar no fue encontrado.", "warning");
}

// Redirigir siempre al listado de usuarios después de la operación
redirigir('/usuarios/');
?>