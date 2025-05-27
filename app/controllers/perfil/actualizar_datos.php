<?php
// 1. Iniciar sesión (si no está activa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir configuración y dependencias
require_once __DIR__ . '/../../config.php'; // $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/funciones_globales.php'; // setMensaje, redirigir, sanear
require_once __DIR__ . '/../../models/UsuarioModel.php';

// 3. Verificar que el usuario esté logueado (AISLAMIENTO DEL USUARIO)
if (!isset($_SESSION['id_usuario'])) {
    setMensaje("Debe iniciar sesión para realizar esta acción.", "error");
    redirigir($URL . '/login/'); // $URL está definido en config.php
}
$id_usuario_actualizar = (int)$_SESSION['id_usuario']; // Clave para todas las operaciones de BD

// 4. Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido. El método de solicitud no es válido.", "error");
    redirigir('/perfil/');
}

// 5. Obtener y sanear (inicialmente) datos del formulario
// Usamos trim() para quitar espacios al inicio/final. El saneamiento final se hará antes de la BD si es necesario.
$nombres_nuevos = trim($_POST['nombres'] ?? '');
$usuario_nuevo = trim($_POST['usuario'] ?? '');
$email_nuevo = trim($_POST['email'] ?? ''); // Este campo se usa como 'usuario' y 'email'

// 6. Guardar datos en sesión para repoblar el formulario en caso de error y redirección
// La clave de sesión es única por usuario para evitar conflictos.
$form_data_key = 'form_data_perfil_datos_' . $id_usuario_actualizar;
$_SESSION[$form_data_key] = [
    'nombres' => $nombres_nuevos, 
    'usuario' => $usuario_nuevo,  // ✅ AGREGAR
    'email' => $email_nuevo
];

// 7. Validaciones separadas
if (empty($nombres_nuevos)) {
    setMensaje("El campo 'Nombre Completo' no puede estar vacío.", "warning");
    redirigir('/perfil/');
}

if (empty($usuario_nuevo)) {
    setMensaje("El campo 'Usuario' no puede estar vacío.", "warning");
    redirigir('/perfil/');
}

if (empty($email_nuevo)) {
    setMensaje("El campo 'Email' no puede estar vacío.", "warning");
    redirigir('/perfil/');
}

if (!Validator::isValidEmail($email_nuevo)) {
    setMensaje("El formato del email no es válido.", "error");
    redirigir('/perfil/');
}

// 8. Validaciones de duplicados - AMBOS CAMPOS POR SEPARADO
try {
    $usuarioModel = new UsuarioModel($pdo, $URL);

    // ✅ Verificar usuario duplicado
    if ($usuarioModel->usuarioExiste($usuario_nuevo, $id_usuario_actualizar)) {
        setMensaje("El usuario '" . sanear($usuario_nuevo) . "' ya está registrado por otra cuenta.", "error");
        redirigir('/perfil/');
    }

    // ✅ Verificar email duplicado
    if ($usuarioModel->emailExiste($email_nuevo, $id_usuario_actualizar)) {
        setMensaje("El email '" . sanear($email_nuevo) . "' ya está registrado por otra cuenta.", "error");
        redirigir('/perfil/');
    }

    // ✅ Actualizar usando el método correcto del modelo
    $datos_actualizar = [
        'nombres' => $nombres_nuevos,
        'usuario' => $usuario_nuevo,
        'email' => $email_nuevo
    ];

    if ($usuarioModel->actualizarUsuario($id_usuario_actualizar, $datos_actualizar)) {
        unset($_SESSION[$form_data_key]);
        
        // ✅ Actualizar la sesión con el nuevo usuario
        $_SESSION['usuario'] = $usuario_nuevo;
        $_SESSION['sesion_email'] = $email_nuevo; // Mantener compatibilidad
        $_SESSION['nombres'] = $nombres_nuevos;
        
        setMensaje("Perfil actualizado correctamente.", "success");
        redirigir('/perfil/');
    } else {
        setMensaje("Error al actualizar el perfil.", "error");
        $_SESSION[$form_data_key] = $_POST;
        redirigir('/perfil/');
    }

} catch (PDOException $e) {
    error_log("Error en actualizar perfil: " . $e->getMessage());
    setMensaje("Error en el sistema al actualizar el perfil.", "error");
    redirigir('/perfil/');
}