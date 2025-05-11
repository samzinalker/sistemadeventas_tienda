<?php
// 1. Asegurar que la sesión está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Verificar que el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ' . $URL . '/login.php');
    exit();
}

// Obtener el ID del usuario actual
$id_usuario = $_SESSION['id_usuario'];

// Consulta para mostrar SOLO las compras del usuario actual
$sql_compras = "SELECT co.*, 
                pro.codigo as codigo, 
                pro.nombre as nombre_producto, 
                pro.descripcion as descripcion, 
                pro.stock as stock, 
                pro.stock_minimo as stock_minimo, 
                pro.stock_maximo as stock_maximo, 
                pro.precio_compra as precio_compra_producto,
                pro.precio_venta as precio_venta_producto, 
                pro.fecha_ingreso as fecha_ingreso, 
                pro.imagen as imagen,
                cat.nombre_categoria as nombre_categoria, 
                us.nombres as nombre_usuarios_producto,
                prov.nombre_proveedor as nombre_proveedor, 
                prov.celular as celular_proveedor, 
                prov.telefono as telefono_proveedor,
                prov.empresa as empresa, 
                prov.email as email_proveedor, 
                prov.direccion as direccion_proveedor, 
                us.nombres as nombres_usuario 
                FROM tb_compras as co 
                INNER JOIN tb_almacen as pro ON co.id_producto = pro.id_producto 
                INNER JOIN tb_categorias as cat ON cat.id_categoria = pro.id_categoria
                INNER JOIN tb_usuarios as us ON co.id_usuario = us.id_usuario 
                INNER JOIN tb_proveedores as prov ON co.id_proveedor = prov.id_proveedor
                WHERE co.id_usuario = :id_usuario
                ORDER BY co.id_compra DESC";

$query_compras = $pdo->prepare($sql_compras);
$query_compras->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$query_compras->execute();
$compras_datos = $query_compras->fetchAll(PDO::FETCH_ASSOC);