<?php
// 1. Incluir configuración y dependencias principales
require_once __DIR__ . '/../../config.php'; // Para $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../utils/funciones_globales.php'; // Para setMensaje, redirigir
require_once __DIR__ . '/../../models/UsuarioModel.php';    // Modelo de Usuario

// 2. Iniciar sesión (setMensaje y la propia sesión de usuario lo requieren)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido.", "error");
    redirigir('/login/'); // Redirigir a la página de login
}

// 4. Obtener y sanear datos del formulario
$email_ingresado = trim($_POST['email'] ?? '');
$password_ingresada = $_POST['password_user'] ?? '';

if (empty($email_ingresado) || empty($password_ingresada)) {
    setMensaje("Email y contraseña son requeridos.", "warning");
    redirigir('/login/');
}

// 5. Lógica de autenticación usando el Modelo
try {
    $usuarioModel = new UsuarioModel($pdo, $URL);
    $usuario_data = $usuarioModel->getUsuarioByEmail($email_ingresado);

    if ($usuario_data) {
        // Usuario encontrado, verificar contraseña
        if (password_verify($password_ingresada, $usuario_data['password_user'])) {
            // Contraseña correcta: Iniciar sesión
            $_SESSION['id_usuario'] = $usuario_data['id_usuario'];
            $_SESSION['sesion_email'] = $usuario_data['email']; // Usado por layout/sesion.php
            $_SESSION['rol'] = $usuario_data['nombre_rol'];   // UsuarioModel devuelve 'nombre_rol'
            $_SESSION['nombres'] = $usuario_data['nombres'];

            // Actualizar fecha y hora de última actualización (o login)
            // $fechaHora global de config.php
            $usuarioModel->actualizarFechaHoraLogin($usuario_data['id_usuario'], $fechaHora);
            
            redirigir('/index.php'); // Redirigir al panel principal
        } else {
            // Contraseña incorrecta
            setMensaje("Contraseña incorrecta. Inténtelo de nuevo.", "error");
            redirigir('/login/');
        }
    } else {
        // Usuario no encontrado
        setMensaje("Usuario no encontrado. Verifique el email ingresado.", "error");
        redirigir('/login/');
    }

} catch (PDOException $e) {
    // Error en la base de datos
    error_log("Error en login (ingreso.php): " . $e->getMessage()); // Registrar el error real
    setMensaje("Error en el sistema al intentar iniciar sesión. Por favor, intente más tarde.", "error");
    redirigir('/login/');
} catch (Exception $e) {
    // Otros errores inesperados
    error_log("Error general en login (ingreso.php): " . $e->getMessage());
    setMensaje("Ocurrió un error inesperado. Por favor, intente más tarde.", "error");
    redirigir('/login/');
}

?>