<?php
include ('../../config.php');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['mensaje'] = "Debes iniciar sesión para realizar esta acción";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_producto = $_POST['id_producto'] ?? null;

// Verificar que se recibió un ID de producto
if (!$id_producto) {
    $_SESSION['mensaje'] = "Error: No se especificó el producto a eliminar";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/almacen/');
    exit();
}

// Verificar si el producto está en uso en la tabla de ventas o compras
$sql_check_uso = "SELECT COUNT(*) FROM tb_carrito WHERE id_producto = :id_producto
                 UNION ALL
                 SELECT COUNT(*) FROM tb_compras WHERE id_producto = :id_producto";
$query_check_uso = $pdo->prepare($sql_check_uso);
$query_check_uso->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
$query_check_uso->execute();
$resultados = $query_check_uso->fetchAll(PDO::FETCH_COLUMN);

if ($resultados[0] > 0 || $resultados[1] > 0) {
    $_SESSION['mensaje'] = "No se puede eliminar el producto porque está siendo utilizado en ventas o compras";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/almacen/');
    exit();
}

// Verificar propiedad del producto - SIEMPRE verificar sin excepciones
$sql_check = "SELECT COUNT(*) FROM tb_almacen 
             WHERE id_producto = :id_producto AND id_usuario = :id_usuario";
$query_check = $pdo->prepare($sql_check);
$query_check->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
$query_check->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$query_check->execute();

if ($query_check->fetchColumn() === 0) {
    $_SESSION['mensaje'] = "No tienes permiso para eliminar este producto";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/almacen/');
    exit();
}

// Obtener imagen antes de eliminar
$sql_imagen = "SELECT imagen FROM tb_almacen WHERE id_producto = :id_producto AND id_usuario = :id_usuario";
$query_imagen = $pdo->prepare($sql_imagen);
$query_imagen->bindParam(':id_producto', $id_producto);
$query_imagen->bindParam(':id_usuario', $id_usuario);
$query_imagen->execute();
$imagen = $query_imagen->fetchColumn();

// Eliminar de la base de datos - SIEMPRE con restricción de usuario
$sql_delete = "DELETE FROM tb_almacen WHERE id_producto = :id_producto AND id_usuario = :id_usuario";
$query_delete = $pdo->prepare($sql_delete);
$query_delete->bindParam(':id_producto', $id_producto);
$query_delete->bindParam(':id_usuario', $id_usuario);

if ($query_delete->execute()) {
    // Eliminar la imagen física si existe y no es la predeterminada
    if ($imagen && $imagen != 'default.png') {
        $ruta_imagen = "../../../almacen/img_productos/" . $imagen;
        if (file_exists($ruta_imagen)) {
            unlink($ruta_imagen);
        }
    }
    
    $_SESSION['mensaje'] = "Producto eliminado correctamente";
    $_SESSION['icono'] = "success";
} else {
    $_SESSION['mensaje'] = "Error al eliminar el producto";
    $_SESSION['icono'] = "error";
}

header('Location: ' . $URL . '/almacen/');