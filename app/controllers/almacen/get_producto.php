<?php
// Resumen: Este script maneja solicitudes GET para obtener los detalles de un producto específico.
// Verifica la sesión del usuario, valida el ID del producto, y utiliza AlmacenModel
// para obtener los datos del producto, asegurándose de que pertenezca al usuario logueado.
// Devuelve una respuesta JSON con el estado y los datos del producto o un mensaje de error.

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php'; // $URL, $pdo
// No necesitas funciones_globales.php aquí si no las usas directamente.
require_once __DIR__ . '/../../models/AlmacenModel.php'; // Solo AlmacenModel es necesario aquí

$response = ['status' => 'error', 'message' => 'Producto no encontrado o acceso denegado.'];

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Debe iniciar sesión para ver los detalles.';
    $response['redirectTo'] = $URL . '/login/'; // Para que el JS pueda redirigir
    echo json_encode($response);
    exit();
}
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

if (!isset($_GET['id_producto'])) { // Asegúrate que el JS envía 'id_producto' como parámetro GET
    $response['message'] = 'ID de producto no proporcionado.';
    echo json_encode($response);
    exit();
}

$id_producto = filter_var($_GET['id_producto'], FILTER_VALIDATE_INT);
if (!$id_producto) {
    $response['message'] = 'ID de producto no válido.';
    echo json_encode($response);
    exit();
}

try {
    $almacenModel = new AlmacenModel($pdo);
    $producto = $almacenModel->getProductoByIdAndUsuarioId($id_producto, $id_usuario_logueado);

    if ($producto) {
        // Construir la URL completa de la imagen
        $nombre_imagen = $producto['imagen'] ?: 'default_product.png'; // Usa default si no hay imagen
        $producto['imagen_url'] = rtrim($URL, '/') . '/almacen/img_productos/' . $nombre_imagen;
        
        // Podrías formatear fechas aquí si lo prefieres en lugar de en JS
        // Ejemplo:
        // $producto['fecha_ingreso_formateada'] = date('d/m/Y', strtotime($producto['fecha_ingreso']));
        // $producto['fyh_creacion_formateada'] = date('d/m/Y H:i:s', strtotime($producto['fyh_creacion']));
        // $producto['fyh_actualizacion_formateada'] = ($producto['fyh_actualizacion'] && $producto['fyh_actualizacion'] !== '0000-00-00 00:00:00') ? date('d/m/Y H:i:s', strtotime($producto['fyh_actualizacion'])) : 'N/A';


        $response['status'] = 'success';
        $response['data'] = $producto; // Aquí van todos los datos del producto
        unset($response['message']); // Limpiar mensaje de error si todo fue bien
    }
    // Si $producto es false, se usa el $response['message'] por defecto: 'Producto no encontrado...'
} catch (PDOException $e) {
    error_log("Error de BD en get_producto.php: " . $e->getMessage());
    $response['message'] = "Error en el sistema al obtener datos del producto (BD).";
} catch (Exception $e) {
    error_log("Error general en get_producto.php: " . $e->getMessage());
    $response['message'] = "Error inesperado del servidor al obtener datos del producto.";
}

echo json_encode($response);
?>