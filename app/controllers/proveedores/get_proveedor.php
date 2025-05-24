<?php
// Resumen: Controlador para obtener los datos de un proveedor específico.
// Recibe el ID del proveedor vía GET, utiliza ProveedorModel para obtener los datos
// (verificando propiedad) y devuelve una respuesta JSON.

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/ProveedorModel.php';

$response = ['status' => 'error', 'message' => 'Proveedor no encontrado o acceso denegado.'];

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Debe iniciar sesión para ver los detalles.';
    $response['redirectTo'] = $URL . '/login/';
    echo json_encode($response);
    exit();
}
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

if (!isset($_GET['id_proveedor'])) {
    $response['message'] = 'ID de proveedor no proporcionado.';
    echo json_encode($response);
    exit();
}

$id_proveedor = filter_var($_GET['id_proveedor'], FILTER_VALIDATE_INT);
if (!$id_proveedor) {
    $response['message'] = 'ID de proveedor no válido.';
    echo json_encode($response);
    exit();
}

try {
    $proveedorModel = new ProveedorModel($pdo, $URL);
    $proveedor = $proveedorModel->getProveedorByIdAndUsuarioId($id_proveedor, $id_usuario_logueado);

    if ($proveedor) {
        $response['status'] = 'success';
        $response['data'] = $proveedor;
        unset($response['message']); 
    }
    // Si no se encuentra, el mensaje de error por defecto es adecuado.

} catch (PDOException $e) {
    error_log("PDO Error en get_proveedor.php: " . $e->getMessage());
    $response['message'] = "Error de BD al obtener datos del proveedor.";
} catch (Exception $e) {
    error_log("General Error en get_proveedor.php: " . $e->getMessage());
    $response['message'] = "Error inesperado del servidor al obtener datos del proveedor.";
}

echo json_encode($response);
?>