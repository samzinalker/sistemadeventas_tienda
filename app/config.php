<?php
// Asegúrate de que los errores de PHP no se muestren directamente en producción,
// pero para desarrollo, tenerlos visibles puede ayudar a diagnosticar problemas


if (!defined('SERVIDOR')) define('SERVIDOR','localhost');
if (!defined('USUARIO')) define('USUARIO','root');
if (!defined('PASSWORD')) define('PASSWORD',''); // Contraseña vacía para root es común en XAMPP/WAMP por defecto
if (!defined('BD')) define('BD','sistemadeventas');

$servidor = "mysql:dbname=".BD.";host=".SERVIDOR;

try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ];
    $pdo = new PDO($servidor, USUARIO, PASSWORD, $options);
} catch(PDOException $e) {
    error_log("Error de conexión a la BD en config.php: " . $e->getMessage());
    
    // Para depuración en desarrollo (quitar en producción)
    echo "Error de conexión: " . $e->getMessage();
    
    die("Error crítico: No se pudo conectar a la base de datos. Por favor, contacte al administrador.");
}

// URL base para tu proyecto en InfinityFree
$URL = "http://localhost/sistemadeventas";

date_default_timezone_set('America/Guayaquil');
$fechaHora = date('Y-m-d H:i:s');

// ✅ CONFIGURACIÓN DE ROLES DEL SISTEMA
define('ROL_REGISTRO_PUBLICO', 7); 
define('ROL_ADMINISTRADOR', 1);    
define('ROL_VENDEDOR', 7);         

// Función para obtener el rol por defecto para registro público
function obtenerRolRegistroPublico(): int {
    return ROL_REGISTRO_PUBLICO;
}

// Función para validar que un rol existe antes de asignarlo
function validarRolExiste(PDO $pdo, int $id_rol): bool {
    try {
        $sql = "SELECT COUNT(*) FROM tb_roles WHERE id_rol = :id_rol";
        $query = $pdo->prepare($sql);
        $query->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error al validar rol: " . $e->getMessage());
        return false;
    }
}