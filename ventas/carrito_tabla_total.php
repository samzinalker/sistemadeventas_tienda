<?php
include('../app/config.php');
if (session_status() == PHP_SESSION_NONE) session_start();

$id_usuario_actual = $_SESSION['id_usuario'];
$sql_carrito = "SELECT c.*, pro.precio_venta 
                FROM tb_carrito c 
                INNER JOIN tb_almacen pro ON c.id_producto = pro.id_producto
                WHERE c.id_usuario = :id_usuario AND c.nro_venta = 0";
$query_carrito = $pdo->prepare($sql_carrito);
$query_carrito->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT);
$query_carrito->execute();
$carrito_datos = $query_carrito->fetchAll(PDO::FETCH_ASSOC);

$precio_total = 0;
foreach ($carrito_datos as $carrito_dato) {
    $subtotal = $carrito_dato['cantidad'] * $carrito_dato['precio_venta'];
    $precio_total += $subtotal;
}
echo number_format($precio_total, 2);
?>