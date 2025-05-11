<?php
// Verificar si hay una sesión activa
if (!isset($_SESSION['id_usuario'])) {
    session_start();
    $_SESSION['mensaje'] = "Debes iniciar sesión para acceder a esta página";
    $_SESSION['icono'] = "error";
    
    ?>
    <script>
        location.href = "<?php echo $URL;?>/login";
    </script>
    <?php
    exit();
}

// Obtener el ID de usuario de la sesión
$id_usuario = $_SESSION['id_usuario'];

// Para páginas de detalle, verificar propiedad del proveedor
if (isset($_GET['id_proveedor'])) {
    $id_proveedor = $_GET['id_proveedor'];
    
    $sql_verificacion = "SELECT COUNT(*) as existe FROM tb_proveedores 
                         WHERE id_proveedor = :id_proveedor AND id_usuario = :id_usuario";
    $query_verificacion = $pdo->prepare($sql_verificacion);
    $query_verificacion->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_INT);
    $query_verificacion->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $query_verificacion->execute();
    $resultado = $query_verificacion->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado['existe'] == 0) {
        $_SESSION['mensaje'] = "No tienes permiso para ver este proveedor";
        $_SESSION['icono'] = "error";
        
        ?>
        <script>
            location.href = "<?php echo $URL;?>/proveedores";
        </script>
        <?php
        exit();
    }
}
?>