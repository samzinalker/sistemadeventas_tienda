<?php
include ('../../config.php');

// Obtener datos del formulario
$nombres = $_POST['nombres'];
$email = $_POST['email'];
$rol = $_POST['rol'];
$password_user = $_POST['password_user'];
$password_repeat = $_POST['password_repeat'];

try {
    // Verificar si el correo ya está registrado
    $sql_check = "SELECT COUNT(*) FROM tb_usuarios WHERE email = :email";
    $query_check = $pdo->prepare($sql_check);
    $query_check->bindParam(':email', $email, PDO::PARAM_STR);
    $query_check->execute();
    $email_exists = $query_check->fetchColumn();

    if ($email_exists > 0) {
        session_start();
        $_SESSION['mensaje'] = "El correo ya está registrado. Intente con otro.";
        $_SESSION['icono'] = "error";
        header('Location: '.$URL.'/usuarios/create.php');
        exit();
    }

    // Validar si las contraseñas coinciden
    if ($password_user === $password_repeat) {
        $password_hash = password_hash($password_user, PASSWORD_DEFAULT);

        // Insertar el nuevo usuario
        $sentencia = $pdo->prepare("INSERT INTO tb_usuarios 
            (nombres, email, id_rol, password_user, fyh_creacion) 
            VALUES (:nombres, :email, :id_rol, :password_user, :fyh_creacion)");
        $sentencia->bindParam(':nombres', $nombres);
        $sentencia->bindParam(':email', $email);
        $sentencia->bindParam(':id_rol', $rol);
        $sentencia->bindParam(':password_user', $password_hash);
        $sentencia->bindParam(':fyh_creacion', $fechaHora);
        $sentencia->execute();

        session_start();
        $_SESSION['mensaje'] = "Usuario registrado correctamente.";
        $_SESSION['icono'] = "success";
        header('Location: '.$URL.'/usuarios/');
    } else {
        session_start();
        $_SESSION['mensaje'] = "Las contraseñas no coinciden. Intente de nuevo.";
        $_SESSION['icono'] = "error";
        header('Location: '.$URL.'/usuarios/create.php');
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error al registrar el usuario.";
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/usuarios/create.php');
}
?>