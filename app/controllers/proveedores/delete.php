<?php
// Resumen: Controlador para eliminar un proveedor.
// Recibe el ID del proveedor vía POST, utiliza ProveedorModel para la eliminación
// (que verifica propiedad) y devuelve una respuesta JSON.

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
// funciones_globales.php no es estrictamente necesario aquí si solo devolvemos JSON
require_once __DIR__ . '/../../models/ProveedorModel.php';

$response = ['status' => 'error', 'message' => 'Error desconocido al eliminar el proveedor.'];

if (session_status() === PHP_SESSION_NONE) { session_start(); }

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

$id_proveedor = filter_var($_POST['id_proveedor'] ?? null, FILTER_VALIDATE_INT);
if (!$id_proveedor) {
    $response['message'] = 'ID de proveedor no válido o no proporcionado.';
    echo json_encode($response);
    exit();
}

try {
    $proveedorModel = new ProveedorModel($pdo, $URL);

    // Opcional: Antes de eliminar, obtener el nombre para el mensaje de éxito.
    // $proveedor = $proveedorModel->getProveedorByIdAndUsuarioId($id_proveedor, $id_usuario_logueado);
    // $nombre_proveedor_eliminado = $proveedor ? $proveedor['nombre_proveedor'] : "desconocido";

    if ($proveedorModel->eliminarProveedor($id_proveedor, $id_usuario_logueado)) {
        $response['status'] = 'success';
        $response['message'] = "Proveedor eliminado correctamente."; // Podrías añadir $nombre_proveedor_eliminado aquí
    } else {
        // Esto podría ser porque el proveedor no existe, no pertenece al usuario, o está en uso (si se implementa esa verificación).
        $response['message'] = "No se pudo eliminar el proveedor. Puede que no exista, no le pertenezca, o esté en uso en compras.";
        // Para un mensaje más específico, podrías llamar a getProveedorByIdAndUsuarioId y productoEnUso (si existe)
    }

} catch (PDOException $e) {
    error_log("PDO Error en delete_proveedor.php: " . $e->getMessage());
    $response['message'] = "Error de base de datos al eliminar el proveedor.";
} catch (Exception $e) {
    error_log("General Error en delete_proveedor.php: " . $e->getMessage());
    $response['message'] = "Error inesperado del servidor al eliminar el proveedor.";
}

echo json_encode($response);
?>