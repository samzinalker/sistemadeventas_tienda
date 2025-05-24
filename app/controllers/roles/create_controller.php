<?php
// 1. Iniciar sesión si es necesario (para setMensaje y verificar sesión del admin)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir config.php PRIMERO
require_once __DIR__ . '/../../config.php'; // Para $pdo, $URL, $fechaHora

// 3. Incluir las demás utilidades y modelos
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/funciones_globales.php'; // Para setMensaje, redirigir, sanear
require_once __DIR__ . '/../../models/RolModel.php';

// 4. Verificar sesión y permisos (administrador)
// (Esto se hace en la vista que muestra el formulario, pero es bueno tenerlo aquí también como doble chequeo
//  o si se accede a este controlador directamente de forma malintencionada)
if (!isset($_SESSION['id_usuario']) || strtolower(trim($_SESSION['rol'] ?? '')) !== 'administrador') {
    setMensaje("Acceso denegado. No tiene permisos para crear roles.", "error");
    redirigir('/'); // Redirigir a la página principal o login
}

// --- Lógica específica del controlador ---

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido. Método incorrecto.", "error");
    redirigir('/roles/create.php'); // Redirigir de vuelta al formulario de creación
}

// Instanciar el modelo de Rol
$rolModel = new RolModel($pdo);

// Validar campos requeridos
$campos_requeridos = ['nombre_rol'];
$campos_faltantes = Validator::requiredFields($_POST, $campos_requeridos);

if (!empty($campos_faltantes)) {
    $campos_str = implode(', ', array_map('sanear', $campos_faltantes));
    setMensaje("El campo 'Nombre del Rol' es obligatorio.", "error");
    // Guardar datos del formulario en sesión para repoblar (mejora UX)
    $_SESSION['form_data_rol_create'] = $_POST; 
    redirigir('/roles/create.php');
}

// Obtener y limpiar datos del formulario
$nombre_rol = trim($_POST['nombre_rol']);

// Validaciones adicionales
if (empty($nombre_rol)) { // Doble chequeo por si el trim lo deja vacío
    setMensaje("El nombre del rol no puede estar vacío.", "error");
    $_SESSION['form_data_rol_create'] = $_POST;
    redirigir('/roles/create.php');
}

// Verificar si el nombre del rol ya existe
if ($rolModel->nombreRolExiste($nombre_rol)) {
    setMensaje("El nombre de rol '" . sanear($nombre_rol) . "' ya está registrado. Intente con otro.", "error");
    $_SESSION['form_data_rol_create'] = $_POST;
    redirigir('/roles/create.php');
}

// Crear el rol
// La variable $fechaHora viene de config.php (YYYY-MM-DD HH:MM:SS)
$creado = $rolModel->crearRol($nombre_rol, $fechaHora);

if ($creado) {
    unset($_SESSION['form_data_rol_create']); // Limpiar datos de formulario en sesión
    setMensaje("Rol '" . sanear($nombre_rol) . "' registrado correctamente.", "success");
    redirigir('/roles/'); // Redirigir al listado de roles
} else {
    setMensaje("Error al registrar el rol. Inténtelo de nuevo.", "error");
    $_SESSION['form_data_rol_create'] = $_POST;
    redirigir('/roles/create.php');
}
?>