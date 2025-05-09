<?php
include('../../config.php');
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_cliente = intval($_POST['id_cliente']);
$fyh = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    $sql_check = "SELECT COUNT(*) FROM tb_carrito WHERE id_usuario = :id_usuario AND nro_venta = 0";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':id_usuario' => $id_usuario]);
    $carrito_count = $stmt_check->fetchColumn();

    if ($carrito_count == 0) {
        throw new Exception("El carrito está vacío. No se puede finalizar la venta.");
    }

    $nro_venta = time();

    $sql_update = "UPDATE tb_carrito SET nro_venta = :nro_venta, fyh_actualizacion = :fyh WHERE id_usuario = :id_usuario AND nro_venta = 0";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([
        ':nro_venta' => $nro_venta,
        ':fyh' => $fyh,
        ':id_usuario' => $id_usuario
    ]);

    $sql_total = "SELECT SUM(c.cantidad * p.precio_venta) as total
                  FROM tb_carrito c
                  INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
                  WHERE c.id_usuario = :id_usuario AND c.nro_venta = :nro_venta";
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute([
        ':id_usuario' => $id_usuario,
        ':nro_venta' => $nro_venta
    ]);
    $total = $stmt_total->fetchColumn();
    if (!$total) $total = 0;

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

    $sql_items = "SELECT id_producto, cantidad FROM tb_carrito WHERE id_usuario = :id_usuario AND nro_venta = :nro_venta";
    $stmt_items = $pdo->prepare($sql_items);
    $stmt_items->execute([
        ':id_usuario' => $id_usuario,
        ':nro_venta' => $nro_venta
    ]);
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $item) {
        $sql_stock = "UPDATE tb_almacen SET stock = stock - :cantidad WHERE id_producto = :id_producto";
        $stmt_stock = $pdo->prepare($sql_stock);
        $stmt_stock->execute([
            ':cantidad' => $item['cantidad'],
            ':id_producto' => $item['id_producto']
        ]);
    }

    $pdo->commit();
    header('Location: ../../ventas/create.php?success=1');
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: ../../ventas/create.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>