<?php
include ('../../config.php');

// Obtener datos del formulario
$nombres = $_POST['nombres'];
$email = $_POST['email'];
$password_user = $_POST['password_user'];
$password_repeat = $_POST['password_repeat'];
$id_usuario = $_POST['id_usuario'];
$rol = $_POST['rol'];

try {
    // Validar si las contraseñas coinciden (si se ingresaron)
    if (!empty($password_user) || !empty($password_repeat)) {
        if ($password_user === $password_repeat) {
            $password_hash = password_hash($password_user, PASSWORD_DEFAULT);

            // Actualizar datos incluyendo la contraseña
            $sentencia = $pdo->prepare("UPDATE tb_usuarios 
                SET nombres = :nombres, email = :email, id_rol = :id_rol, 
                    password_user = :password_user, fyh_actualizacion = :fyh_actualizacion 
                WHERE id_usuario = :id_usuario");
            $sentencia->bindParam(':password_user', $password_hash);
        } else {
            session_start();
            $_SESSION['mensaje'] = "Las contraseñas no coinciden.";
            $_SESSION['icono'] = "error";
            header('Location: '.$URL.'/usuarios/update.php?id='.$id_usuario);
            exit();
        }
    } else {
        // Actualizar datos sin cambiar la contraseña
        $sentencia = $pdo->prepare("UPDATE tb_usuarios 
            SET nombres = :nombres, email = :email, id_rol = :id_rol, 
                fyh_actualizacion = :fyh_actualizacion 
            WHERE id_usuario = :id_usuario");
    }

    $sentencia->bindParam(':nombres', $nombres);
    $sentencia->bindParam(':email', $email);
    $sentencia->bindParam(':id_rol', $rol);
    $sentencia->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia->bindParam(':id_usuario', $id_usuario);
    $sentencia->execute();

    session_start();
    $_SESSION['mensaje'] = "Usuario actualizado correctamente.";
    $_SESSION['icono'] = "success";
    header('Location: '.$URL.'/usuarios/');
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error al actualizar el usuario.";
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/usuarios/update.php?id='.$id_usuario);
}
?>