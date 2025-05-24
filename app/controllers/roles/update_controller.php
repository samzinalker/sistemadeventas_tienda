<?php
// 1. Iniciar sesión si es necesario
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
if (!isset($_SESSION['id_usuario']) || strtolower(trim($_SESSION['rol'] ?? '')) !== 'administrador') {
    setMensaje("Acceso denegado. No tiene permisos para actualizar roles.", "error");
    redirigir('/'); 
}

// --- Lógica específica del controlador ---

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido. Método incorrecto.", "error");
    redirigir('/roles/'); 
}

if (!isset($_POST['id_rol']) || !filter_var($_POST['id_rol'], FILTER_VALIDATE_INT) || !isset($_POST['nombre_rol'])) {
    setMensaje("Datos incompletos para actualizar el rol.", "error");
    redirigir('/roles/');
}

$id_rol_actualizar = (int)$_POST['id_rol'];
$nombre_rol_nuevo = trim($_POST['nombre_rol']);

// Clave de sesión específica para este formulario de actualización
$form_data_key = 'form_data_rol_update_' . $id_rol_actualizar;
$_SESSION[$form_data_key] = ['rol' => $nombre_rol_nuevo]; // Guardar solo el nombre del rol

if ($id_rol_actualizar == 1) { 
    setMensaje("El rol 'administrador' principal no puede ser modificado.", "warning");
    unset($_SESSION[$form_data_key]); 
    redirigir('/roles/');
}

$rolModel = new RolModel($pdo);

if (empty($nombre_rol_nuevo)) {
    setMensaje("El nombre del rol no puede estar vacío.", "error");
    redirigir('/roles/update.php?id=' . $id_rol_actualizar);
}

if ($rolModel->nombreRolExiste($nombre_rol_nuevo, $id_rol_actualizar)) {
    setMensaje("El nombre de rol '" . sanear($nombre_rol_nuevo) . "' ya está registrado para otro rol. Intente con otro.", "error");
    redirigir('/roles/update.php?id=' . $id_rol_actualizar);
}

$actualizado = $rolModel->actualizarRol($id_rol_actualizar, $nombre_rol_nuevo, $fechaHora);

if ($actualizado) {
    unset($_SESSION[$form_data_key]); 
    setMensaje("Rol '" . sanear($nombre_rol_nuevo) . "' actualizado correctamente.", "success");
    redirigir('/roles/'); 
} else {
    setMensaje("Error al actualizar el rol. Inténtelo de nuevo.", "error");
    redirigir('/roles/update.php?id=' . $id_rol_actualizar);
}
?>