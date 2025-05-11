<?php
include('../../config.php');
session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['mensaje'] = "Debes iniciar sesión para realizar esta acción";
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/login');
    exit();
}

// Obtener el ID de usuario de la sesión
$id_usuario = $_SESSION['id_usuario'];

// Verificar que se enviaron los datos del formulario
if (!isset($_POST['password_actual']) || !isset($_POST['password_nueva']) || !isset($_POST['password_confirmar'])) {
    $_SESSION['mensaje'] = "Faltan datos requeridos";
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/perfil');
    exit();
}

$password_actual = $_POST['password_actual'];
$password_nueva = $_POST['password_nueva'];
$password_confirmar = $_POST['password_confirmar'];

// Verificar que los campos no estén vacíos
if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
    $_SESSION['mensaje'] = "Todos los campos de contraseña son obligatorios";
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/perfil');
    exit();
}

// Verificar que las contraseñas nuevas coincidan
if ($password_nueva !== $password_confirmar) {
    $_SESSION['mensaje'] = "Las contraseñas nuevas no coinciden";
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/perfil');
    exit();
}

// Verificar longitud mínima de contraseña
if (strlen($password_nueva) < 6) {
    $_SESSION['mensaje'] = "La contraseña debe tener al menos 6 caracteres";
    $_SESSION['icono'] = "warning";
    header('Location: '.$URL.'/perfil');
    exit();
}

try {
    // Obtener la contraseña actual del usuario
    $sql = "SELECT password_user FROM tb_usuarios WHERE id_usuario = :id_usuario";
    $query = $pdo->prepare($sql);
    $query->bindParam(':id_usuario', $id_usuario);
    $query->execute();
    
    if ($query->rowCount() > 0) {
        $usuario = $query->fetch(PDO::FETCH_ASSOC);
        $password_hash = $usuario['password_user'];
        
        // Verificar si la contraseña actual es correcta
        if (password_verify($password_actual, $password_hash)) {
            // La contraseña actual es correcta, actualizar a la nueva
            $nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
            
            $sql_update = "UPDATE tb_usuarios SET 
                           password_user = :password_user,
                           fyh_actualizacion = :fyh_actualizacion
                           WHERE id_usuario = :id_usuario";
            
            $query_update = $pdo->prepare($sql_update);
            $query_update->bindParam(':password_user', $nuevo_hash);
            $query_update->bindParam(':fyh_actualizacion', $fechaHora);
            $query_update->bindParam(':id_usuario', $id_usuario);
            $query_update->execute();
            
            $_SESSION['mensaje'] = "Contraseña actualizada correctamente";
            $_SESSION['icono'] = "success";
        } else {
            $_SESSION['mensaje'] = "La contraseña actual es incorrecta";
            $_SESSION['icono'] = "error";
        }
    } else {
        $_SESSION['mensaje'] = "Error al verificar la contraseña";
        $_SESSION['icono'] = "error";
    }
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al actualizar la contraseña";
    $_SESSION['icono'] = "error";
}

header('Location: '.$URL.'/perfil');
exit();
?>