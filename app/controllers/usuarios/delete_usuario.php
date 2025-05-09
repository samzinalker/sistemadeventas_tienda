<?php
include ('../../config.php');

$id_usuario = $_POST['id_usuario'];

try {
    // Verificar si el usuario existe
    $sql_check = "SELECT COUNT(*) FROM tb_usuarios WHERE id_usuario = :id_usuario";
    $query_check = $pdo->prepare($sql_check);
    $query_check->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $query_check->execute();
    $usuario_exists = $query_check->fetchColumn();

    if ($usuario_exists > 0) {
        // Eliminar al usuario
        $sentencia = $pdo->prepare("DELETE FROM tb_usuarios WHERE id_usuario = :id_usuario");
        $sentencia->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $sentencia->execute();

        session_start();
        $_SESSION['mensaje'] = "Usuario eliminado correctamente.";
        $_SESSION['icono'] = "success";
        header('Location: '.$URL.'/usuarios/');
    } else {
        session_start();
        $_SESSION['mensaje'] = "El usuario no existe.";
        $_SESSION['icono'] = "error";
        header('Location: '.$URL.'/usuarios/');
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error al eliminar el usuario.";
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/usuarios/');
}
?>