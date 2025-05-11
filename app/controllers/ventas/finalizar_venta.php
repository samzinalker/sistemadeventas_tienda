<?php
include('../../config.php');
if (session_status() == PHP_SESSION_NONE) session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_cliente = intval($_POST['id_cliente'] ?? 0);
$fyh = date('Y-m-d H:i:s');

// Validar cliente
if ($id_cliente <= 0) {
    $_SESSION['mensaje'] = "Debe seleccionar un cliente para la venta";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/ventas/create.php');
    exit();
}

try {
    $pdo->beginTransaction();

    // Verificar que hay productos en el carrito
    $sql_check = "SELECT COUNT(*) FROM tb_carrito WHERE id_usuario = :id_usuario AND nro_venta = 0";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':id_usuario' => $id_usuario]);
    $carrito_count = $stmt_check->fetchColumn();

    if ($carrito_count == 0) {
        throw new Exception("El carrito está vacío. No se puede finalizar la venta.");
    }

    // Verificar que todos los productos en el carrito pertenecen al usuario
    $sql_productos_carrito = "SELECT c.id_producto, c.cantidad, p.stock, p.nombre, p.id_usuario 
                             FROM tb_carrito c
                             JOIN tb_almacen p ON c.id_producto = p.id_producto
                             WHERE c.id_usuario = :id_usuario AND c.nro_venta = 0";
    $stmt_productos = $pdo->prepare($sql_productos_carrito);
    $stmt_productos->execute([':id_usuario' => $id_usuario]);
    $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

    // Verificar propiedad y stock de cada producto
    foreach ($productos as $producto) {
        // Verificar propiedad del producto
        if ($producto['id_usuario'] != $id_usuario) {
            throw new Exception("El producto '{$producto['nombre']}' no te pertenece y no puede ser vendido.");
        }
        
        // Verificar stock suficiente
        if ($producto['cantidad'] > $producto['stock']) {
            throw new Exception("Stock insuficiente para '{$producto['nombre']}'. Disponible: {$producto['stock']}, Solicitado: {$producto['cantidad']}");
        }
    }

    // Generar número de venta único
    $nro_venta = time();

    // Actualizar productos en carrito con número de venta
    $sql_update = "UPDATE tb_carrito 
                   SET nro_venta = :nro_venta, fyh_actualizacion = :fyh 
                   WHERE id_usuario = :id_usuario AND nro_venta = 0";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([
        ':nro_venta' => $nro_venta,
        ':fyh' => $fyh,
        ':id_usuario' => $id_usuario
    ]);

    // Calcular total de la venta
    $sql_total = "SELECT SUM(c.cantidad * p.precio_venta) as total
                  FROM tb_carrito c
                  INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
                  WHERE c.id_usuario = :id_usuario AND c.nro_venta = :nro_venta";
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute([
        ':id_usuario' => $id_usuario,
        ':nro_venta' => $nro_venta
    ]);
    $total = $stmt_total->fetchColumn() ?: 0;

    // Registrar la venta principal
    $sql_venta = "INSERT INTO tb_ventas (nro_venta, id_cliente, id_usuario, total_pagado, fyh_creacion, fyh_actualizacion)
                  VALUES (:nro_venta, :id_cliente, :id_usuario, :total_pagado, :fyh, :fyh)";
    $stmt_venta = $pdo->prepare($sql_venta);
    $stmt_venta->execute([
        ':nro_venta' => $nro_venta,
        ':id_cliente' => $id_cliente,
        ':id_usuario' => $id_usuario,
        ':total_pagado' => $total,
        ':fyh' => $fyh
    ]);

    // Actualizar el stock de productos
    foreach ($productos as $producto) {
        $nuevo_stock = $producto['stock'] - $producto['cantidad'];
        
        // Garantizar que el stock nunca sea negativo
        if ($nuevo_stock < 0) $nuevo_stock = 0;
        
        $sql_stock = "UPDATE tb_almacen 
                      SET stock = :nuevo_stock, 
                          fyh_actualizacion = :fyh
                      WHERE id_producto = :id_producto 
                      AND id_usuario = :id_usuario";
        $stmt_stock = $pdo->prepare($sql_stock);
        $stmt_stock->execute([
            ':nuevo_stock' => $nuevo_stock,
            ':fyh' => $fyh,
            ':id_producto' => $producto['id_producto'],
            ':id_usuario' => $id_usuario
        ]);
    }

    $pdo->commit();
    
    $_SESSION['mensaje'] = "Venta #" . $nro_venta . " realizada con éxito";
    $_SESSION['icono'] = "success";
    header("Location: " . $URL . "/ventas/create.php?success=1");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/ventas/create.php');
    exit();
}