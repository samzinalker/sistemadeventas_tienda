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

$nombre_categoria = $_GET['nombre_categoria'];
$id_categoria = $_GET['id_categoria'];
$id_usuario_actual = $_SESSION['id_usuario'];

// Verificar si el usuario es propietario de la categoría
$consulta = $pdo->prepare("SELECT id_usuario FROM tb_categorias WHERE id_categoria = :id_categoria");
$consulta->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
$consulta->execute();
$categoria = $consulta->fetch(PDO::FETCH_ASSOC);

// Si no es su categoría, denegar acceso
if ($categoria['id_usuario'] != $id_usuario_actual) {
    $_SESSION['mensaje'] = "No tiene permiso para modificar esta categoría";
    $_SESSION['icono'] = "error";
    echo "<script>location.href = '$URL/categorias';</script>";
    exit();
}

// Verificar si otra categoría del mismo usuario ya tiene ese nombre
$consulta_duplicado = $pdo->prepare("SELECT COUNT(*) AS total FROM tb_categorias 
                                   WHERE nombre_categoria = :nombre_categoria 
                                   AND id_usuario = :id_usuario 
                                   AND id_categoria != :id_categoria");
$consulta_duplicado->bindParam(':nombre_categoria', $nombre_categoria);
$consulta_duplicado->bindParam(':id_usuario', $id_usuario_actual);
$consulta_duplicado->bindParam(':id_categoria', $id_categoria);
$consulta_duplicado->execute();
$resultado = $consulta_duplicado->fetch(PDO::FETCH_ASSOC);

if ($resultado['total'] > 0) {
    $_SESSION['mensaje'] = "Ya existe otra categoría con este nombre";
    $_SESSION['icono'] = "warning";
    echo "<script>location.href = '$URL/categorias';</script>";
    exit();
}

// Si pasa todas las validaciones, proceder con la actualización
$sentencia = $pdo->prepare("UPDATE tb_categorias
    SET nombre_categoria = :nombre_categoria,
        fyh_actualizacion = :fyh_actualizacion 
    WHERE id_categoria = :id_categoria");

$sentencia->bindParam(':nombre_categoria', $nombre_categoria);
$sentencia->bindParam(':fyh_actualizacion', $fechaHora);
$sentencia->bindParam(':id_categoria', $id_categoria);

if($sentencia->execute()){
    $_SESSION['mensaje'] = "Se actualizó la categoría correctamente";
    $_SESSION['icono'] = "success";
    echo "<script>location.href = '$URL/categorias';</script>";
} else {
    $_SESSION['mensaje'] = "Error al actualizar en la base de datos";
    $_SESSION['icono'] = "error";
    echo "<script>location.href = '$URL/categorias';</script>";
}