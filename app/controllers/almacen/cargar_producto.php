<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica autenticación
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ' . $URL . '/login.php');
    exit();
}

$id_producto_get = $_GET['id'];
$id_usuario_actual = $_SESSION['id_usuario'];

// Consulta siempre restringida al usuario actual - sin excepciones
$sql_productos = "SELECT *, cat.nombre_categoria as categoria, u.email as email, u.id_usuario as id_usuario
                  FROM tb_almacen as a 
                  INNER JOIN tb_categorias as cat ON a.id_categoria = cat.id_categoria 
                  INNER JOIN tb_usuarios as u ON u.id_usuario = a.id_usuario 
                  WHERE a.id_producto = :id_producto AND a.id_usuario = :id_usuario";

$query_productos = $pdo->prepare($sql_productos);
$query_productos->bindParam(':id_producto', $id_producto_get, PDO::PARAM_INT);
$query_productos->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT);
$query_productos->execute();

// Verificar si se encontró el producto
if ($query_productos->rowCount() === 0) {
    // Producto no encontrado o no tiene permisos
    $_SESSION['mensaje'] = "No tienes acceso a este producto o no existe";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/almacen/');
    exit();
}

$productos_datos = $query_productos->fetchAll(PDO::FETCH_ASSOC);

foreach ($productos_datos as $productos_dato) {
    $codigo = $productos_dato['codigo'];
    $nombre_categoria = $productos_dato['categoria'];
    $nombre = $productos_dato['nombre'];
    $email = $productos_dato['email'];
    $id_usuario = $productos_dato['id_usuario'];
    $descripcion = $productos_dato['descripcion'];
    $stock = $productos_dato['stock'];
    $stock_minimo = $productos_dato['stock_minimo'];
    $stock_maximo = $productos_dato['stock_maximo'];
    $precio_compra = $productos_dato['precio_compra'];
    $precio_venta = $productos_dato['precio_venta'];
    $fecha_ingreso = $productos_dato['fecha_ingreso'];
    $imagen = $productos_dato['imagen'];
}