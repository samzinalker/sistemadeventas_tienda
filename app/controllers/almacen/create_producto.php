<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php'; // Define $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../utils/funciones_globales.php'; // Para sanear() y setMensaje()
require_once __DIR__ . '/../../models/AlmacenModel.php';
require_once __DIR__ . '/../../models/CategoriaModel.php';

$response = ['status' => 'error', 'message' => 'Error desconocido al crear el producto.'];

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

// Validaciones (ejemplo simplificado, expandir según necesidad)
$nombre = trim($_POST['nombre'] ?? '');
$id_categoria = filter_var($_POST['id_categoria'] ?? null, FILTER_VALIDATE_INT);
$stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$precio_compra = filter_var($_POST['precio_compra'] ?? null, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.01]]);
$precio_venta = filter_var($_POST['precio_venta'] ?? null, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.01]]);
$fecha_ingreso = $_POST['fecha_ingreso'] ?? ''; // Validar formato si es necesario

if (empty($nombre) || !$id_categoria || $stock === false || $precio_compra === false || $precio_venta === false || empty($fecha_ingreso)) {
    $response['message'] = 'Faltan campos requeridos o tienen formato incorrecto.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit();
}

$descripcion = trim($_POST['descripcion'] ?? '');
$stock_minimo = filter_var($_POST['stock_minimo'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$stock_maximo = filter_var($_POST['stock_maximo'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
if ($stock_maximo !== false && $stock_maximo > 0 && $stock_minimo !== false && $stock_minimo > $stock_maximo) {
    $response['message'] = 'El stock mínimo no puede ser mayor al stock máximo.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit();
}


try {
    $almacenModel = new AlmacenModel($pdo);
    $categoriaModel = new CategoriaModel($pdo);

    if (!$categoriaModel->getCategoriaByIdAndUsuarioId($id_categoria, $id_usuario_logueado)) {
        $response['message'] = 'Categoría no válida o no pertenece al usuario.';
        echo json_encode($response);
        exit();
    }

    $codigo_producto = $almacenModel->generarCodigoProducto($id_usuario_logueado);
    $nombre_imagen_bd = 'default_product.png'; // Imagen por defecto

    if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] == UPLOAD_ERR_OK) {
        $directorio_base_imagenes = __DIR__ . '/../../../almacen/img_productos/';
        if (!is_dir($directorio_base_imagenes)) mkdir($directorio_base_imagenes, 0775, true);
        
        $nombre_temporal_imagen = $_FILES['imagen_producto']['tmp_name'];
        $extension_imagen = strtolower(pathinfo($_FILES['imagen_producto']['name'], PATHINFO_EXTENSION));
        $nombre_unico_imagen = $codigo_producto . "_" . time() . "." . $extension_imagen;
        $ruta_completa_imagen = $directorio_base_imagenes . $nombre_unico_imagen;
        $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($extension_imagen, $tipos_permitidos) && $_FILES['imagen_producto']['size'] <= 2097152 /* 2MB */) {
            if (move_uploaded_file($nombre_temporal_imagen, $ruta_completa_imagen)) {
                $nombre_imagen_bd = $nombre_unico_imagen;
            } else {
                $response['message'] = "Error al guardar la imagen.";
                echo json_encode($response); exit();
            }
        } else {
            $response['message'] = "Imagen no válida (tipo o tamaño).";
            $response['status'] = 'warning';
            echo json_encode($response); exit();
        }
    }

    $datos_producto = [
        'codigo' => $codigo_producto, 'nombre' => $nombre, 'descripcion' => $descripcion,
        'stock' => $stock, 'stock_minimo' => $stock_minimo, 'stock_maximo' => $stock_maximo,
        'precio_compra' => $precio_compra, 'precio_venta' => $precio_venta,
        'fecha_ingreso' => $fecha_ingreso, 'imagen' => $nombre_imagen_bd,
        'id_usuario' => $id_usuario_logueado, 'id_categoria' => $id_categoria,
        'fyh_creacion' => $fechaHora, 'fyh_actualizacion' => $fechaHora
    ];

    $creadoId = $almacenModel->crearProducto($datos_producto);
    if ($creadoId) {
        $response['status'] = 'success';
        $response['message'] = "Producto '" . sanear($nombre) . "' registrado (Cod: " . $codigo_producto . ").";
        // $response['new_data'] = $almacenModel->getProductoByIdAndUsuarioId((int)$creadoId, $id_usuario_logueado); // Para actualizar DataTables dinámicamente
    } else {
        $response['message'] = "No se pudo registrar el producto.";
    }

} catch (PDOException $e) {
    error_log("PDO Error en create_producto.php: " . $e->getMessage());
    $response['message'] = "Error de base de datos.";
} catch (Exception $e) {
    error_log("General Error en create_producto.php: " . $e->getMessage());
    $response['message'] = "Error inesperado del servidor.";
}

// setMensaje($response['message'], $response['status']); // Para recarga de página
echo json_encode($response);
?>