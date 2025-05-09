<?php
include('../../config.php');
if (session_status() == PHP_SESSION_NONE) session_start();

$id_carrito = $_POST['id_carrito'];
$sql = "DELETE FROM tb_carrito WHERE id_carrito = :id_carrito";
$query = $pdo->prepare($sql);
$query->bindParam(':id_carrito', $id_carrito, PDO::PARAM_INT);
$query->execute();

header('Location: ../../ventas/create.php');
exit;