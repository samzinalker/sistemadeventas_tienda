<?php
// Iniciar sesión solo si no hay una activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DEBUG: Mostrar toda la sesión al inicio de este script
error_log("DEBUG layout/sesion.php: Contenido de SESSION al inicio para URI (" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "): " . print_r($_SESSION, true));


global $URL, $pdo;
if (!isset($URL) || !isset($pdo)) {
    error_log("ERROR CRITICO layout/sesion.php: \$URL o \$pdo no están disponibles. URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
    if (session_id()) {
        $_SESSION['mensaje'] = "Error crítico de configuración del sistema (S0). Contacte al administrador.";
        $_SESSION['icono'] = "error";
    }
    // No redirigir desde aquí si hay un error crítico de config, para poder ver el error.
    die("Error crítico: Variables de configuración \$URL o \$pdo no disponibles en layout/sesion.php. Revise los logs del servidor PHP.");
}


if (isset($_SESSION['sesion_email'])) {
    $email_sesion = $_SESSION['sesion_email'];
    error_log("DEBUG layout/sesion.php: sesion_email encontrado: " . $email_sesion . " para URI (" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . ")");

    try {
        $sql = "SELECT us.id_usuario as id_usuario, us.nombres as nombres, 
                       us.email as email, rol.rol as rol 
                FROM tb_usuarios as us 
                INNER JOIN tb_roles as rol ON us.id_rol = rol.id_rol 
                WHERE us.email = :email";
        $query = $pdo->prepare($sql);
        $query->bindParam(':email', $email_sesion, PDO::PARAM_STR);
        $query->execute();
        error_log("DEBUG layout/sesion.php: Consulta de usuario ejecutada para " . $email_sesion);

        if ($query->rowCount() > 0) {
            $usuario = $query->fetch(PDO::FETCH_ASSOC);
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombres'] = $usuario['nombres'];
            // $_SESSION['apellidos'] = $usuario['apellidos']; // 'apellidos' no está en tu SELECT actual
            $_SESSION['rol'] = $usuario['rol'];
            // $_SESSION['preferencia_iva'] = (float)$usuario['preferencia_iva']; // 'preferencia_iva' no está en tu SELECT actual

            error_log("DEBUG layout/sesion.php: Usuario encontrado y sesión establecida: ID " . $usuario['id_usuario'] . ", Rol: " . $usuario['rol'] . " para URI (" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . ")");

            // Estas variables son para conveniencia si se usan directamente en el scope que incluye este archivo
            // pero ya están en $_SESSION.
            $id_usuario_sesion = $_SESSION['id_usuario'];
            $rol_sesion = $_SESSION['rol'];
            $nombres_sesion = $_SESSION['nombres'];

        } else {
            error_log("WARN layout/sesion.php: Usuario NO encontrado en BD para email: " . $email_sesion . ". Redirigiendo a login (PUNTO 1) desde URI (" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . ")");
            unset($_SESSION['sesion_email'], $_SESSION['id_usuario'], $_SESSION['nombres'], $_SESSION['rol']);
            // unset($_SESSION['apellidos'], $_SESSION['preferencia_iva']); // No estaban en la sesión desde este script
            
            if (!isset($_SESSION['mensaje'])) { 
                $_SESSION['mensaje'] = "Tu sesión ha expirado o el usuario no es válido (código S1). Por favor, inicia sesión de nuevo.";
                $_SESSION['icono'] = "warning";
            }
            header('Location: ' . rtrim($URL, '/') . '/login/'); 
            exit();
        }
    } catch (PDOException $e) {
        error_log("ERROR PDO layout/sesion.php: " . $e->getMessage() . ". Redirigiendo a login (PUNTO 2) desde URI (" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . ")"); 

        unset($_SESSION['sesion_email'], $_SESSION['id_usuario'], $_SESSION['nombres'], $_SESSION['rol']);
        // unset($_SESSION['apellidos'], $_SESSION['preferencia_iva']);
        
        if (!isset($_SESSION['mensaje'])) {
            $_SESSION['mensaje'] = "Error de base de datos al verificar la sesión (código S2). Por favor, contacte al administrador.";
            $_SESSION['icono'] = "error";
        }
        header('Location: ' . rtrim($URL, '/') . '/login/'); 
        exit();
    }
} else {
    error_log("WARN layout/sesion.php: \$_SESSION['sesion_email'] NO ESTÁ SETEADO. Redirigiendo a login (PUNTO 3) desde URI (" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . ")");
    header('Location: ' . rtrim($URL, '/') . '/login/'); 
    exit();
}
error_log("DEBUG layout/sesion.php: Sesión validada exitosamente para URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . ". Usuario: " . ($_SESSION['nombres'] ?? 'N/A'));
?>