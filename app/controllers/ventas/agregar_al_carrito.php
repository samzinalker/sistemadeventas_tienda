<?php
include('../../config.php');
if (session_status() == PHP_SESSION_NONE) session_start();

$id_usuario_actual = $_SESSION['id_usuario'];
$id_producto = $_POST['id_producto'];
$cantidad = $_POST['cantidad'];

// OPCIONAL: Validar existencia, stock, etc.

$sql = "INSERT INTO tb_carrito (id_usuario, id_producto, cantidad, nro_venta) 
        VALUES (:id_usuario, :id_producto, :cantidad, 0)";
$query = $pdo->prepare($sql);
$query->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT);
$query->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
$query->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
$query->execute();

echo "OK";