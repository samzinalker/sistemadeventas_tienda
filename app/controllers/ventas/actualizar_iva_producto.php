<?php
include('../../config.php');
if (session_status() == PHP_SESSION_NONE) session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener datos del POST
$id_carrito = isset($_POST['id_carrito']) ? intval($_POST['id_carrito']) : 0;
$id_producto = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
$porcentaje_iva = isset($_POST['porcentaje_iva']) ? floatval($_POST['porcentaje_iva']) : 0;

// Validar que no sea negativo
if ($porcentaje_iva < 0) {
    $porcentaje_iva = 0;
}

// Validar que el item del carrito pertenezca al usuario
try {
    // Verificar que el ID del carrito sea válido
    if ($id_carrito <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID de carrito no válido']);
        exit();
    }
    
    $sql_check = "SELECT COUNT(*) FROM tb_carrito 
                 WHERE id_carrito = :id_carrito AND id_usuario = :id_usuario AND nro_venta = 0";
    $query_check = $pdo->prepare($sql_check);
    $query_check->bindParam(':id_carrito', $id_carrito, PDO::PARAM_INT);
    $query_check->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $query_check->execute();
    
    if ($query_check->fetchColumn() === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado en el carrito']);
        exit();
    }

    // Inicializar el array de IVA si no existe
    if (!isset($_SESSION['iva_productos'])) {
        $_SESSION['iva_productos'] = [];
    }

    // Guardar el porcentaje de IVA para este producto en la sesión
    $_SESSION['iva_productos'][$id_carrito] = $porcentaje_iva;
    
    // Si el ID del producto es válido, guardar también por producto
    if ($id_producto > 0) {
        if (!isset($_SESSION['ultimo_iva_por_producto'])) {
            $_SESSION['ultimo_iva_por_producto'] = [];
        }
        $_SESSION['ultimo_iva_por_producto'][$id_producto] = $porcentaje_iva;
    }

    echo json_encode([
        'status' => 'success',
        'id_carrito' => $id_carrito,
        'id_producto' => $id_producto,
        'porcentaje_iva' => $porcentaje_iva,
        'message' => 'IVA actualizado correctamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al actualizar el IVA: ' . $e->getMessage()
    ]);
}