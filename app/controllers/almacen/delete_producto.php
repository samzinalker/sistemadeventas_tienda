<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../utils/funciones_globales.php';
require_once __DIR__ . '/../../models/AlmacenModel.php';

$response = ['status' => 'error', 'message' => 'Error desconocido al eliminar.'];

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Debe iniciar sesión.';
    $response['redirectTo'] = $URL . '/login/';
    echo json_encode($response);
    exit();
}
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_producto'])) {
    $response['message'] = 'Solicitud no válida.';
    echo json_encode($response);
    exit();
}

$id_producto = filter_var($_POST['id_producto'], FILTER_VALIDATE_INT);
if (!$id_producto) {
    $response['message'] = 'ID de producto no válido.';
    echo json_encode($response);
    exit();
}

try {
    $almacenModel = new AlmacenModel($pdo);
    // El modelo ya verifica propiedad y si está en uso antes de devolver el nombre de la imagen.
    $nombre_imagen_a_borrar = $almacenModel->eliminarProducto($id_producto, $id_usuario_logueado);

    if ($nombre_imagen_a_borrar !== null) { // null indica error, no pertenencia, o en uso.
        if ($nombre_imagen_a_borrar && $nombre_imagen_a_borrar != 'default_product.png') {
            $ruta_imagen = __DIR__ . '/../../../almacen/img_productos/' . $nombre_imagen_a_borrar;
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
        }
        $response['status'] = 'success';
        $response['message'] = "Producto eliminado correctamente.";
    } else {
        // Consultar si el producto existe y pertenece al usuario para dar un mensaje más específico
        $producto_existente = $almacenModel->getProductoByIdAndUsuarioId($id_producto, $id_usuario_logueado);
        if (!$producto_existente) {
            $response['message'] = "Producto no encontrado o no tienes permiso.";
        } elseif ($almacenModel->productoEnUso($id_producto)) {
            $response['message'] = "No se puede eliminar, el producto está en uso.";
            $response['status'] = 'warning';
        } else {
            $response['message'] = "No se pudo eliminar el producto.";
        }
    }

} catch (Exception $e) {
    error_log("Error en delete_producto.php: " . $e->getMessage());
    $response['message'] = "Error del servidor al eliminar.";
}

// setMensaje($response['message'], $response['status']);
echo json_encode($response);
?>