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

// Obtener el ID del usuario actual
$id_usuario = $_SESSION['id_usuario'];

$id_producto = $_GET['id_producto'];
$nro_compra = $_GET['nro_compra'];
$fecha_compra = $_GET['fecha_compra'];
$id_proveedor = $_GET['id_proveedor'];
$comprobante = $_GET['comprobante'];
$precio_compra = $_GET['precio_compra'];
$cantidad_compra = $_GET['cantidad_compra'];
$stock_total = $_GET['stock_total'];

$pdo->beginTransaction();

$sentencia = $pdo->prepare("INSERT INTO tb_compras
       (id_producto, nro_compra, fecha_compra, id_proveedor, comprobante, id_usuario, precio_compra, cantidad, fyh_creacion) 
VALUES (:id_producto, :nro_compra, :fecha_compra, :id_proveedor, :comprobante, :id_usuario, :precio_compra, :cantidad, :fyh_creacion)");

$sentencia->bindParam('id_producto', $id_producto);
$sentencia->bindParam('nro_compra', $nro_compra);
$sentencia->bindParam('fecha_compra', $fecha_compra);
$sentencia->bindParam('id_proveedor', $id_proveedor);
$sentencia->bindParam('comprobante', $comprobante);
$sentencia->bindParam('id_usuario', $id_usuario); // Usar el ID del usuario actual
$sentencia->bindParam('precio_compra', $precio_compra);
$sentencia->bindParam('cantidad', $cantidad_compra);
$sentencia->bindParam('fyh_creacion', $fechaHora);

if($sentencia->execute()){
    // Actualiza el stock desde la compra
    $sentencia = $pdo->prepare("UPDATE tb_almacen SET stock = :stock WHERE id_producto = :id_producto");
    $sentencia->bindParam('stock', $stock_total);
    $sentencia->bindParam('id_producto', $id_producto);
    $sentencia->execute();

    $pdo->commit();

    $_SESSION['mensaje'] = "Se registró la compra correctamente";
    $_SESSION['icono'] = "success";
    ?>
    <script>
        location.href = "<?php echo $URL;?>/compras";
    </script>
    <?php
} else {
    $pdo->rollBack();
    
    $_SESSION['mensaje'] = "Error al registrar la compra en la base de datos";
    $_SESSION['icono'] = "error";
    ?>
    <script>
        location.href = "<?php echo $URL;?>/compras/create.php";
    </script>
    <?php
}