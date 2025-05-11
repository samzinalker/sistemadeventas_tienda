<?php
/**
 * Sistema de verificación centralizado para el módulo de almacén
 * Verifica que el usuario actual tenga acceso al producto solicitado
 */

// Incluir el archivo de configuración si aún no está incluido
if (!defined('SERVIDOR')) {
    include_once(__DIR__ . '/../../config.php');
}

function verificar_propiedad_producto($pdo, $id_producto, $id_usuario) {
    // Verifica la propiedad del producto - SIEMPRE privado por usuario
    // Sin excepciones para administradores
    $sql = "SELECT COUNT(*) FROM tb_almacen WHERE id_producto = :id_producto AND id_usuario = :id_usuario";
    $query = $pdo->prepare($sql);
    $query->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $query->execute();
    
    return ($query->fetchColumn() > 0);
}

function redireccionar_si_no_es_propietario($pdo, $id_producto, $id_usuario) {
    // Hacemos global la variable URL para poder usarla dentro de la función
    global $URL;
    
    if (!verificar_propiedad_producto($pdo, $id_producto, $id_usuario)) {
        // Si no es el dueño, redirige con mensaje de error
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['mensaje'] = "No tienes permisos para acceder a este producto";
        $_SESSION['icono'] = "error";
        header('Location: ' . $URL . '/almacen/');
        exit();
    }
}