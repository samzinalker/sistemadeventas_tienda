<?php
// Iniciar sesión si no está iniciada (necesario para setMensaje)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir dependencias
require_once __DIR__ . '/../../config.php'; // Para $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/funciones_globales.php'; // NUEVO
require_once __DIR__ . '/../../models/UsuarioModel.php';
// RolModel no es necesario aquí, ya que este controlador solo procesa el POST.
// RolModel se usa en la vista usuarios/create.php para mostrar el <select> de roles.

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido.", "error");
    redirigir('/usuarios/create.php'); // Redirigir a la página del formulario
}

// Instanciar modelos
$usuarioModel = new UsuarioModel($pdo, $URL);

// Validar campos requeridos
$campos_requeridos = ['nombres', 'email', 'rol', 'password_user', 'password_repeat'];
$campos_faltantes = Validator::requiredFields($_POST, $campos_requeridos);

if (!empty($campos_faltantes)) {
    $campos_str = implode(', ', array_map('sanear', $campos_faltantes)); // Sanear nombres de campos
    setMensaje("Los siguientes campos son obligatorios: {$campos_str}.", "error");
    // Guardar datos del formulario en sesión para repoblar (opcional, pero mejora UX)
    $_SESSION['form_data_usuario_create'] = $_POST; 
    redirigir('/usuarios/create.php');
}

// Obtener y limpiar datos del formulario
$nombres = trim($_POST['nombres']);
$email = trim($_POST['email']);
$id_rol = filter_input(INPUT_POST, 'rol', FILTER_VALIDATE_INT);
$password = $_POST['password_user'];
$password_repeat = $_POST['password_repeat'];

// Validaciones adicionales
if (!Validator::isValidEmail($email)) {
    setMensaje("El formato del correo electrónico no es válido.", "error");
    $_SESSION['form_data_usuario_create'] = $_POST;
    redirigir('/usuarios/create.php');
}

if ($id_rol === false || $id_rol <= 0) { // Rol debe ser un entero positivo
    setMensaje("Seleccione un rol válido.", "error");
    $_SESSION['form_data_usuario_create'] = $_POST;
    redirigir('/usuarios/create.php');
}

// Verificar si el correo ya está registrado
if ($usuarioModel->emailExiste($email)) {
    setMensaje("El correo electrónico '" . sanear($email) . "' ya está registrado. Intente con otro.", "error");
    $_SESSION['form_data_usuario_create'] = $_POST;
    redirigir('/usuarios/create.php');
}

// Validar y procesar contraseña
list($password_hash, $error_password) = procesarPassword($password, $password_repeat);

if ($error_password) {
    setMensaje($error_password, "error");
    $_SESSION['form_data_usuario_create'] = $_POST;
    redirigir('/usuarios/create.php');
}

// Crear el usuario
// La variable $fechaHora viene de config.php
$creado = $usuarioModel->crearUsuario($nombres, $email, $password_hash, $id_rol, $fechaHora);

if ($creado) {
    unset($_SESSION['form_data_usuario_create']); // Limpiar datos de formulario en sesión
    setMensaje("Usuario registrado correctamente.", "success");
    redirigir('/usuarios/'); // Redirigir al listado de usuarios
} else {
    setMensaje("Error al registrar el usuario. Inténtelo de nuevo.", "error");
    $_SESSION['form_data_usuario_create'] = $_POST;
    redirigir('/usuarios/create.php');
}
?>