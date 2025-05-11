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
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit();
}

// Obtener el ID del usuario actual
$id_usuario = $_SESSION['id_usuario'];

// Validación de parámetros
$id_compra = filter_var($_GET['id_compra'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
$id_producto = filter_var($_GET['id_producto'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
$nro_compra = filter_var($_GET['nro_compra'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
$fecha_compra = filter_var($_GET['fecha_compra'] ?? '', FILTER_SANITIZE_STRING);
$id_proveedor = filter_var($_GET['id_proveedor'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
$comprobante = filter_var($_GET['comprobante'] ?? '', FILTER_SANITIZE_STRING);
$precio_compra = filter_var($_GET['precio_compra'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$cantidad_compra = filter_var($_GET['cantidad_compra'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
$stock_total = filter_var($_GET['stock_total'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

// Validar que todos los campos necesarios tengan valores válidos
if (!$id_compra || !$id_producto || !$nro_compra || !$fecha_compra || !$id_proveedor || 
    !$comprobante || !$precio_compra || $cantidad_compra <= 0 || $stock_total < 0) {
    $_SESSION['mensaje'] = "Todos los campos son obligatorios y deben tener valores válidos";
    $_SESSION['icono'] = "error";
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos o inválidos']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Verificar si la compra pertenece al usuario actual
    $verificar = $pdo->prepare("SELECT id_usuario FROM tb_compras WHERE id_compra = :id_compra");
    $verificar->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
    $verificar->execute();
    $compra = $verificar->fetch(PDO::FETCH_ASSOC);

    if (!$compra || $compra['id_usuario'] != $id_usuario) {
        throw new Exception("No tienes permiso para modificar esta compra");
    }

    // Actualizar compra
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

    $sentencia->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $sentencia->bindParam(':nro_compra', $nro_compra, PDO::PARAM_INT);
    $sentencia->bindParam(':fecha_compra', $fecha_compra, PDO::PARAM_STR);
    $sentencia->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_INT);
    $sentencia->bindParam(':comprobante', $comprobante, PDO::PARAM_STR);
    $sentencia->bindParam(':precio_compra', $precio_compra);
    $sentencia->bindParam(':cantidad', $cantidad_compra, PDO::PARAM_INT);
    $sentencia->bindParam(':fyh_actualizacion', $fechaHora, PDO::PARAM_STR);
    $sentencia->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
    $sentencia->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

    if(!$sentencia->execute()) {
        throw new Exception("Error al actualizar la compra");
    }

    // Actualizar stock (garantizar que nunca sea negativo)
    if ($stock_total < 0) $stock_total = 0;
    
    $sentencia = $pdo->prepare("UPDATE tb_almacen 
                              SET stock = :stock, 
                                  fyh_actualizacion = NOW() 
                              WHERE id_producto = :id_producto
                              AND id_usuario = :id_usuario");
    $sentencia->bindParam(':stock', $stock_total, PDO::PARAM_INT);
    $sentencia->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $sentencia->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    
    if (!$sentencia->execute()) {
        throw new Exception("Error al actualizar el stock del producto");
    }

    $pdo->commit();

    $_SESSION['mensaje'] = "Compra actualizada correctamente";
    $_SESSION['icono'] = "success";
    ?>
    <script>
        location.href = "<?php echo $URL;?>/compras";
    </script>
    <?php
} catch (Exception $e) {
    $pdo->rollBack();
    
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    ?>
    <script>
        location.href = "<?php echo $URL;?>/compras";
    </script>
    <?php
}