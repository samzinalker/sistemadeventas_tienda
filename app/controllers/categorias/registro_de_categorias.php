<?php
include ('../../config.php');

// Verificar sesión y permisos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si es administrador
if ($_SESSION['rol'] !== 'administrador') {
    $_SESSION['mensaje'] = "Solo los administradores pueden modificar categorías";
    $_SESSION['icono'] = "error";
    echo "<script>location.href = '$URL/categorias';</script>";
    exit();
}

$nombre_categoria = $_GET['nombre_categoria'];

$sentencia = $pdo->prepare("INSERT INTO tb_categorias
       ( nombre_categoria, fyh_creacion) 
VALUES (:nombre_categoria,:fyh_creacion)");

$sentencia->bindParam('nombre_categoria',$nombre_categoria);
$sentencia->bindParam('fyh_creacion',$fechaHora);
if($sentencia->execute()){
    // No iniciar sesión aquí, ya debería estar iniciada
    $_SESSION['mensaje'] = "Se registro la categoría de la manera correcta";
    $_SESSION['icono'] = "success";
    echo "<script>location.href = '$URL/categorias';</script>";
} else {
    $_SESSION['mensaje'] = "Error no se pudo registrar en la base de datos";
    $_SESSION['icono'] = "error";
    echo "<script>location.href = '$URL/categorias';</script>";
}