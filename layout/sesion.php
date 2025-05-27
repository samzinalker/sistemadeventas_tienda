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
    die("Error crítico: Variables de configuración \$URL o \$pdo no disponibles en layout/sesion.php. Revise los logs del servidor PHP.");
}

// ✅ CORREGIDO: Validar por usuario en lugar de email
if (isset($_SESSION['usuario']) && isset($_SESSION['id_usuario'])) {
    $usuario_sesion = $_SESSION['usuario'];
    $id_usuario_sesion = $_SESSION['id_usuario'];
    
    error_log("DEBUG layout/sesion.php: usuario encontrado: " . $usuario_sesion . " para URI (" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . ")");

    try {
        // ✅ CORREGIDO: Buscar por usuario Y validar que esté activo
        $sql = "SELECT us.id_usuario, us.nombres, us.usuario, us.email, us.imagen_perfil,
                       rol.rol as rol, rol.id_rol
                FROM tb_usuarios as us 
                INNER JOIN tb_roles as rol ON us.id_rol = rol.id_rol 
                WHERE us.usuario = :usuario AND us.id_usuario = :id_usuario AND us.estado = 'activo'";
        
        $query = $pdo->prepare($sql);
        $query->bindParam(':usuario', $usuario_sesion, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario_sesion, PDO::PARAM_INT);
        $query->execute();
        
        error_log("DEBUG layout/sesion.php: Consulta de usuario ejecutada para " . $usuario_sesion);

        if ($query->rowCount() > 0) {
            $usuario = $query->fetch(PDO::FETCH_ASSOC);
            
            // ✅ CORREGIDO: Actualizar toda la información de sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombres'] = $usuario['nombres'];
            $_SESSION['usuario'] = $usuario['usuario'];
            $_SESSION['sesion_email'] = $usuario['email']; // Mantener para compatibilidad
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['imagen_perfil'] = $usuario['imagen_perfil'];

            error_log("DEBUG layout/sesion.php: Usuario encontrado y sesión establecida: ID " . $usuario['id_usuario'] . ", Rol: " . $usuario['rol'] . " para URI (" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . ")");

            // Variables de conveniencia para el scope que incluye este archivo
            $id_usuario_sesion = $_SESSION['id_usuario'];
            $rol_sesion = $_SESSION['rol'];
            $nombres_sesion = $_SESSION['nombres'];
            $usuario_sesion = $_SESSION['usuario'];
            $email_sesion = $_SESSION['sesion_email'];

        } else {
            error_log("WARN layout/sesion.php: Usuario NO encontrado en BD para usuario: " . $usuario_sesion . " o está inactivo. Redirigiendo a login (PUNTO 1) desde URI (" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . ")");
            
            // ✅ CORREGIDO: Limpiar toda la sesión
            session_unset();
            session_destroy();
            session_start(); // Reiniciar para mensajes
            
            $_SESSION['mensaje'] = "Tu sesión ha expirado, el usuario no es válido o ha sido desactivado (código S1). Por favor, inicia sesión de nuevo.";
            $_SESSION['icono'] = "warning";
            
            header('Location: ' . rtrim($URL, '/') . '/login/'); 
            exit();
        }
        
    } catch (PDOException $e) {
        error_log("ERROR PDO layout/sesion.php: " . $e->getMessage() . ". Redirigiendo a login (PUNTO 2) desde URI (" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . ")"); 

        // ✅ CORREGIDO: Limpiar toda la sesión en caso de error
        session_unset();
        session_destroy();
        session_start(); // Reiniciar para mensajes
        
        $_SESSION['mensaje'] = "Error de base de datos al verificar la sesión (código S2). Por favor, contacte al administrador.";
        $_SESSION['icono'] = "error";
        
        header('Location: ' . rtrim($URL, '/') . '/login/'); 
        exit();
    }
    
} else {
    error_log("WARN layout/sesion.php: Variables de sesión requeridas no están disponibles. Redirigiendo a login (PUNTO 3) desde URI (" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . ")");
    
    // ✅ CORREGIDO: No destruir sesión aquí para no perder mensajes de error
    header('Location: ' . rtrim($URL, '/') . '/login/'); 
    exit();
}

error_log("DEBUG layout/sesion.php: Sesión validada exitosamente para URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . ". Usuario: " . ($_SESSION['nombres'] ?? 'N/A') . " (@" . ($_SESSION['usuario'] ?? 'N/A') . ")");
?>