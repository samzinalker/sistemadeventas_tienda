<?php
// 1. Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir configuración y dependencias
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../utils/Validator.php'; // Para isValidPasswordLength
require_once __DIR__ . '/../../utils/funciones_globales.php'; // Para setMensaje, redirigir, procesarPassword
require_once __DIR__ . '/../../models/UsuarioModel.php';

// 3. Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    setMensaje("Debe iniciar sesión para realizar esta acción.", "error");
    redirigir('/login/');
}
$id_usuario_actualizar = (int)$_SESSION['id_usuario'];

// 4. Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido.", "error");
    redirigir('/perfil/');
}

// 5. Obtener datos del formulario
$password_actual_ingresada = $_POST['password_actual'] ?? '';
$password_nueva = $_POST['password_nueva'] ?? '';
$password_confirmar = $_POST['password_confirmar'] ?? '';

// 6. Validaciones
if (empty($password_actual_ingresada) || empty($password_nueva) || empty($password_confirmar)) {
    setMensaje("Todos los campos de contraseña son obligatorios.", "warning");
    redirigir('/perfil/');
}

if (!Validator::isValidPasswordLength($password_nueva, 6)) {
    setMensaje("La nueva contraseña debe tener al menos 6 caracteres.", "warning");
    redirigir('/perfil/');
}

if ($password_nueva !== $password_confirmar) {
    setMensaje("La nueva contraseña y su confirmación no coinciden.", "error");
    redirigir('/perfil/');
}

// 7. Lógica de actualización de contraseña usando el Modelo
try {
    $usuarioModel = new UsuarioModel($pdo, $URL);

    // Obtener el hash de la contraseña actual del usuario desde la BD
    $usuario_data = $usuarioModel->getUsuarioById($id_usuario_actualizar);
    if (!$usuario_data || !isset($usuario_data['password_user'])) {
        setMensaje("Error al verificar la información del usuario.", "error");
        redirigir('/perfil/');
    }
    $password_hash_actual_bd = $usuario_data['password_user'];

    // Verificar si la contraseña actual ingresada es correcta
    if (password_verify($password_actual_ingresada, $password_hash_actual_bd)) {
        // La contraseña actual es correcta, proceder a hashear y actualizar la nueva
        $nuevo_password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        if ($nuevo_password_hash === false) {
            // Error en la función password_hash del servidor
            setMensaje("Error interno al procesar la nueva contraseña.", "error");
            redirigir('/perfil/');
        }

        // $fechaHora viene de config.php
        if ($usuarioModel->actualizarPassword($id_usuario_actualizar, $nuevo_password_hash, $fechaHora)) {
            setMensaje("Contraseña actualizada correctamente.", "success");
            // Opcional: Forzar logout o enviar notificación por email sobre cambio de contraseña
        } else {
            setMensaje("No se pudo actualizar la contraseña en la base de datos.", "error");
        }
    } else {
        // La contraseña actual ingresada es incorrecta
        setMensaje("La contraseña actual que ingresó es incorrecta.", "error");
    }
} catch (PDOException $e) {
    error_log("Error de BD en actualizar_password.php: " . $e->getMessage());
    setMensaje("Error en el sistema al actualizar la contraseña. Por favor, intente más tarde.", "error");
} catch (Exception $e) {
    error_log("Error general en actualizar_password.php: " . $e->getMessage());
    setMensaje("Ocurrió un error inesperado al actualizar la contraseña. Por favor, intente más tarde.", "error");
}

redirigir('/perfil/');
?>