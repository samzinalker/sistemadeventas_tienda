<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../utils/funciones_globales.php';
require_once __DIR__ . '/../../models/AlmacenModel.php';
require_once __DIR__ . '/../../models/CategoriaModel.php';

$response = ['status' => 'error', 'message' => 'Error desconocido al actualizar.'];

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Debe iniciar sesión.';
    $response['redirectTo'] = $URL . '/login/';
    echo json_encode($response);
    exit();
}
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método no permitido.';
    echo json_encode($response);
    exit();
}

$id_producto = filter_var($_POST['id_producto_update'] ?? null, FILTER_VALIDATE_INT);
if (!$id_producto) {
    $response['message'] = 'ID de producto no válido.';
    echo json_encode($response);
    exit();
}

// Validaciones (similares a create, pero para campos _update)
$nombre = trim($_POST['nombre_update'] ?? '');
$id_categoria = filter_var($_POST['id_categoria_update'] ?? null, FILTER_VALIDATE_INT);
// ... más validaciones para stock, precios, fecha ...

if (empty($nombre) || !$id_categoria /* || otras validaciones fallan */) {
    $response['message'] = 'Faltan campos requeridos o tienen formato incorrecto para actualizar.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit();
}

try {
    $almacenModel = new AlmacenModel($pdo);
    $categoriaModel = new CategoriaModel($pdo);

    $producto_existente = $almacenModel->getProductoByIdAndUsuarioId($id_producto, $id_usuario_logueado);
    if (!$producto_existente) {
        $response['message'] = 'Producto no encontrado o no tienes permiso.';
        echo json_encode($response);
        exit();
    }
    if (!$categoriaModel->getCategoriaByIdAndUsuarioId($id_categoria, $id_usuario_logueado)) {
        $response['message'] = 'Categoría seleccionada no válida.';
        echo json_encode($response);
        exit();
    }

    $datos_actualizar = [
        'nombre' => $nombre,
        'id_categoria' => $id_categoria,
        'descripcion' => trim($_POST['descripcion_update'] ?? $producto_existente['descripcion']),
        'stock' => filter_var($_POST['stock_update'] ?? $producto_existente['stock'], FILTER_VALIDATE_INT),
        'stock_minimo' => filter_var($_POST['stock_minimo_update'] ?? $producto_existente['stock_minimo'], FILTER_VALIDATE_INT),
        'stock_maximo' => filter_var($_POST['stock_maximo_update'] ?? $producto_existente['stock_maximo'], FILTER_VALIDATE_INT),
        'precio_compra' => filter_var($_POST['precio_compra_update'] ?? $producto_existente['precio_compra'], FILTER_VALIDATE_FLOAT),
        'precio_venta' => filter_var($_POST['precio_venta_update'] ?? $producto_existente['precio_venta'], FILTER_VALIDATE_FLOAT),
        'fecha_ingreso' => $_POST['fecha_ingreso_update'] ?? $producto_existente['fecha_ingreso'],
        'fyh_actualizacion' => $fechaHora
    ];

    $nombre_imagen_actual = $producto_existente['imagen'];
    
    if (isset($_FILES['imagen_producto_update']) && $_FILES['imagen_producto_update']['error'] == UPLOAD_ERR_OK) {
        $directorio_base_imagenes = __DIR__ . '/../../../almacen/img_productos/';
        $nombre_temporal_imagen = $_FILES['imagen_producto_update']['tmp_name'];
        $extension_imagen = strtolower(pathinfo($_FILES['imagen_producto_update']['name'], PATHINFO_EXTENSION));
        $nombre_unico_imagen = $producto_existente['codigo'] . "_" . time() . "." . $extension_imagen; // Usa el código existente
        $ruta_completa_imagen = $directorio_base_imagenes . $nombre_unico_imagen;
        $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($extension_imagen, $tipos_permitidos) && $_FILES['imagen_producto_update']['size'] <= 2097152) {
            if (move_uploaded_file($nombre_temporal_imagen, $ruta_completa_imagen)) {
                // Borrar imagen anterior si no es la default y existe
                if ($nombre_imagen_actual && $nombre_imagen_actual != 'default_product.png' && file_exists($directorio_base_imagenes . $nombre_imagen_actual)) {
                    unlink($directorio_base_imagenes . $nombre_imagen_actual);
                }
                $datos_actualizar['imagen'] = $nombre_unico_imagen; // Actualizar con la nueva imagen
            } else { /* Error al mover archivo */ }
        } else { /* Error tipo/tamaño */ }
    }
    // Si no se subió nueva imagen, no se añade 'imagen' a $datos_actualizar, por lo que el modelo no la tocará.

    if ($almacenModel->actualizarProducto($id_producto, $id_usuario_logueado, $datos_actualizar)) {
        $response['status'] = 'success';
        $response['message'] = "Producto '" . sanear($nombre) . "' actualizado.";
        // $response['updated_data'] = $almacenModel->getProductoByIdAndUsuarioId($id_producto, $id_usuario_logueado);
    } else {
        $response['message'] = "No se pudo actualizar el producto.";
    }

} catch (Exception $e) {
    error_log("Error en update_producto.php: " . $e->getMessage());
    $response['message'] = "Error del servidor al actualizar.";
}

// setMensaje($response['message'], $response['status']);
echo json_encode($response);
?>