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
$cantidad_compra = $_GET['cantidad_compra'];
$stock_actual = $_GET['stock_actual'];

// Verificar si la compra pertenece al usuario actual
$verificar = $pdo->prepare("SELECT id_usuario FROM tb_compras WHERE id_compra = :id_compra");
$verificar->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
$verificar->execute();
$compra = $verificar->fetch(PDO::FETCH_ASSOC);

if (!$compra || $compra['id_usuario'] != $id_usuario) {
    $_SESSION['mensaje'] = "No tienes permiso para eliminar esta compra";
    $_SESSION['icono'] = "error";
    echo "<script>location.href = '$URL/compras';</script>";
    exit();
}

$pdo->beginTransaction();

$sentencia = $pdo->prepare("DELETE FROM tb_compras WHERE id_compra = :id_compra AND id_usuario = :id_usuario");
$sentencia->bindParam('id_compra', $id_compra);
$sentencia->bindParam('id_usuario', $id_usuario);

if($sentencia->execute()){
    // Actualiza el stock desde la compra
    $stock = $stock_actual - $cantidad_compra;
    $sentencia = $pdo->prepare("UPDATE tb_almacen SET stock = :stock WHERE id_producto = :id_producto");
    $sentencia->bindParam('stock', $stock);
    $sentencia->bindParam('id_producto', $id_producto);
    $sentencia->execute();

    $pdo->commit();

    $_SESSION['mensaje'] = "Se eliminó la compra correctamente";
    $_SESSION['icono'] = "success";
    ?>
    <script>
        location.href = "<?php echo $URL;?>/compras";
    </script>
    <?php
} else {
    $pdo->rollBack();
    
    $_SESSION['mensaje'] = "Error al eliminar la compra de la base de datos";
    $_SESSION['icono'] = "error";
    ?>
    <script>
        location.href = "<?php echo $URL;?>/compras";
    </script>
    <?php
}