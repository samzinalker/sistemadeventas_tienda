<?php
include('../../config.php');

// Obtener los datos del formulario
$email = trim($_POST['email']); // Añadido trim para limpiar espacios
$password_user = $_POST['password_user'];

try {
    // Preparar consulta parametrizada
    $sql = "SELECT u.*, r.rol FROM tb_usuarios u 
            INNER JOIN tb_roles r ON u.id_rol = r.id_rol
            WHERE u.email = :email";
    $query = $pdo->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();

    // Verificar si el usuario existe
    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $email_tabla = $usuario['email'];
        $password_user_tabla = $usuario['password_user'];

        // Verificar la contraseña
        if (password_verify($password_user, $password_user_tabla)) {
            session_start();
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['sesion_email'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['nombres'] = $usuario['nombres'];

            // Actualizar hora de último acceso
            $fyh_actualizacion = date('Y-m-d H:i:s');
            $sql_update = "UPDATE tb_usuarios SET fyh_actualizacion = :fyh_actualizacion 
                         WHERE id_usuario = :id_usuario";
            $query_update = $pdo->prepare($sql_update);
            $query_update->bindParam(':fyh_actualizacion', $fyh_actualizacion);
            $query_update->bindParam(':id_usuario', $usuario['id_usuario']);
            $query_update->execute();

            header('Location: ' . $URL . '/index.php');
            exit();
        } else {
            // Contraseña incorrecta
            session_start();
            $_SESSION['mensaje'] = "Contraseña incorrecta";
            $_SESSION['icono'] = "error";
            header('Location: ' . $URL . '/login');
            exit();
        }
    } else {
        // Usuario no encontrado
        session_start();
        $_SESSION['mensaje'] = "Usuario no encontrado";
        $_SESSION['icono'] = "error";
        header('Location: ' . $URL . '/login');
        exit();
    }
} catch (Exception $e) {
    // Error en la base de datos
    session_start();
    $_SESSION['mensaje'] = "Error en el sistema. Intenta más tarde";
    $_SESSION['icono'] = "error";
    // En producción, lo ideal sería registrar el error pero no mostrarlo
    // error_log("Error en login: " . $e->getMessage());
    header('Location: ' . $URL . '/login');
    exit();
}
?>