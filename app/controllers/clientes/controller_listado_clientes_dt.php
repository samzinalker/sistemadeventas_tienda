<?php
require_once __DIR__ . '/../../../app/config.php'; // Para $pdo y $URL

// Iniciar sesión solo si no hay una activa, para acceder a $_SESSION['id_usuario']
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Usuario no autenticado. Por favor, inicie sesión."
    ]);
    exit;
}

$id_usuario_logueado = $_SESSION['id_usuario'];
$params = $_POST; // Parámetros enviados por DataTables

// Columnas de la base de datos que corresponden a las 'data' properties en la configuración JS de DataTables
// Esto es útil para construir la cláusula ORDER BY de forma segura.
$columnas_mapeadas_db = [
    'id_cliente',       // Generalmente no se ordena por ID directamente si está oculto
    'nombre_cliente',
    'tipo_documento',
    'nit_ci_cliente',
    'celular_cliente',
    'email_cliente'
    // 'direccion_cliente' // Si la añades a la tabla y quieres ordenarla
];


$bindings = [];
// Base de la consulta: seleccionar clientes del usuario logueado y que estén activos
$sqlBase = "FROM tb_clientes c WHERE c.id_usuario = :id_usuario_logueado AND c.estado = 'activo'";
$bindings[':id_usuario_logueado'] = $id_usuario_logueado;

// --- Búsqueda (search) ---
$sqlSearch = "";
if (!empty($params['search']['value'])) {
    $searchValue = '%' . trim($params['search']['value']) . '%';
    // Columnas en las que se realizará la búsqueda
    $columnas_busqueda = ['c.nombre_cliente', 'c.nit_ci_cliente', 'c.email_cliente', 'c.celular_cliente', 'c.tipo_documento'];
    
    $searchConditions = [];
    foreach ($columnas_busqueda as $col) {
        $searchConditions[] = $col . " LIKE :search_value";
    }
    $sqlSearch = " AND (" . implode(" OR ", $searchConditions) . ")";
    $bindings[':search_value'] = $searchValue;
}

// --- Conteo de Registros ---
// Total de registros sin el filtro de búsqueda de DataTables (pero con filtro de usuario y estado)
$stmtTotal = $pdo->prepare("SELECT COUNT(c.id_cliente) FROM tb_clientes c WHERE c.id_usuario = :id_usuario_logueado AND c.estado = 'activo'");
$stmtTotal->execute([':id_usuario_logueado' => $id_usuario_logueado]);
$recordsTotal = $stmtTotal->fetchColumn();

// Total de registros con el filtro de búsqueda de DataTables aplicado
$stmtFiltered = $pdo->prepare("SELECT COUNT(c.id_cliente) " . $sqlBase . $sqlSearch);
$stmtFiltered->execute($bindings);
$recordsFiltered = $stmtFiltered->fetchColumn();

// --- Ordenamiento (order) ---
$sqlOrder = "";
if (isset($params['order']) && count($params['order'])) {
    $orderParams = $params['order'][0]; // Tomar el primer criterio de ordenamiento
    $columnIndex = intval($orderParams['column']); // Índice de la columna según DataTables
    
    // Obtener el 'data' property de la columna desde la configuración de DataTables enviada
    $columnDataProperty = $params['columns'][$columnIndex]['data']; 

    // Validar que la columna por la que se quiere ordenar esté en nuestro mapeo seguro
    if (in_array($columnDataProperty, $columnas_mapeadas_db)) {
        $columnDir = strtoupper($orderParams['dir']) === 'ASC' ? 'ASC' : 'DESC'; // Dirección del ordenamiento
        $sqlOrder = " ORDER BY c." . $columnDataProperty . " " . $columnDir;
    } else {
        // Orden por defecto si la columna no es válida o no se especifica
        $sqlOrder = " ORDER BY c.nombre_cliente ASC";
    }
} else {
    $sqlOrder = " ORDER BY c.nombre_cliente ASC"; // Orden por defecto
}

// --- Paginación (limit y offset) ---
$sqlLimit = "";
if (isset($params['start']) && $params['length'] != -1) {
    // $params['start'] es el offset (desde dónde empezar)
    // $params['length'] es el limit (cuántos registros tomar)
    $sqlLimit = " LIMIT " . intval($params['start']) . ", " . intval($params['length']);
}

// --- Consulta Principal para obtener los datos ---
// Selecciona los campos que necesitas mostrar en la tabla y/o usar en el JavaScript al seleccionar un cliente
$campos_select = "c.id_cliente, c.nombre_cliente, c.tipo_documento, c.nit_ci_cliente, c.celular_cliente, c.email_cliente, c.direccion_cliente, c.provincia_cliente, c.ciudad_cliente, c.referencia_cliente, c.estado"; // Añadí c.estado también, puede ser útil

$querySql = "SELECT " . $campos_select . " " . $sqlBase . $sqlSearch . $sqlOrder . $sqlLimit;

$stmtData = $pdo->prepare($querySql);
$stmtData->execute($bindings);
$data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

// --- Preparar la respuesta para DataTables ---
$respuesta = [
    "draw" => isset($params['draw']) ? intval($params['draw']) : 0,
    "recordsTotal" => intval($recordsTotal),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $data
];

echo json_encode($respuesta);
?>