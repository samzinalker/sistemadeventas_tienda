<?php
// Resumen: Controlador para servir datos de proveedores a DataTables con procesamiento del lado del servidor.
// Maneja la paginación, búsqueda y ordenamiento.

require_once __DIR__ . '/../../config.php'; // Define $pdo, $URL, $fechaHora
// No necesitas funciones_globales.php aquí a menos que vayas a sanear la salida, lo cual es bueno.
// require_once __DIR__ . '/../../utils/funciones_globales.php'; 
require_once __DIR__ . '/../../models/ProveedorModel.php'; // Usaremos el modelo para interactuar con la BD

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

$proveedorModel = new ProveedorModel($pdo, $URL);

// Parámetros de DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0; // Inicio del conjunto de resultados
$length = isset($_POST['length']) ? intval($_POST['length']) : 10; // Número de registros a devolver
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : ''; // Valor de búsqueda global

// Columnas para ordenamiento (ejemplo básico, ajustar según tus columnas visibles y buscables)
$columnOrderIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
$columnOrderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';

// Mapeo de índices de columna de DataTables a nombres de columna de la BD
// IMPORTANTE: Ajusta esto según las columnas en tu tabla #tablaProveedores y su orden
$columns = [
    0 => 'nombre_proveedor', // Asume que la primera columna es el nombre
    1 => 'celular',
    2 => 'telefono',
    3 => 'empresa',
    4 => 'email',
    5 => 'direccion'
    // Añade más si es necesario, o si el orden es diferente
];
$orderBy = $columns[$columnOrderIndex] ?? 'nombre_proveedor'; // Columna por defecto para ordenar


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

// Aplicar filtro de búsqueda si existe
if (!empty($searchValue)) {
    // Buscar en múltiples columnas. Ajusta las columnas según necesites.
    $sqlData .= " AND (nombre_proveedor LIKE :searchValue 
                     OR celular LIKE :searchValue 
                     OR empresa LIKE :searchValue 
                     OR email LIKE :searchValue)";
    $bindings[':searchValue'] = '%' . $searchValue . '%';
}

// Contar registros después de aplicar el filtro (para recordsFiltered)
$sqlFilteredCount = str_replace("SELECT id_proveedor, nombre_proveedor, celular, telefono, empresa, email, direccion", "SELECT COUNT(id_proveedor)", $sqlData);
$queryFilteredCount = $pdo->prepare($sqlFilteredCount);
foreach ($bindings as $key => $value) {
    $queryFilteredCount->bindValue($key, $value, (is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR));
}
$queryFilteredCount->execute();
$recordsFiltered = (int)$queryFilteredCount->fetchColumn();

// Aplicar ordenamiento
$sqlData .= " ORDER BY " . $orderBy . " " . $columnOrderDir;

// Aplicar paginación
$sqlData .= " LIMIT :start, :length";

$queryData = $pdo->prepare($sqlData);
foreach ($bindings as $key => $value) {
    // Re-bind para la consulta principal, ya que $searchValue podría no estar en la primera
    if ($key === ':searchValue' || $key === ':id_usuario') { // Asegúrate que todos los placeholders estén
         $queryData->bindValue($key, $value, (is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR));
    }
}
$queryData->bindParam(':id_usuario', $id_usuario_logueado, PDO::PARAM_INT); // Asegurar que id_usuario siempre está
$queryData->bindParam(':start', $start, PDO::PARAM_INT);
$queryData->bindParam(':length', $length, PDO::PARAM_INT);

$queryData->execute();
$proveedores = $queryData->fetchAll(PDO::FETCH_ASSOC);

$data = [];
foreach ($proveedores as $proveedor) {
    // Formatea los datos como DataTables los espera.
    // Aquí puedes añadir botones de acción si los necesitas directamente en los datos de la fila.
    // Por ahora, solo devolvemos los datos crudos.
    $data[] = [
        $proveedor['nombre_proveedor'], // Columna 0
        $proveedor['celular'],          // Columna 1
        $proveedor['telefono'],         // Columna 2
        $proveedor['empresa'],          // Columna 3
        $proveedor['email'],            // Columna 4
        $proveedor['direccion'],        // Columna 5
        // Ejemplo de cómo añadir botones de acción:
        // '<button onclick="editarProveedor('.$proveedor['id_proveedor'].')">Editar</button>'
        // Asegúrate que el número de elementos aquí coincida con el número de columnas <th> en tu HTML
    ];
}

$response = [
    "draw" => $draw,
    "recordsTotal" => $recordsTotal,
    "recordsFiltered" => $recordsFiltered,
    "data" => $data
];

echo json_encode($response);
?>