<?php
// Este archivo debe incluirse DESPUÉS de app/config.php y DESPUÉS de session_start() si se usa sesión.

/**
 * Establece un mensaje de feedback para el usuario en la sesión.
 * @param string $mensaje El mensaje a mostrar.
 * @param string $tipoIcono 'success', 'error', 'warning', 'info'.
 */
function setMensaje(string $mensaje, string $tipoIcono): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['icono'] = $tipoIcono;
}

/**
 * Redirige al usuario a una URL relativa a la URL base del sitio.
 * La variable global $URL debe estar definida (desde config.php).
 * @param string $url_relativa La ruta a la que redirigir (ej. '/usuarios/' o '/login.php').
 */
function redirigir(string $url_relativa): void {
    global $URL; // Necesita acceso a la variable $URL de config.php
    if (!isset($URL)) {
        // Fallback por si $URL no está definida, aunque no debería pasar si config.php se incluye primero.
        // Esto podría ser un error fatal o una redirección a una página de error genérica.
        // Por simplicidad, aquí solo intentamos una redirección simple.
        header("Location: " . $url_relativa);
        exit();
    }
    header("Location: " . rtrim($URL, '/') . '/' . ltrim($url_relativa, '/'));
    exit();
}

/**
 * Procesa y valida las contraseñas.
 * @param string $password La contraseña ingresada.
 * @param string $password_repeat La confirmación de la contraseña.
 * @param int $minLength Longitud mínima requerida para la contraseña.
 * @return array Retorna un array: [?string $hash, ?string $error_mensaje].
 *               Si es exitoso, $hash no es null y $error_mensaje es null.
 *               Si hay error, $hash es null y $error_mensaje contiene el error.
 */
function procesarPassword(string $password, string $password_repeat, int $minLength = 6): array {
    if (!Validator::isValidPasswordLength($password, $minLength)) {
        return [null, "La contraseña debe tener al menos {$minLength} caracteres."];
    }
    if ($password !== $password_repeat) {
        return [null, "Las contraseñas no coinciden."];
    }
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    if ($password_hash === false) {
        // Esto sería un error interno del servidor con password_hash
        return [null, "Error al procesar la contraseña. Intente de nuevo."];
    }
    
    return [$password_hash, null];
}

/**
 * Sanitiza una cadena para prevenir XSS básico al mostrarla en HTML.
 * @param string|null $string
 * @return string
 */
function sanear(?string $string): string {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

?>