<?php
// 1. Iniciar sesión (si no está activa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir configuración y dependencias
require_once __DIR__ . '/../../config.php'; // $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../utils/Validator.php'; // Para isValidPasswordLength
require_once __DIR__ . '/../../utils/funciones_globales.php'; // Para setMensaje, redirigir
require_once __DIR__ . '/../../models/UsuarioModel.php';

// 3. Verificar que el usuario esté logueado (AISLAMIENTO DEL USUARIO)
if (!isset($_SESSION['id_usuario'])) {
    setMensaje("Debe iniciar sesión para realizar esta acción.", "error");
    redirigir($URL . '/login/');
}
$id_usuario_actualizar = (int)$_SESSION['id_usuario']; // Clave para todas las operaciones de BD

// 4. Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido. El método de solicitud no es válido.", "error");
    redirigir($URL . '/perfil/');
}

// 5. Obtener datos del formulario (se usa trim por si acaso, aunque para contraseñas no suele ser problemático)
$password_actual_ingresada = trim($_POST['password_actual'] ?? '');
$password_nueva = trim($_POST['password_nueva'] ?? '');
$password_confirmar = trim($_POST['password_confirmar'] ?? '');

// 6. Validaciones de los datos recibidos
if (empty($password_actual_ingresada)) {
    setMensaje("El campo 'Contraseña Actual' no puede estar vacío.", "warning");
    redirigir($URL . '/perfil/');
}

if (empty($password_nueva)) {
    setMensaje("El campo 'Nueva Contraseña' не может быть пустым.", "warning"); // "no puede estar vacío"
    redirigir($URL . '/perfil/');
}

if (empty($password_confirmar)) {
    setMensaje("El campo 'Confirmar Nueva Contraseña' no puede estar vacío.", "warning");
    redirigir($URL . '/perfil/');
}

// Validación de longitud mínima para la nueva contraseña
if (!Validator::isValidPasswordLength($password_nueva, 6)) { // Asumiendo 6 como longitud mínima
    setMensaje("La nueva contraseña debe tener al menos 6 caracteres.", "warning");
    redirigir($URL . '/perfil/');
}

// Verificar que la nueva contraseña y su confirmación coincidan
if ($password_nueva !== $password_confirmar) {
    setMensaje("La nueva contraseña y su confirmación no coinciden. Por favor, verifíquelas.", "error");
    redirigir($URL . '/perfil/');
}

// Evitar que la nueva contraseña sea igual a la actual (opcional pero recomendado)
if ($password_actual_ingresada === $password_nueva) {
    setMensaje("La nueva contraseña no puede ser igual a la contraseña actual.", "warning");
    redirigir($URL . '/perfil/');
}

// 7. Lógica de actualización de contraseña usando el Modelo
try {
    $usuarioModel = new UsuarioModel($pdo);

    // Obtener el hash de la contraseña actual del usuario desde la BD.
    // Esto también confirma que el usuario existe.
    $usuario_data = $usuarioModel->getUsuarioById($id_usuario_actualizar);

    if (!$usuario_data || !isset($usuario_data['password_user'])) {
        // Situación anómala si el usuario está logueado y su ID es correcto.
        setMensaje("Error crítico al verificar la información del usuario. Su sesión podría ser inválida.", "error");
        // Considerar invalidar la sesión aquí por seguridad.
        session_destroy();
        redirigir($URL . '/login/');
    }
    $password_hash_actual_bd = $usuario_data['password_user'];

    // Verificar si la contraseña actual ingresada por el usuario coincide con la almacenada en la BD.
    if (password_verify($password_actual_ingresada, $password_hash_actual_bd)) {
        // La contraseña actual es correcta. Proceder a hashear y actualizar la nueva contraseña.
        
        // Hashear la nueva contraseña.
        $nuevo_password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        if ($nuevo_password_hash === false) {
            // Error en la función password_hash() del servidor, muy improbable.
            error_log("Error en password_hash() en actualizar_password.php para usuario ID {$id_usuario_actualizar}");
            setMensaje("Error interno del sistema al procesar la nueva contraseña. Por favor, intente más tarde.", "error");
            redirigir($URL . '/perfil/');
        }

        // $fechaHora es una variable global definida en config.php.
        // Actualizar la contraseña en la base de datos.
        $actualizado = $usuarioModel->actualizarPassword($id_usuario_actualizar, $nuevo_password_hash, $fechaHora);

        if ($actualizado) {
            setMensaje("Su contraseña ha sido actualizada correctamente. Le recomendamos iniciar sesión nuevamente por seguridad.", "success");
            // SUGERENCIA DE SEGURIDAD:
            // Forzar un cierre de sesión en todas las demás sesiones activas del usuario (si tienes un sistema de tokens de sesión).
            // O, como mínimo, forzar el cierre de la sesión actual para que tenga que volver a ingresar con la nueva contraseña.
            // Ejemplo de forzar logout de la sesión actual:
            // session_destroy();
            // redirigir($URL.'/login/?mensaje=contrasena_actualizada'); // Para mostrar un mensaje específico en la página de login.
            // Por ahora, solo redirigimos al perfil. El usuario sigue logueado.
        } else {
            setMensaje("No se pudo actualizar su contraseña en la base de datos en este momento. Inténtelo de nuevo.", "error");
        }
    } else {
        // La contraseña actual ingresada es incorrecta.
        setMensaje("La 'Contraseña Actual' que ingresó es incorrecta. Por favor, verifíquela.", "error");
    }
} catch (PDOException $e) {
    error_log("Error de BD en actualizar_password.php para usuario ID {$id_usuario_actualizar}: " . $e->getMessage());
    setMensaje("Error del sistema al actualizar su contraseña. Por favor, contacte al soporte si el problema persiste.", "error");
} catch (Exception $e) {
    error_log("Error general en actualizar_password.php para usuario ID {$id_usuario_actualizar}: " . $e->getMessage());
    setMensaje("Ocurrió un error inesperado al procesar su solicitud de cambio de contraseña. Por favor, intente más tarde.", "error");
}

// Siempre redirigir de vuelta a la página de perfil.
redirigir($URL . '/perfil/');
?>