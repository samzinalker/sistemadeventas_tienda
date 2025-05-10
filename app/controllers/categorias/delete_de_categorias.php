<?php
include ('../../config.php');

// Verificar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si está logueado
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['mensaje'] = "Debe iniciar sesión para realizar esta acción";
    $_SESSION['icono'] = "error";
    echo "<script>location.href = '$URL/login';</script>";
    exit();
}

$id_categoria = $_GET['id_categoria'];
$id_usuario_actual = $_SESSION['id_usuario'];

// Verificar si el usuario es propietario de la categoría
$consulta = $pdo->prepare("SELECT id_usuario FROM tb_categorias WHERE id_categoria = :id_categoria");
$consulta->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
$consulta->execute();
$categoria = $consulta->fetch(PDO::FETCH_ASSOC);

// Si no es su categoría, denegar acceso
if ($categoria['id_usuario'] != $id_usuario_actual) {
    $_SESSION['mensaje'] = "No tiene permiso para eliminar esta categoría";
    $_SESSION['icono'] = "error";
    echo "<script>location.href = '$URL/categorias';</script>";
    exit();
}

// Verificar si la categoría está siendo utilizada en el almacén
$consulta_uso = $pdo->prepare("SELECT COUNT(*) as total FROM tb_almacen WHERE id_categoria = :id_categoria");
$consulta_uso->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
$consulta_uso->execute();
$resultado_uso = $consulta_uso->fetch(PDO::FETCH_ASSOC);

if ($resultado_uso['total'] > 0) {
    $_SESSION['mensaje'] = "No se puede eliminar la categoría porque está siendo utilizada en productos";
    $_SESSION['icono'] = "warning";
    echo "<script>location.href = '$URL/categorias';</script>";
    exit();
}

// Si la categoría no está siendo utilizada, proceder con la eliminación
$sentencia = $pdo->prepare("DELETE FROM tb_categorias WHERE id_categoria = :id_categoria");
$sentencia->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);

if($sentencia->execute()){
    $_SESSION['mensaje'] = "Categoría eliminada correctamente";
    $_SESSION['icono'] = "success";
    echo "<script>location.href = '$URL/categorias';</script>";
} else {
    $_SESSION['mensaje'] = "Error al eliminar la categoría";
    $_SESSION['icono'] = "error";
    echo "<script>location.href = '$URL/categorias';</script>";
}