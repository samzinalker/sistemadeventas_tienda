<?php
// 1. Incluir configuración y dependencias principales
require_once __DIR__ . '/../../config.php'; // Para $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../utils/Validator.php'; // Para validaciones directas si son necesarias
require_once __DIR__ . '/../../utils/funciones_globales.php'; // Para setMensaje, redirigir, procesarPassword, sanear
require_once __DIR__ . '/../../models/UsuarioModel.php';    // Modelo de Usuario

// 2. Iniciar sesión (setMensaje lo requiere)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido.", "error");
    redirigir('/login/registro.php');
}

// 4. Obtener y sanear datos del formulario
$nombres = trim($_POST['nombres'] ?? '');
$usuario = trim($_POST['usuario'] ?? ''); // ✅ AGREGAR ESTA LÍNEA
$email = trim($_POST['email'] ?? '');
$password = $_POST['password_user'] ?? '';
$password_repeat = $_POST['repassword'] ?? '';

// Guardar datos en sesión para repoblar el formulario en caso de error
$_SESSION['form_data_registro'] = $_POST;

// 5. Validaciones
$campos_requeridos = ['nombres', 'usuario', 'email', 'password_user', 'repassword']; // ✅ AGREGAR 'usuario'
$campos_faltantes = Validator::requiredFields($_POST, $campos_requeridos);

if (!empty($campos_faltantes)) {
    $campos_str = implode(', ', array_map('sanear', $campos_faltantes));
    setMensaje("Todos los campos marcados son obligatorios: {$campos_str}.", "warning");
    redirigir('/login/registro.php');
}

if (!Validator::isValidEmail($email)) {
    setMensaje("El formato del correo electrónico no es válido.", "error");
    redirigir('/login/registro.php');
}

// Validar y procesar contraseña usando la función global
list($password_hash, $error_password) = procesarPassword($password, $password_repeat, 6);

if ($error_password) {
    setMensaje($error_password, "error");
    redirigir('/login/registro.php');
}

// 6. Lógica de registro usando el Modelo
try {
    $usuarioModel = new UsuarioModel($pdo, $URL);

    // Verificar si el usuario ya existe
    if ($usuarioModel->usuarioExiste($usuario)) {
        setMensaje("El nombre de usuario '" . sanear($usuario) . "' ya está registrado. Intente con otro.", "warning");
        redirigir('/login/registro.php');
    }

    // Verificar si el email ya existe
    if ($usuarioModel->emailExiste($email)) {
        setMensaje("El email '" . sanear($email) . "' ya está registrado. Intente con otro.", "warning");
        redirigir('/login/registro.php');
    }

    // Crear el usuario
    $creado = $usuarioModel->crearUsuario($nombres, $usuario, $email, $password_hash);

    if ($creado) {
        unset($_SESSION['form_data_registro']);
        
        // Log de auditoría para registro público
        $ultimo_id = $usuarioModel->getUltimoIdUsuario();
        error_log("Registro público exitoso: Usuario '$usuario' creado con ID: $ultimo_id");
        
        setMensaje("Usuario registrado correctamente. Ahora puede iniciar sesión.", "success");
        redirigir('/login/');
    } else {
        setMensaje("Error al registrar el usuario. Inténtelo de nuevo.", "error");
        redirigir('/login/registro.php');
    }

} catch (PDOException $e) {
    error_log("Error en registro (registro.php): " . $e->getMessage());
    setMensaje("Error en el sistema durante el registro. Por favor, intente más tarde.", "error");
    redirigir('/login/registro.php');
} catch (Exception $e) {
    error_log("Error general en registro (registro.php): " . $e->getMessage());
    setMensaje("Ocurrió un error inesperado durante el registro. Por favor, intente más tarde.", "error");
    redirigir('/login/registro.php');
}
?>