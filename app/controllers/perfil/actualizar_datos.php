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
if (!isset($_POST['nombres']) || !isset($_POST['email'])) {
    $_SESSION['mensaje'] = "Faltan datos requeridos";
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/perfil');
    exit();
}

$nombres = trim($_POST['nombres']);
$email = trim($_POST['email']);

// Verificar que los campos no estén vacíos
if (empty($nombres) || empty($email)) {
    $_SESSION['mensaje'] = "Los campos nombre y email son obligatorios";
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/perfil');
    exit();
}

try {
    // Verificar si el email está en uso por otro usuario
    $sql_check = "SELECT id_usuario FROM tb_usuarios WHERE email = :email AND id_usuario != :id_usuario";
    $query_check = $pdo->prepare($sql_check);
    $query_check->bindParam(':email', $email);
    $query_check->bindParam(':id_usuario', $id_usuario);
    $query_check->execute();
    
    if ($query_check->rowCount() > 0) {
        $_SESSION['mensaje'] = "El email ya está en uso por otro usuario";
        $_SESSION['icono'] = "error";
        header('Location: '.$URL.'/perfil');
        exit();
    }
    
    // Actualizar los datos del usuario
    $sql_update = "UPDATE tb_usuarios SET 
                   nombres = :nombres, 
                   email = :email,
                   fyh_actualizacion = :fyh_actualizacion
                   WHERE id_usuario = :id_usuario";
                   
    $query_update = $pdo->prepare($sql_update);
    $query_update->bindParam(':nombres', $nombres);
    $query_update->bindParam(':email', $email);
    $query_update->bindParam(':fyh_actualizacion', $fechaHora);
    $query_update->bindParam(':id_usuario', $id_usuario);
    $query_update->execute();
    
    // Actualizar la sesión con los nuevos datos
    $_SESSION['sesion_email'] = $email;
    $_SESSION['nombres'] = $nombres;
    
    $_SESSION['mensaje'] = "Datos actualizados correctamente";
    $_SESSION['icono'] = "success";
    
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al actualizar los datos";
    $_SESSION['icono'] = "error";
}

header('Location: '.$URL.'/perfil');
exit();
?>