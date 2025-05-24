<?php
require_once __DIR__ . '/../../app/config.php'; // Ajusta la ruta según sea necesario *le quite el  /..

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario_sesion'])) {
    echo json_encode(['results' => [], 'pagination' => ['more' => false], 'message' => 'Usuario no autenticado.']);
    exit;
}

$id_usuario_logueado = $_SESSION['id_usuario_sesion'];
$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Número de resultados por página para Select2

$response = ['results' => [], 'pagination' => ['more' => false]];

if (empty($searchTerm) && $page === 1) { // Podrías mostrar algunos clientes por defecto si no hay término
    // Opcional: Cargar algunos clientes iniciales si el término está vacío en la primera carga.
    // Por ahora, solo buscamos si hay término.
}

if (!empty($searchTerm)) {
    try {
        // Contar total de resultados para la paginación
        $sqlCount = "SELECT COUNT(id_cliente) as total_count 
                     FROM tb_clientes 
                     WHERE id_usuario = :id_usuario 
                       AND (nombre_cliente LIKE :term 
                            OR nit_ci_cliente LIKE :term_documento)";
        
        $stmtCount = $pdo->prepare($sqlCount);
        $stmtCount->bindValue(':id_usuario', $id_usuario_logueado, PDO::PARAM_INT);
        $stmtCount->bindValue(':term', '%' . $searchTerm . '%', PDO::PARAM_STR);
        $stmtCount->bindValue(':term_documento', $searchTerm . '%', PDO::PARAM_STR); // Búsqueda más precisa por inicio de documento
        $stmtCount->execute();
        $total_count = $stmtCount->fetchColumn();

        // Obtener resultados paginados
        $offset = ($page - 1) * $limit;
        $sql = "SELECT id_cliente, nombre_cliente, nit_ci_cliente, tipo_documento, celular_cliente, email_cliente 
                FROM tb_clientes 
                WHERE id_usuario = :id_usuario 
                  AND (nombre_cliente LIKE :term 
                       OR nit_ci_cliente LIKE :term_documento)
                ORDER BY nombre_cliente ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_usuario', $id_usuario_logueado, PDO::PARAM_INT);
        $stmt->bindValue(':term', '%' . $searchTerm . '%', PDO::PARAM_STR);
        $stmt->bindValue(':term_documento', $searchTerm . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($clientes as $cliente) {
            $results[] = [
                'id' => $cliente['id_cliente'], // Select2 espera 'id'
                'text' => $cliente['nombre_cliente'], // Select2 espera 'text'
                // Puedes añadir más datos para usarlos en `templateResult` y `templateSelection`
                'nombre_cliente' => $cliente['nombre_cliente'],
                'nit_ci_cliente' => $cliente['nit_ci_cliente'],
                'tipo_documento' => ucfirst(str_replace('_', ' ', $cliente['tipo_documento'])), // Formatear tipo_documento
                'celular_cliente' => $cliente['celular_cliente'],
                'email_cliente' => $cliente['email_cliente']
            ];
        }
        $response['results'] = $results;
        if (($page * $limit) < $total_count) {
            $response['pagination']['more'] = true;
        }

    } catch (PDOException $e) {
        // Loggear el error $e->getMessage()
        $response['message'] = 'Error al buscar clientes: ' . $e->getMessage();
        // No envíes 'status' => 'error' aquí, Select2 espera un formato específico.
        // Un array vacío de 'results' y un mensaje de error (si lo manejas en el frontend) es suficiente.
    }
} else if ($page === 1) { // Si el término es vacío pero es la primera página, podrías devolver algo
     try {
        // Cargar algunos clientes por defecto (ej. los últimos 10 creados o los más frecuentes)
        // Esto es opcional. Para este ejemplo, solo se devuelve si hay término.
        // Si quieres cargar clientes por defecto:
        $sqlDefault = "SELECT id_cliente, nombre_cliente, nit_ci_cliente, tipo_documento, celular_cliente, email_cliente 
                       FROM tb_clientes 
                       WHERE id_usuario = :id_usuario AND estado = 'activo'
                       ORDER BY fyh_creacion DESC 
                       LIMIT :limit";
        $stmtDefault = $pdo->prepare($sqlDefault);
        $stmtDefault->bindValue(':id_usuario', $id_usuario_logueado, PDO::PARAM_INT);
        $stmtDefault->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmtDefault->execute();
        $clientes_default = $stmtDefault->fetchAll(PDO::FETCH_ASSOC);

        $results_default = [];
        foreach ($clientes_default as $cliente) {
             $results_default[] = [
                'id' => $cliente['id_cliente'],
                'text' => $cliente['nombre_cliente'],
                'nombre_cliente' => $cliente['nombre_cliente'],
                'nit_ci_cliente' => $cliente['nit_ci_cliente'],
                'tipo_documento' => ucfirst(str_replace('_', ' ', $cliente['tipo_documento'])),
                'celular_cliente' => $cliente['celular_cliente'],
                'email_cliente' => $cliente['email_cliente']
            ];
        }
        $response['results'] = $results_default;
        // Aquí podrías calcular si hay más clientes para paginación inicial si fuera necesario.

    } catch (PDOException $e) {
        $response['message'] = 'Error al cargar clientes iniciales: ' . $e->getMessage();
    }
}


echo json_encode($response);
?>