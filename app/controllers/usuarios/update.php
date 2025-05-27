<?php
// 1. Iniciar sesión si es necesario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir config.php PRIMERO
require_once __DIR__ . '/../../config.php';

// 3. Incluir las demás utilidades y modelos
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/funciones_globales.php';
require_once __DIR__ . '/../../models/UsuarioModel.php';

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido.", "error");
    redirigir('/usuarios/'); 
}

// Instanciar modelo
$usuarioModel = new UsuarioModel($pdo, $URL);

// Validar campos requeridos
$id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
$campos_requeridos = ['nombres', 'usuario', 'email', 'rol']; // ✅ AGREGAR 'usuario'
$campos_faltantes = Validator::requiredFields($_POST, $campos_requeridos);

if (!$id_usuario) {
    setMensaje("ID de usuario no proporcionado o inválido.", "error");
    redirigir('/usuarios/');
}

$redirect_url_on_error = '/usuarios/update.php?id=' . $id_usuario;

if (!empty($campos_faltantes)) {
    $campos_str = implode(', ', array_map('sanear', $campos_faltantes));
    setMensaje("Los siguientes campos son obligatorios: {$campos_str}.", "error");
    $_SESSION['form_data_usuario_update'][$id_usuario] = $_POST;
    redirigir($redirect_url_on_error);
}

// Obtener y limpiar datos del formulario
$nombres = trim($_POST['nombres']);
$usuario = trim($_POST['usuario']); // ✅ AGREGAR ESTA LÍNEA
$email = trim($_POST['email']);
$id_rol = filter_input(INPUT_POST, 'rol', FILTER_VALIDATE_INT);
$password = $_POST['password_user'] ?? ''; 
$password_repeat = $_POST['password_repeat'] ?? '';

// Validaciones adicionales
if (!Validator::isValidEmail($email)) {
    setMensaje("El formato del correo electrónico no es válido.", "error");
    $_SESSION['form_data_usuario_update'][$id_usuario] = $_POST;
    redirigir($redirect_url_on_error);
}

if ($id_rol === false || $id_rol <= 0) {
    setMensaje("Seleccione un rol válido.", "error");
    $_SESSION['form_data_usuario_update'][$id_usuario] = $_POST;
    redirigir($redirect_url_on_error);
}

// ✅ AGREGAR: Verificar si el usuario ya existe
if ($usuarioModel->usuarioExiste($usuario, $id_usuario)) {
    setMensaje("El nombre de usuario '" . sanear($usuario) . "' ya está registrado por otro usuario.", "error");
    $_SESSION['form_data_usuario_update'][$id_usuario] = $_POST;
    redirigir($redirect_url_on_error);
}

if ($usuarioModel->emailExiste($email, $id_usuario)) { 
    setMensaje("El correo electrónico '" . sanear($email) . "' ya está registrado por otro usuario.", "error");
    $_SESSION['form_data_usuario_update'][$id_usuario] = $_POST;
    redirigir($redirect_url_on_error);
}

$actualizado_ok = true; 

if (!empty($password) || !empty($password_repeat)) { 
    list($password_hash, $error_password) = procesarPassword($password, $password_repeat);
    if ($error_password) {
        setMensaje($error_password, "error");
        $_SESSION['form_data_usuario_update'][$id_usuario] = $_POST;
        redirigir($redirect_url_on_error);
    }
    if (!$usuarioModel->actualizarPassword($id_usuario, $password_hash, $fechaHora)) {
        $actualizado_ok = false;
        setMensaje("Error al actualizar la contraseña.", "error"); 
    }
}

if ($actualizado_ok) { 
    // ✅ CORREGIR: Agregar parámetro $usuario
    if ($usuarioModel->actualizarUsuario($id_usuario, $nombres, $usuario, $email, $id_rol, $fechaHora)) {
        unset($_SESSION['form_data_usuario_update'][$id_usuario]);
        setMensaje("Usuario actualizado correctamente.", "success");
        redirigir('/usuarios/');
    } else {
        setMensaje("Error al actualizar los datos del usuario.", "error");
        $_SESSION['form_data_usuario_update'][$id_usuario] = $_POST;
        redirigir($redirect_url_on_error);
    }
} else { 
    $_SESSION['form_data_usuario_update'][$id_usuario] = $_POST;
    redirigir($redirect_url_on_error);
}
?>