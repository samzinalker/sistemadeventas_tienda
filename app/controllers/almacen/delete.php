<?php
include ('../../config.php');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['id_usuario'])) {
    echo "No autenticado";
    exit();
}
$id_usuario = $_SESSION['id_usuario'];
$id_producto = $_POST['id_producto'] ?? null;

if ($id_producto) {
    // Verifica que el producto pertenece al usuario autenticado
    $sql_check = "SELECT id_producto FROM tb_almacen WHERE id_producto = :id_producto AND id_usuario = :id_usuario";
    $query_check = $pdo->prepare($sql_check);
    $query_check->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $query_check->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $query_check->execute();

    if ($query_check->rowCount() === 1) {
        $sql = "DELETE FROM tb_almacen WHERE id_producto = :id_producto AND id_usuario = :id_usuario";
        $query = $pdo->prepare($sql);
        $query->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        if ($query->execute()) {
            header('Location: ../../../almacen/index.php?mensaje=Producto eliminado correctamente');
            exit();
        } else {
            echo '<div class="alert alert-danger">Error al eliminar el producto.</div>';
        }
    } else {
        echo '<div class="alert alert-danger">No tienes permiso para borrar este producto.</div>';
    }
} else {
    echo '<div class="alert alert-danger">ID de producto no proporcionado.</div>';
}
?>