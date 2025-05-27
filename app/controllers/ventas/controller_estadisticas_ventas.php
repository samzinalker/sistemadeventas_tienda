<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios - Asegurarse de usar la ruta correcta
include_once '../../config.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Función para devolver respuesta JSON
function responder($status, $message, $data = []) {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    responder('error', 'Sesión expirada o usuario no autenticado.');
}

$id_usuario = $_SESSION['id_usuario'];

// Log para depuración
error_log("Ejecutando estadísticas de ventas para usuario ID: $id_usuario - Fecha: " . date('Y-m-d H:i:s'));

try {
    // Consulta para obtener total de ventas por estado
    $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado_venta = 'PAGADA' THEN 1 ELSE 0 END) as pagadas,
                SUM(CASE WHEN estado_venta = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado_venta = 'ANULADA' THEN 1 ELSE 0 END) as anuladas,
                SUM(CASE WHEN estado_venta = 'ENTREGADA' THEN 1 ELSE 0 END) as entregadas
            FROM tb_ventas
            WHERE id_usuario = :id_usuario";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado) {
        // Asegurarse de que los valores no sean NULL
        $total = $resultado['total'] !== null ? intval($resultado['total']) : 0;
        $pagadas = $resultado['pagadas'] !== null ? intval($resultado['pagadas']) : 0;
        $pendientes = $resultado['pendientes'] !== null ? intval($resultado['pendientes']) : 0;
        $anuladas = $resultado['anuladas'] !== null ? intval($resultado['anuladas']) : 0;
        $entregadas = $resultado['entregadas'] !== null ? intval($resultado['entregadas']) : 0;
        
        // Registrar los valores para depuración
        error_log("Estadísticas obtenidas: Total=$total, Pagadas=$pagadas, Pendientes=$pendientes, Anuladas=$anuladas, Entregadas=$entregadas");
        
        responder('success', 'Estadísticas obtenidas correctamente', [
            'total' => $total,
            'pagadas' => $pagadas, 
            'pendientes' => $pendientes,
            'anuladas' => $anuladas,
            'entregadas' => $entregadas
        ]);
    } else {
        error_log("No se obtuvieron resultados de la consulta de estadísticas");
        responder('success', 'No hay datos de ventas disponibles', [
            'total' => 0,
            'pagadas' => 0,
            'pendientes' => 0,
            'anuladas' => 0,
            'entregadas' => 0
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en controller_estadisticas_ventas.php: " . $e->getMessage());
    responder('error', 'Error al obtener estadísticas: ' . $e->getMessage());
}