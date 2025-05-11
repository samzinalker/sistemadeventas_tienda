<?php

include ('../../config.php');

// Iniciamos sesión para acceder a los datos del usuario
session_start();

// Verificamos que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['mensaje'] = "Debes iniciar sesión para realizar esta acción";
    $_SESSION['icono'] = "error";
    ?>
    <script>
        location.href = "<?php echo $URL;?>/login";
    </script>
    <?php
    exit();
}

$nombre_proveedor = $_GET['nombre_proveedor'];
$celular = $_GET['celular'];
$telefono = $_GET['telefono'];
$empresa = $_GET['empresa'];
$email = $_GET['email'];
$direccion = $_GET['direccion'];
$id_usuario = $_SESSION['id_usuario']; // Obtenemos el ID del usuario de la sesión

$sentencia = $pdo->prepare("INSERT INTO tb_proveedores
       (nombre_proveedor, celular, telefono, empresa, email, direccion, id_usuario, fyh_creacion) 
VALUES (:nombre_proveedor, :celular, :telefono, :empresa, :email, :direccion, :id_usuario, :fyh_creacion)");

$sentencia->bindParam('nombre_proveedor', $nombre_proveedor);
$sentencia->bindParam('celular', $celular);
$sentencia->bindParam('telefono', $telefono);
$sentencia->bindParam('empresa', $empresa);
$sentencia->bindParam('email', $email);
$sentencia->bindParam('direccion', $direccion);
$sentencia->bindParam('id_usuario', $id_usuario); // Vinculamos el parámetro id_usuario
$sentencia->bindParam('fyh_creacion', $fechaHora);

if($sentencia->execute()){
    // echo "se registro correctamente";
    $_SESSION['mensaje'] = "Se registró al proveedor de la manera correcta";
    $_SESSION['icono'] = "success";
    // header('Location: '.$URL.'/categorias/');
    ?>
    <script>
        location.href = "<?php echo $URL;?>/proveedores";
    </script>
    <?php
}else{
    $_SESSION['mensaje'] = "Error no se pudo registrar en la base de datos";
    $_SESSION['icono'] = "error";
    //  header('Location: '.$URL.'/categorias');
    ?>
    <script>
        location.href = "<?php echo $URL;?>/proveedores";
    </script>
    <?php
}