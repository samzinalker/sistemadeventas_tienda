<?php
// Verificar autenticación
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ' . $URL . '/login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_venta_get = isset($_GET['id_venta']) ? $_GET['id_venta'] : 0;

if (!$id_venta_get) {
    $_SESSION['mensaje'] = "ID de venta no especificado";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/ventas/');
    exit();
}

// Consultar la venta asegurándose que pertenezca al usuario actual
$sql_ventas = "SELECT ve.*, cli.nombre_cliente as nombre_cliente
               FROM tb_ventas as ve 
               INNER JOIN tb_clientes as cli ON cli.id_cliente = ve.id_cliente 
               WHERE ve.id_venta = :id_venta AND ve.id_usuario = :id_usuario";
$query_ventas = $pdo->prepare($sql_ventas);
$query_ventas->bindParam(':id_venta', $id_venta_get, PDO::PARAM_INT);
$query_ventas->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$query_ventas->execute();

// Verificar si la venta existe y pertenece al usuario
if ($query_ventas->rowCount() === 0) {
    $_SESSION['mensaje'] = "La venta solicitada no existe o no tienes permiso para verla";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/ventas/');
    exit();
}

$ventas_datos = $query_ventas->fetchAll(PDO::FETCH_ASSOC);
foreach($ventas_datos as $ventas_dato) {
    $nro_venta = $ventas_dato['nro_venta'];
    $id_cliente = $ventas_dato['id_cliente'];
}

// Consultar detalles de productos en el carrito para esta venta
$sql_carrito = "SELECT c.*, 
                pro.nombre as nombre_producto, 
                pro.descripcion as descripcion, 
                pro.precio_venta as precio_venta, 
                pro.stock as stock 
                FROM tb_carrito as c 
                INNER JOIN tb_almacen as pro ON c.id_producto = pro.id_producto 
                WHERE c.nro_venta = :nro_venta AND c.id_usuario = :id_usuario 
                ORDER BY c.id_carrito ASC";
$query_carrito = $pdo->prepare($sql_carrito);
$query_carrito->bindParam(':nro_venta', $nro_venta, PDO::PARAM_INT);
$query_carrito->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$query_carrito->execute();