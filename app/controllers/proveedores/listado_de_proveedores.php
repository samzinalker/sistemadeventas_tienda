<?php
// Resumen: Script para obtener y preparar la lista de proveedores para el usuario actualmente logueado.
// Este script es incluido por la vista principal del módulo de proveedores (ej. proveedores/index.php).
// Asume que la sesión del usuario ya ha sido validada y que las variables de configuración
// como $pdo (conexión a la base de datos) y $_SESSION['id_usuario'] están disponibles.
// El resultado ($proveedores_datos) es un array con los datos de los proveedores,
// que luego se utiliza en la vista para renderizar la tabla de proveedores.

// Verificar que las dependencias cruciales estén disponibles.
// Esto es una medida de seguridad y ayuda en la depuración.
if (!isset($pdo)) {
    // Si $pdo no está definido, es un error de configuración grave.
    // Se registra el error y se inicializa $proveedores_datos como array vacío
    // para evitar errores subsiguientes en la vista que espera esta variable.
    error_log("Error crítico: La conexión PDO (\$pdo) no está disponible en listado_de_proveedores.php. Asegúrese de que config.php se incluya antes que este script.");
    $proveedores_datos = [];
    // En un entorno de producción, podrías querer mostrar un mensaje de error más amigable o redirigir.
} elseif (!isset($_SESSION['id_usuario'])) {
    // Si no hay un ID de usuario en la sesión, significa que la gestión de sesión no funcionó como se esperaba
    // o el usuario no está correctamente logueado.
    error_log("Error crítico: \$_SESSION['id_usuario'] no está definido en listado_de_proveedores.php. Asegúrese de que layout/sesion.php se incluya y se ejecute correctamente antes que este script.");
    $proveedores_datos = [];
    // Podrías redirigir al login aquí si es apropiado, aunque sesion.php ya debería haberlo hecho.
    // Ejemplo: redirigir('/login/'); (necesitaría la función redirigir y $URL)
} else {
    // Si todo está bien, proceder a obtener los datos de los proveedores.
    $id_usuario_actual = (int)$_SESSION['id_usuario']; // Obtener y castear el ID del usuario de la sesión.

    try {
        // Consulta SQL para seleccionar todos los proveedores que pertenecen al usuario logueado.
        // Se ordenan alfabéticamente por el nombre del proveedor para una mejor visualización.
        $sql_proveedores = "SELECT * FROM tb_proveedores 
                            WHERE id_usuario = :id_usuario 
                            ORDER BY nombre_proveedor ASC";
        
        $query_proveedores = $pdo->prepare($sql_proveedores);
        
        // Vincular el ID del usuario actual a la consulta para filtrar los resultados.
        // PDO::PARAM_INT asegura que el tipo de dato es correcto.
        $query_proveedores->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT);
        
        $query_proveedores->execute();
        
        // Obtener todos los resultados como un array asociativo.
        // Si no hay proveedores, $proveedores_datos será un array vacío.
        $proveedores_datos = $query_proveedores->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // En caso de un error durante la consulta a la base de datos:
        // 1. Registrar el error detallado en el log del servidor (no mostrarlo directamente al usuario por seguridad).
        error_log("Error de PDO en listado_de_proveedores.php al obtener proveedores: " . $e->getMessage());
        
        // 2. Asignar un array vacío a $proveedores_datos para que la vista no falle.
        $proveedores_datos = [];
        
        // 3. Opcional: Establecer un mensaje de error amigable para el usuario (si tu sistema de mensajes lo maneja).
        //    Si usas mensajes flash en la sesión:
        //    if (function_exists('setMensaje')) { // Verificar si la función existe
        //        setMensaje("Ocurrió un error al cargar la lista de proveedores. Por favor, intente más tarde.", "error");
        //    }
    }
}
?>