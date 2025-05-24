<?php
// 1. Asegura que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Verifica que el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /sistemadeventas/login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// 3. Consulta SOLO los productos del usuario autenticado
$sql_productos = "SELECT a.*, cat.nombre_categoria as categoria, u.email as email
                  FROM tb_almacen as a
                  INNER JOIN tb_categorias as cat ON a.id_categoria = cat.id_categoria
                  INNER JOIN tb_usuarios as u ON u.id_usuario = a.id_usuario
                  WHERE a.id_usuario = :id_usuario";
$query_productos = $pdo->prepare($sql_productos);
$query_productos->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$query_productos->execute();
$productos_datos = $query_productos->fetchAll(PDO::FETCH_ASSOC);