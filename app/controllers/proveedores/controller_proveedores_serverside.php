<?php
// Resumen: Controlador para servir datos de proveedores a DataTables con procesamiento del lado del servidor.
// Maneja la paginación, búsqueda y ordenamiento.

require_once __DIR__ . '/../../config.php'; // Define $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../models/ProveedorModel.php'; 

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Debe iniciar sesión para esta acción."
    ]);
    exit();
}
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

// Parámetros de DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

$columnOrderIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
$columnOrderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';

// Mapeo de índices de columna de DataTables a nombres de columna de la BD
// IMPORTANTE: Ajusta esto según las columnas en tu tabla #tablaProveedores y su orden VISIBLE
// Esto es para el ORDENAMIENTO.
$columnsSortable = [
    0 => 'id_proveedor',       // Columna ID
    1 => 'nombre_proveedor',   // Columna Nombre
    2 => 'empresa',            // Columna Empresa
    3 => 'celular',            // Columna Celular
    4 => 'telefono',           // Columna Teléfono
    5 => 'email',              // Columna Email
    6 => 'direccion'           // Columna Dirección
    // La columna 7 (Acciones) no es ordenable por datos de la BD
];
$orderBy = $columnsSortable[$columnOrderIndex] ?? 'nombre_proveedor'; // Por defecto ordenar por nombre


// --- Lógica para obtener el total de registros (sin filtrar) ---
$sqlTotal = "SELECT COUNT(id_proveedor) FROM tb_proveedores WHERE id_usuario = :id_usuario";
$queryTotal = $pdo->prepare($sqlTotal);
$queryTotal->bindParam(':id_usuario', $id_usuario_logueado, PDO::PARAM_INT);
$queryTotal->execute();
$recordsTotal = (int)$queryTotal->fetchColumn();

// --- Lógica para obtener registros filtrados y paginados ---
$sqlData = "SELECT id_proveedor, nombre_proveedor, celular, telefono, empresa, email, direccion 
            FROM tb_proveedores 
            WHERE id_usuario = :id_usuario";
$bindings = [':id_usuario' => $id_usuario_logueado];

if (!empty($searchValue)) {
    $sqlData .= " AND (nombre_proveedor LIKE :searchValue 
                     OR celular LIKE :searchValue 
                     OR empresa LIKE :searchValue 
                     OR email LIKE :searchValue
                     OR telefono LIKE :searchValue
                     OR direccion LIKE :searchValue)";
    $bindings[':searchValue'] = '%' . $searchValue . '%';
}

$sqlFilteredCount = "SELECT COUNT(id_proveedor) FROM tb_proveedores WHERE id_usuario = :id_usuario";
if (!empty($searchValue)) {
     $sqlFilteredCount .= " AND (nombre_proveedor LIKE :searchValueSearch 
                         OR celular LIKE :searchValueSearch 
                         OR empresa LIKE :searchValueSearch 
                         OR email LIKE :searchValueSearch
                         OR telefono LIKE :searchValueSearch
                         OR direccion LIKE :searchValueSearch)";
}

$queryFilteredCount = $pdo->prepare($sqlFilteredCount);
$queryFilteredCount->bindParam(':id_usuario', $id_usuario_logueado, PDO::PARAM_INT);
if (!empty($searchValue)) {
    $queryFilteredCount->bindValue(':searchValueSearch', '%' . $searchValue . '%', PDO::PARAM_STR);
}
$queryFilteredCount->execute();
$recordsFiltered = (int)$queryFilteredCount->fetchColumn();


$sqlData .= " ORDER BY " . $orderBy . " " . $columnOrderDir;
$sqlData .= " LIMIT :start, :length";

$queryData = $pdo->prepare($sqlData);
// Bind all parameters for the main data query
$queryData->bindParam(':id_usuario', $id_usuario_logueado, PDO::PARAM_INT);
if (!empty($searchValue)) {
    $queryData->bindValue(':searchValue', '%' . $searchValue . '%', PDO::PARAM_STR);
}
$queryData->bindParam(':start', $start, PDO::PARAM_INT);
$queryData->bindParam(':length', $length, PDO::PARAM_INT);

$queryData->execute();
$proveedores_result = $queryData->fetchAll(PDO::FETCH_ASSOC);

$data = [];
foreach ($proveedores_result as $proveedor_row) {
    // Formatea los datos como un array de OBJETOS
    // Cada propiedad del objeto corresponde a una fuente de datos para DataTables
    $data[] = [
        "id_proveedor"     => $proveedor_row['id_proveedor'],
        "nombre_proveedor" => $proveedor_row['nombre_proveedor'],
        "celular"          => $proveedor_row['celular'],
        "telefono"         => $proveedor_row['telefono'],
        "empresa"          => $proveedor_row['empresa'],
        "email"            => $proveedor_row['email'],
        "direccion"        => $proveedor_row['direccion']
        // Puedes añadir una columna "acciones" aquí si la generas en el servidor:
        // "acciones" => '<button class="btn btn-xs btn-success" onclick="seleccionarProveedor('.$proveedor_row['id_proveedor'].', \''.htmlspecialchars($proveedor_row['nombre_proveedor'], ENT_QUOTES).'\')">Seleccionar</button>'
    ];
}

$response = [
    "draw"            => $draw,
    "recordsTotal"    => $recordsTotal,
    "recordsFiltered" => $recordsFiltered,
    "data"            => $data // $data ahora es un array de objetos
];

echo json_encode($response);
?>