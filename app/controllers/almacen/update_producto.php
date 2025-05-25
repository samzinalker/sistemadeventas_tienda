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

// Validaciones completas
$nombre = trim($_POST['nombre_update'] ?? '');
$id_categoria = filter_var($_POST['id_categoria_update'] ?? null, FILTER_VALIDATE_INT);
$stock = filter_var($_POST['stock_update'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$precio_compra = filter_var($_POST['precio_compra_update'] ?? null, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.01]]);
$precio_venta = filter_var($_POST['precio_venta_update'] ?? null, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.01]]);
$fecha_ingreso = $_POST['fecha_ingreso_update'] ?? '';

if (empty($nombre) || !$id_categoria || $stock === false || $precio_compra === false || $precio_venta === false || empty($fecha_ingreso)) {
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
        'stock' => $stock,
        'stock_minimo' => filter_var($_POST['stock_minimo_update'] ?? $producto_existente['stock_minimo'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]),
        'stock_maximo' => filter_var($_POST['stock_maximo_update'] ?? $producto_existente['stock_maximo'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]),
        'precio_compra' => $precio_compra,
        'precio_venta' => $precio_venta,
        'iva_predeterminado' => filter_var($_POST['iva_predeterminado_update'] ?? $producto_existente['iva_predeterminado'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]), // AGREGADO
        'fecha_ingreso' => $fecha_ingreso,
        'fyh_actualizacion' => $fechaHora
    ];

    $nombre_imagen_actual = $producto_existente['imagen'];
    
    if (isset($_FILES['imagen_producto_update']) && $_FILES['imagen_producto_update']['error'] == UPLOAD_ERR_OK) {
        $directorio_base_imagenes = __DIR__ . '/../../../almacen/img_productos/';
        $nombre_temporal_imagen = $_FILES['imagen_producto_update']['tmp_name'];
        $extension_imagen = strtolower(pathinfo($_FILES['imagen_producto_update']['name'], PATHINFO_EXTENSION));
        $nombre_unico_imagen = $producto_existente['codigo'] . "_" . time() . "." . $extension_imagen;
        $ruta_completa_imagen = $directorio_base_imagenes . $nombre_unico_imagen;
        $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($extension_imagen, $tipos_permitidos) && $_FILES['imagen_producto_update']['size'] <= 2097152) {
            if (move_uploaded_file($nombre_temporal_imagen, $ruta_completa_imagen)) {
                if ($nombre_imagen_actual && $nombre_imagen_actual != 'default_product.png' && file_exists($directorio_base_imagenes . $nombre_imagen_actual)) {
                    unlink($directorio_base_imagenes . $nombre_imagen_actual);
                }
                $datos_actualizar['imagen'] = $nombre_unico_imagen;
            }
        }
    }

    if ($almacenModel->actualizarProducto($id_producto, $id_usuario_logueado, $datos_actualizar)) {
        $response['status'] = 'success';
        $response['message'] = "Producto '" . sanear($nombre) . "' actualizado.";
    } else {
        $response['message'] = "No se pudo actualizar el producto.";
    }

} catch (PDOException $e) {
    error_log("PDO Error en update_producto.php: " . $e->getMessage());
    $response['message'] = "Error de base de datos: " . $e->getMessage(); // Para debugging
} catch (Exception $e) {
    error_log("General Error en update_producto.php: " . $e->getMessage());
    $response['message'] = "Error del servidor al actualizar: " . $e->getMessage(); // Para debugging
}

echo json_encode($response);
?>