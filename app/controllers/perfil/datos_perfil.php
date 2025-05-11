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

// Consultar los datos del usuario
$sql = "SELECT us.*, rol.rol 
        FROM tb_usuarios as us 
        INNER JOIN tb_roles as rol ON us.id_rol = rol.id_rol 
        WHERE us.id_usuario = :id_usuario";
$query = $pdo->prepare($sql);
$query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$query->execute();

// Verificar que se obtuvieron los datos
if ($query->rowCount() > 0) {
    $usuario = $query->fetch(PDO::FETCH_ASSOC);
    
    // Asignar variables para usar en la vista
    $nombres = $usuario['nombres'];
    $email = $usuario['email'];
    $rol = $usuario['rol'];
    $imagen_perfil = isset($usuario['imagen_perfil']) ? $usuario['imagen_perfil'] : 'user_default.png';
    $fyh_creacion = $usuario['fyh_creacion'];
    $fyh_actualizacion = $usuario['fyh_actualizacion'];
} else {
    // Si no se encuentra el usuario, redirigir al login
    $_SESSION['mensaje'] = "Error al obtener los datos del usuario";
    $_SESSION['icono'] = "error";
    
    ?>
    <script>
        location.href = "<?php echo $URL;?>/login";
    </script>
    <?php
    exit();
}
?>