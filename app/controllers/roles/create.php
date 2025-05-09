<?php
include ('../../config.php');

// Obtener datos del formulario
$rol = trim($_POST['rol']);

try {
    // Verificar si el rol ya existe
    $sql_check = "SELECT COUNT(*) FROM tb_roles WHERE rol = :rol";
    $query_check = $pdo->prepare($sql_check);
    $query_check->bindParam(':rol', $rol, PDO::PARAM_STR);
    $query_check->execute();
    $rol_exists = $query_check->fetchColumn();

    if ($rol_exists > 0) {
        // Si el rol ya existe, redirigir con un mensaje de error
        session_start();
        $_SESSION['mensaje'] = "El rol ya existe. Intente con otro nombre.";
        $_SESSION['icono'] = "error";
        header('Location: '.$URL.'/roles/create.php');
        exit();
    }

    // Si el rol no existe, proceder a insertar
    $sentencia = $pdo->prepare("INSERT INTO tb_roles (rol, fyh_creacion) VALUES (:rol, :fyh_creacion)");
    $sentencia->bindParam(':rol', $rol);
    $sentencia->bindParam(':fyh_creacion', $fechaHora);
    
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Rol registrado correctamente.";
        $_SESSION['icono'] = "success";
        header('Location: '.$URL.'/roles/');
    } else {
        session_start();
        $_SESSION['mensaje'] = "Error al registrar el rol. Intente nuevamente.";
        $_SESSION['icono'] = "error";
        header('Location: '.$URL.'/roles/create.php');
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/roles/create.php');
}