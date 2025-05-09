<?php
include ('../../config.php');

// 1. Inicia la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Verifica autenticación
if (!isset($_SESSION['id_usuario'])) {
    echo "No autenticado";
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_venta = $_GET['id_venta'] ?? null;
$nro_venta = $_GET['nro_venta'] ?? null;

if ($id_venta && $nro_venta) {
    // 3. Verifica que la venta pertenece al usuario autenticado
    $verifica = $pdo->prepare("SELECT id_venta FROM tb_ventas WHERE id_venta = :id_venta AND id_usuario = :id_usuario");
    $verifica->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
    $verifica->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $verifica->execute();

    if ($verifica->rowCount() === 1) {
        $pdo->beginTransaction();
        $sentencia = $pdo->prepare("DELETE FROM tb_ventas WHERE id_venta=:id_venta AND id_usuario=:id_usuario");
        $sentencia->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
        $sentencia->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

        if ($sentencia->execute()) {
            $sentencia2 = $pdo->prepare("DELETE FROM tb_carrito WHERE nro_venta=:nro_venta");
            $sentencia2->bindParam(':nro_venta', $nro_venta, PDO::PARAM_INT);
            $sentencia2->execute();
            $pdo->commit();
            ?>
            <script>
                location.href = "<?php echo $URL;?>/ventas";
            </script>
            <?php
        } else {
            $pdo->rollBack();
            echo "Error al intentar borrar la venta";
        }
    } else {
        echo "No autorizado para borrar esta venta";
        exit();
    }
} else {
    echo "Error: datos incompletos";
}
?>