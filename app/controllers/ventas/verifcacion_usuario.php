<?php
/**
 * Sistema de verificación centralizado para el módulo de ventas
 * Verifica que el usuario actual tenga acceso a la venta solicitada
 */

// Incluir el archivo de configuración si aún no está incluido
if (!defined('SERVIDOR')) {
    include_once(__DIR__ . '/../../config.php');
}

/**
 * Verifica si el usuario es propietario de una venta específica
 * 
 * @param PDO $pdo Conexión a la base de datos
 * @param int $id_venta ID de la venta a verificar
 * @param int $id_usuario ID del usuario actual
 * @return bool TRUE si el usuario es propietario, FALSE en caso contrario
 */
function verificar_propiedad_venta($pdo, $id_venta, $id_usuario) {
    $sql = "SELECT COUNT(*) FROM tb_ventas WHERE id_venta = :id_venta AND id_usuario = :id_usuario";
    $query = $pdo->prepare($sql);
    $query->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
    $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $query->execute();
    
    return ($query->fetchColumn() > 0);
}

/**
 * Verifica si el usuario es propietario de un número de venta específico
 * 
 * @param PDO $pdo Conexión a la base de datos
 * @param int $nro_venta Número de venta a verificar
 * @param int $id_usuario ID del usuario actual
 * @return bool TRUE si el usuario es propietario, FALSE en caso contrario
 */
function verificar_propiedad_nro_venta($pdo, $nro_venta, $id_usuario) {
    $sql = "SELECT COUNT(*) FROM tb_ventas WHERE nro_venta = :nro_venta AND id_usuario = :id_usuario";
    $query = $pdo->prepare($sql);
    $query->bindParam(':nro_venta', $nro_venta, PDO::PARAM_INT);
    $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $query->execute();
    
    return ($query->fetchColumn() > 0);
}

/**
 * Redirecciona al usuario si no es propietario de la venta
 * 
 * @param PDO $pdo Conexión a la base de datos
 * @param int $id_venta ID de la venta a verificar
 * @param int $id_usuario ID del usuario actual
 * @param string $URL URL base del sistema
 */
function redireccionar_si_no_es_propietario_venta($pdo, $id_venta, $id_usuario) {
    global $URL;
    
    if (!verificar_propiedad_venta($pdo, $id_venta, $id_usuario)) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['mensaje'] = "No tienes permisos para acceder a esta venta";
        $_SESSION['icono'] = "error";
        header('Location: ' . $URL . '/ventas/');
        exit();
    }
}

/**
 * Verifica si un producto en carrito pertenece al usuario actual
 * 
 * @param PDO $pdo Conexión a la base de datos
 * @param int $id_carrito ID del item en carrito
 * @param int $id_usuario ID del usuario actual
 * @return bool TRUE si el usuario es propietario, FALSE en caso contrario
 */
function verificar_propiedad_carrito($pdo, $id_carrito, $id_usuario) {
    $sql = "SELECT COUNT(*) FROM tb_carrito WHERE id_carrito = :id_carrito AND id_usuario = :id_usuario";
    $query = $pdo->prepare($sql);
    $query->bindParam(':id_carrito', $id_carrito, PDO::PARAM_INT);
    $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $query->execute();
    
    return ($query->fetchColumn() > 0);
}