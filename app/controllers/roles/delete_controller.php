<?php
// 1. Iniciar sesión si es necesario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir config.php PRIMERO
require_once __DIR__ . '/../../config.php'; // Para $pdo, $URL, $fechaHora

// 3. Incluir las demás utilidades y modelos
// require_once __DIR__ . '/../../utils/Validator.php'; // No es estrictamente necesario para delete simple
require_once __DIR__ . '/../../utils/funciones_globales.php'; // Para setMensaje, redirigir, sanear
require_once __DIR__ . '/../../models/RolModel.php';

// 4. Verificar sesión y permisos (administrador)
if (!isset($_SESSION['id_usuario']) || strtolower(trim($_SESSION['rol'] ?? '')) !== 'administrador') {
    setMensaje("Acceso denegado. No tiene permisos para eliminar roles.", "error");
    redirigir('/'); 
}

// --- Lógica específica del controlador ---

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido. Método incorrecto.", "error");
    redirigir('/roles/'); 
}

// Validar que el ID del rol a eliminar llegue y sea un entero
if (!isset($_POST['id_rol_a_eliminar']) || !filter_var($_POST['id_rol_a_eliminar'], FILTER_VALIDATE_INT)) {
    setMensaje("ID de rol no válido o no proporcionado para eliminar.", "error");
    redirigir('/roles/');
}

$id_rol_eliminar = (int)$_POST['id_rol_a_eliminar'];

// No permitir eliminar el rol de administrador principal (ID 1 usualmente)
if ($id_rol_eliminar == 1) { // Asumiendo que ID 1 es el rol de administrador principal
    setMensaje("El rol 'administrador' principal no puede ser eliminado.", "warning");
    redirigir('/roles/');
}

// Instanciar el modelo de Rol
$rolModel = new RolModel($pdo);

// Intentar eliminar el rol
$rol_eliminado = $rolModel->eliminarRol($id_rol_eliminar);

if ($rol_eliminado) {
    setMensaje("Rol eliminado correctamente.", "success");
} else {
    // RolModel->eliminarRol devuelve false si el rol está en uso o si hubo otro error.
    // Podríamos obtener el rol para mostrar su nombre, pero podría ya no existir si hubo otro error.
    // Es mejor tener un mensaje genérico o mejorar RolModel para dar más detalles del error.
    $rol_existente = $rolModel->getRolById($id_rol_eliminar);
    if ($rol_existente) {
        setMensaje("Error al eliminar el rol '" . sanear($rol_existente['rol']) . "'. Es posible que esté asignado a uno o más usuarios.", "error");
    } else {
        setMensaje("Error al eliminar el rol. Es posible que ya no exista o esté en uso.", "error");
    }
}

redirigir('/roles/');
?>