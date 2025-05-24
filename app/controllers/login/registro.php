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
$email = trim($_POST['email'] ?? ''); // Este será el "usuario" para login
$password = $_POST['password_user'] ?? '';
$password_repeat = $_POST['repassword'] ?? ''; // Corregido de 'repassword' a 'password_repeat' si es necesario o viceversa, asegurar consistencia con el form. El form usa 'repassword'.
                                              // Mantendré 'repassword' para coincidir con el form que me pasaste.

// Guardar datos en sesión para repoblar el formulario en caso de error
$_SESSION['form_data_registro'] = $_POST;

// 5. Validaciones
$campos_requeridos = ['nombres', 'email', 'password_user', 'repassword'];
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
list($password_hash, $error_password) = procesarPassword($password, $password_repeat, 6); // procesarPassword ya valida longitud y coincidencia

if ($error_password) {
    setMensaje($error_password, "error"); // El mensaje de error viene de procesarPassword
    redirigir('/login/registro.php');
}

// 6. Lógica de registro usando el Modelo
try {
    $usuarioModel = new UsuarioModel($pdo, $URL);

    // Verificar si el email (usuario) ya existe
    if ($usuarioModel->emailExiste($email)) {
        setMensaje("El nombre de usuario (email) '" . sanear($email) . "' ya está registrado. Intente con otro.", "warning");
        redirigir('/login/registro.php');
    }

    // Determinar el ID del rol para nuevos usuarios (ej. "vendedor")
    // Este ID puede venir de una constante, configuración, o una consulta si es dinámico.
    // Por ahora, usamos el ID 7 (vendedor) como en tu código original.
    $id_rol_defecto = 7; 
    // En una mejora futura, podrías obtener el ID del rol "vendedor" por su nombre desde RolModel.

    // $fechaHora global de config.php
    $creado = $usuarioModel->crearUsuario($nombres, $email, $password_hash, $id_rol_defecto, $fechaHora);

    if ($creado) {
        unset($_SESSION['form_data_registro']); // Limpiar datos del formulario de la sesión
        setMensaje("Usuario registrado correctamente. Ahora puede iniciar sesión.", "success");
        redirigir('/login/'); // Redirigir a la página de login
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
?>// En login/registro.php, dentro del formulario:
    <?php $form_data = $_SESSION['form_data_registro'] ?? []; ?>
<input type="text" name="nombres" class="form-control" placeholder="Nombre completo" required value="<?php echo sanear($form_data['nombres'] ?? ''); ?>">
    // ... y así para otros campos.
    <?php unset($_SESSION['form_data_registro']); // Limpiar después de usar ?>