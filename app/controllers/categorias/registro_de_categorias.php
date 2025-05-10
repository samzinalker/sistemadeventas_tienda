<?php
include ('../../config.php');

// Verificar sesión y permisos
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
$id_usuario = $_SESSION['id_usuario']; // Guardar id del usuario actual

$sentencia = $pdo->prepare("INSERT INTO tb_categorias
       (nombre_categoria, id_usuario, fyh_creacion) 
VALUES (:nombre_categoria, :id_usuario, :fyh_creacion)");

$sentencia->bindParam('nombre_categoria', $nombre_categoria);
$sentencia->bindParam('id_usuario', $id_usuario, PDO::PARAM_INT);
$sentencia->bindParam('fyh_creacion', $fechaHora);

if($sentencia->execute()){
    $_SESSION['mensaje'] = "Se registró la categoría correctamente";
    $_SESSION['icono'] = "success";
    echo "<script>location.href = '$URL/categorias';</script>";
} else {
    $_SESSION['mensaje'] = "Error al registrar en la base de datos";
    $_SESSION['icono'] = "error";
    echo "<script>location.href = '$URL/categorias';</script>";
}