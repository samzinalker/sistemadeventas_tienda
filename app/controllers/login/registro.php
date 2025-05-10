<?php
include('../../config.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capturar datos del formulario
    $nombres = trim($_POST['nombres']);
    $email = trim($_POST['email']); // Este será el usuario
    $password = $_POST['password_user'];
    $repassword = $_POST['repassword'];
    
    // Validaciones básicas
    if (empty($nombres) || empty($email) || empty($password) || empty($repassword)) {
        $_SESSION['mensaje'] = "Todos los campos son obligatorios";
        $_SESSION['icono'] = "warning";
        header("Location: " . $URL . "/login/registro.php");
        exit();
    }
    
    // Verificar contraseñas
    if ($password !== $repassword) {
        $_SESSION['mensaje'] = "Las contraseñas no coinciden";
        $_SESSION['icono'] = "error";
        header("Location: " . $URL . "/login/registro.php");
        exit();
    }
    
    // Verificar longitud mínima de contraseña
    if (strlen($password) < 6) {
        $_SESSION['mensaje'] = "La contraseña debe tener al menos 6 caracteres";
        $_SESSION['icono'] = "warning";
        header("Location: " . $URL . "/login/registro.php");
        exit();
    }
    
    try {
        // Verificar si el usuario ya existe
        $sql_check = "SELECT * FROM tb_usuarios WHERE email = :email";
        $query_check = $pdo->prepare($sql_check);
        $query_check->bindParam(':email', $email);
        $query_check->execute();
        
        if ($query_check->rowCount() > 0) {
            $_SESSION['mensaje'] = "Este nombre de usuario ya está registrado";
            $_SESSION['icono'] = "warning";
            header("Location: " . $URL . "/login/registro.php");
            exit();
        }
        
        // Hashear contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // ID del rol vendedor (ajusta según tu configuración)
        $id_rol = 7; // vendedor por defecto
        
        // Fechas
        $fyh_creacion = date('Y-m-d H:i:s');
        $fyh_actualizacion = "0000-00-00 00:00:00";
        
        // Insertar nuevo usuario
        $sql = "INSERT INTO tb_usuarios (nombres, email, password_user, token, id_rol, fyh_creacion, fyh_actualizacion) 
                VALUES (:nombres, :email, :password_user, '', :id_rol, :fyh_creacion, :fyh_actualizacion)";
        
        $query = $pdo->prepare($sql);
        $query->bindParam(':nombres', $nombres);
        $query->bindParam(':email', $email);
        $query->bindParam(':password_user', $password_hash);
        $query->bindParam(':id_rol', $id_rol);
        $query->bindParam(':fyh_creacion', $fyh_creacion);
        $query->bindParam(':fyh_actualizacion', $fyh_actualizacion);
        
        if ($query->execute()) {
            $_SESSION['mensaje'] = "Usuario registrado correctamente. Ya puedes iniciar sesión.";
            $_SESSION['icono'] = "success";
            header("Location: " . $URL . "/login");
            exit();
        } else {
            throw new PDOException("Error al insertar datos");
        }
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "Error en el sistema: " . $e->getMessage();
        $_SESSION['icono'] = "error";
        header("Location: " . $URL . "/login/registro.php");
        exit();
    }
} else {
    // Si no es método POST, redirigir
    header("Location: " . $URL . "/login");
    exit();
}