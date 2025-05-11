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

$id_compra = $_GET['id_compra'];
$id_producto = $_GET['id_producto'];
$nro_compra = $_GET['nro_compra'];
$fecha_compra = $_GET['fecha_compra'];
$id_proveedor = $_GET['id_proveedor'];
$comprobante = $_GET['comprobante'];
$precio_compra = $_GET['precio_compra'];
$cantidad_compra = $_GET['cantidad_compra'];
$stock_total = $_GET['stock_total'];

// Verificar si la compra pertenece al usuario actual
$verificar = $pdo->prepare("SELECT id_usuario FROM tb_compras WHERE id_compra = :id_compra");
$verificar->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
$verificar->execute();
$compra = $verificar->fetch(PDO::FETCH_ASSOC);

if (!$compra || $compra['id_usuario'] != $id_usuario) {
    $_SESSION['mensaje'] = "No tienes permiso para modificar esta compra";
    $_SESSION['icono'] = "error";
    echo "<script>location.href = '$URL/compras';</script>";
    exit();
}

$pdo->beginTransaction();

$sentencia = $pdo->prepare("UPDATE tb_compras 
SET id_producto = :id_producto,
    nro_compra = :nro_compra,
    fecha_compra = :fecha_compra,
    id_proveedor = :id_proveedor,
    comprobante = :comprobante,
    precio_compra = :precio_compra,
    cantidad = :cantidad,
    fyh_actualizacion = :fyh_actualizacion 
WHERE id_compra = :id_compra
AND id_usuario = :id_usuario");

$sentencia->bindParam('id_producto', $id_producto);
$sentencia->bindParam('nro_compra', $nro_compra);
$sentencia->bindParam('fecha_compra', $fecha_compra);
$sentencia->bindParam('id_proveedor', $id_proveedor);
$sentencia->bindParam('comprobante', $comprobante);
$sentencia->bindParam('precio_compra', $precio_compra);
$sentencia->bindParam('cantidad', $cantidad_compra);
$sentencia->bindParam('fyh_actualizacion', $fechaHora);
$sentencia->bindParam('id_compra', $id_compra);
$sentencia->bindParam('id_usuario', $id_usuario);

if($sentencia->execute()){
    // Actualiza el stock desde la compra
    $sentencia = $pdo->prepare("UPDATE tb_almacen SET stock = :stock WHERE id_producto = :id_producto");
    $sentencia->bindParam('stock', $stock_total);
    $sentencia->bindParam('id_producto', $id_producto);
    $sentencia->execute();

    $pdo->commit();

    $_SESSION['mensaje'] = "Se actualizó la compra correctamente";
    $_SESSION['icono'] = "success";
    ?>
    <script>
        location.href = "<?php echo $URL;?>/compras";
    </script>
    <?php
} else {
    $pdo->rollBack();
    
    $_SESSION['mensaje'] = "Error al actualizar la compra en la base de datos";
    $_SESSION['icono'] = "error";
    ?>
    <script>
        location.href = "<?php echo $URL;?>/compras";
    </script>
    <?php
}