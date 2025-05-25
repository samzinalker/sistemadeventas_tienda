<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/ComprasModel.php';

$response = ['status' => 'error', 'message' => 'Error desconocido al eliminar la compra.'];

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Debe iniciar sesión para esta acción.';
    $response['redirectTo'] = $URL . '/login/';
    echo json_encode($response);
    exit();
}
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método de solicitud no permitido.';
    echo json_encode($response);
    exit();
}

$id_compra = filter_var($_POST['id_compra'] ?? null, FILTER_VALIDATE_INT);
if (!$id_compra) {
    $response['message'] = 'ID de compra no válido o no proporcionado.';
    echo json_encode($response);
    exit();
}

try {
    $compraModel = new CompraModel($pdo);
    
    // Verificar que la compra pertenezca al usuario antes de eliminar
    $compra = $compraModel->getCompraConDetallesPorId($id_compra, $id_usuario_logueado);
    if (!$compra) {
        $response['message'] = 'Compra no encontrada o no tiene permisos para eliminarla.';
        echo json_encode($response);
        exit();
    }
    
    if ($compraModel->eliminarCompra($id_compra, $id_usuario_logueado)) {
        $response['status'] = 'success';
        $response['message'] = "Compra #{$compra['cabecera']['codigo_compra_referencia']} eliminada correctamente.";
    } else {
        $response['message'] = "No se pudo eliminar la compra. Puede que esté siendo utilizada en otros registros.";
    }

} catch (PDOException $e) {
    error_log("PDO Error en delete_compra.php: " . $e->getMessage());
    $response['message'] = "Error de base de datos al eliminar la compra.";
} catch (Exception $e) {
    error_log("General Error en delete_compra.php: " . $e->getMessage());
    $response['message'] = "Error inesperado del servidor al eliminar la compra.";
}

echo json_encode($response);
?>