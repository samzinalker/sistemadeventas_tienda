<?php
// --- Resumen del Archivo ---
// Nombre: app/controllers/almacen/controller_buscar_productos_dt.php
// Función: Proporciona los datos de productos del almacén de un usuario específico
//          en el formato requerido por DataTables (Server-side processing).
//          Es invocado vía AJAX desde la tabla de búsqueda de productos en compras/create.php.
// Método HTTP esperado: POST
// Parámetros POST esperados (además de los de DataTables):
//   - id_usuario: El ID del usuario cuyos productos se listarán.
// Respuesta: JSON formateado para DataTables
//   {
//     "draw": <int>,
//     "recordsTotal": <int>,
//     "recordsFiltered": <int>,
//     "data": [
//       { "id_producto": ..., "codigo": ..., ... "iva_porcentaje_producto": ..., "nombre_categoria": ..., "iva_ultima_compra": ... },
//       ...
//     ]
//   }

require_once __DIR__ . '/../../config.php'; // Contiene $pdo, $URL, $fechaHora

// Verificar que id_usuario se haya enviado
if (!isset($_POST['id_usuario'])) {
    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "ID de usuario no proporcionado."
    ]);
    exit;
}
$id_usuario = filter_var($_POST['id_usuario'], FILTER_VALIDATE_INT);
if ($id_usuario === false) {
    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "ID de usuario inválido."
    ]);
    exit;
}


// Parámetros de DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10; 
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

// Columnas para ordenamiento y búsqueda (deben coincidir con los 'data' en JS)
$columns = [
    0 => 'p.id_producto',
    1 => 'p.codigo',
    2 => 'p.nombre',
    3 => 'p.stock',
    4 => 'p.precio_compra',
    5 => 'p.iva_predeterminado', // IVA predeterminado del producto en tb_almacen
    6 => 'c.nombre_categoria'
    // La columna de acción y el iva_ultima_compra no se ordenan directamente desde aquí de forma simple
];

$orderByColumnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 2; // Default order por nombre
$orderByColumn = $columns[$orderByColumnIndex] ?? $columns[2]; // Fallback a nombre si el índice es inválido

$orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';
if (!in_array(strtolower($orderDir), ['asc', 'desc'])) {
    $orderDir = 'asc';
}

$bindings = [':id_usuario_main' => $id_usuario]; // Usar alias para el binding principal

// --- Total de registros sin filtrar ---
$stmtTotal = $pdo->prepare("SELECT COUNT(p.id_producto) 
                            FROM tb_almacen p
                            WHERE p.id_usuario = :id_usuario_total");
$stmtTotal->execute([':id_usuario_total' => $id_usuario]);
$recordsTotal = $stmtTotal->fetchColumn();

// --- Construcción de la consulta principal ---
$sql = "SELECT p.id_producto, p.codigo, p.nombre, p.descripcion, p.stock, p.stock_minimo, p.stock_maximo,
               p.precio_compra, p.precio_venta, p.fecha_ingreso, p.imagen,
               p.id_usuario, p.id_categoria, p.fyh_creacion, p.fyh_actualizacion,
               c.nombre_categoria,
               p.iva_predeterminado AS iva_porcentaje_producto, -- IVA predeterminado del producto (de tb_almacen)
               (SELECT dc.porcentaje_iva_item 
                FROM tb_detalle_compras dc
                INNER JOIN tb_compras com ON dc.id_compra = com.id_compra
                WHERE dc.id_producto = p.id_producto AND com.id_usuario = p.id_usuario 
                ORDER BY com.fecha_compra DESC, dc.id_detalle_compra DESC 
                LIMIT 1
               ) AS iva_ultima_compra -- NUEVO: IVA de la última compra para este producto y usuario
        FROM tb_almacen p
        INNER JOIN tb_categorias c ON p.id_categoria = c.id_categoria
        WHERE p.id_usuario = :id_usuario_main"; // Usar alias para el binding principal

// --- Filtrado (búsqueda) ---
$searchSql = "";
if (!empty($searchValue)) {
    // La búsqueda por `iva_ultima_compra` es compleja con subconsultas y LIKE, se omite por simplicidad.
    // Se busca en los campos principales.
    $searchSql = " AND (p.codigo LIKE :searchValue OR 
                       p.nombre LIKE :searchValue OR 
                       p.descripcion LIKE :searchValue OR 
                       c.nombre_categoria LIKE :searchValue OR
                       p.iva_predeterminado LIKE :searchValue)";
    $bindings[':searchValue'] = '%' . $searchValue . '%';
}
$sql .= $searchSql;

// --- Total de registros CON filtro de búsqueda (para recordsFiltered) ---
// Para el COUNT, no necesitamos la subconsulta compleja, solo las condiciones de filtro principales.
$countSqlFiltered = "SELECT COUNT(p.id_producto) 
                     FROM tb_almacen p 
                     INNER JOIN tb_categorias c ON p.id_categoria = c.id_categoria
                     WHERE p.id_usuario = :id_usuario_filtered " . $searchSql; // Reutilizar $searchSql para las condiciones de búsqueda

$stmtFiltered = $pdo->prepare($countSqlFiltered);
$bindingsCountFiltered = [':id_usuario_filtered' => $id_usuario];
if (!empty($searchValue)) {
     $bindingsCountFiltered[':searchValue'] = '%' . $searchValue . '%';
}
$stmtFiltered->execute($bindingsCountFiltered);
$recordsFiltered = $stmtFiltered->fetchColumn();


// --- Ordenamiento y Paginación ---
$sql .= " ORDER BY " . $orderByColumn . " " . strtoupper($orderDir);
if ($length != -1) { // -1 significa mostrar todos los registros (DataTables)
    $sql .= " LIMIT :start, :length";
}

$stmt = $pdo->prepare($sql);

// Bind de los parámetros principales para la consulta de datos
$stmt->bindParam(':id_usuario_main', $id_usuario, PDO::PARAM_INT); // Usar alias para el binding principal
if (!empty($searchValue)) {
    // El binding ':searchValue' ya está en $bindings si searchValue no está vacío
    $stmt->bindParam(':searchValue', $bindings[':searchValue'], PDO::PARAM_STR);
}
if ($length != -1) {
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':length', $length, PDO::PARAM_INT);
}

// Ejecución y preparación de la respuesta
try {
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Asegurar que todos los productos tengan la propiedad iva_ultima_compra, incluso si es null
    // (La subconsulta SQL ya debería devolver NULL si no hay datos, pero esto es una doble verificación)
    foreach ($data as $key => $row) {
        if (!isset($row['iva_ultima_compra'])) {
            $data[$key]['iva_ultima_compra'] = null;
        }
    }

    $response = [
        "draw" => $draw,
        "recordsTotal" => intval($recordsTotal),
        "recordsFiltered" => intval($recordsFiltered),
        "data" => $data,
    ];

} catch (PDOException $e) {
    error_log("Error PDO en controller_buscar_productos_dt.php: " . $e->getMessage());
    $response = [
        "draw" => $draw,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Error de base de datos al buscar productos. Detalles: " . $e->getMessage() // Más detalles en el log, no necesariamente al cliente.
    ];
} catch (Exception $e) {
    error_log("Error general en controller_buscar_productos_dt.php: " . $e->getMessage());
    $response = [
        "draw" => $draw,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Error inesperado del servidor. Detalles: " . $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>