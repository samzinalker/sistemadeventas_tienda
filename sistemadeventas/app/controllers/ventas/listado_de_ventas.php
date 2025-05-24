<?php
// 1. Asegura que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Verifica que el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    // Redirecciona al login o lanza un error seguro
    header('Location: /sistemadeventas/login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// 3. Consulta SOLO las ventas del usuario autenticado
$sql_ventas = "SELECT ve.*, cli.nombre_cliente as nombre_cliente
               FROM tb_ventas as ve 
               INNER JOIN tb_clientes as cli ON cli.id_cliente = ve.id_cliente
               WHERE ve.id_usuario = :id_usuario";
$query_ventas = $pdo->prepare($sql_ventas);
$query_ventas->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$query_ventas->execute();
$ventas_datos = $query_ventas->fetchAll(PDO::FETCH_ASSOC);