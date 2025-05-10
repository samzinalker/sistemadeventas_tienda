<?php
// Verificar sesión y permisos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ' . $URL . '/login.php');
    exit();
}

// Obtener el ID del usuario actual
$id_usuario = $_SESSION['id_usuario'];

// Consulta SQL para mostrar solo las categorías del usuario actual
$sql_categorias = "SELECT * FROM tb_categorias 
                  WHERE id_usuario = :id_usuario
                  ORDER BY id_categoria DESC";
$query_categorias = $pdo->prepare($sql_categorias);
$query_categorias->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$query_categorias->execute();
$categorias_datos = $query_categorias->fetchAll(PDO::FETCH_ASSOC);