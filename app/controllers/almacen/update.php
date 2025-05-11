<?php
include ('../../config.php');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Verifica autenticación
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['mensaje'] = "Debes iniciar sesión para realizar esta acción";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/login.php');
    exit();
}

$id_usuario_actual = $_SESSION['id_usuario'];

// Obtener datos del formulario
$codigo = $_POST['codigo'];
$id_categoria = $_POST['id_categoria'];
$nombre = $_POST['nombre'];
$descripcion = $_POST['descripcion'];
$stock = $_POST['stock'];
$stock_minimo = $_POST['stock_minimo'];
$stock_maximo = $_POST['stock_maximo'];
$precio_compra = (float)$_POST['precio_compra'];
$precio_venta = (float)$_POST['precio_venta'];
$fecha_ingreso = $_POST['fecha_ingreso'];

// Validación de stock no negativo
if ($stock < 0) {
    $_SESSION['mensaje'] = "El stock no puede ser negativo";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/almacen/create.php');
    exit();
}

// Validación de precios
if ($precio_compra <= 0 || $precio_venta <= 0) {
    $_SESSION['mensaje'] = "Los precios deben ser mayores a cero";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/almacen/create.php');
    exit();
}

// Manejo de imágenes
$nombreDelArchivo = date("Y-m-d-h-i-s");
$filename = $nombreDelArchivo . "__" . $_FILES['image']['name'];
$location = "../../../almacen/img_productos/" . $filename;

move_uploaded_file($_FILES['image']['tmp_name'], $location);

// Insertar en la base de datos usando siempre el ID del usuario actual
$sentencia = $pdo->prepare("INSERT INTO tb_almacen
       (codigo, nombre, descripcion, stock, stock_minimo, stock_maximo, precio_compra, precio_venta, fecha_ingreso, imagen, id_usuario, id_categoria, fyh_creacion) 
VALUES (:codigo, :nombre, :descripcion, :stock, :stock_minimo, :stock_maximo, :precio_compra, :precio_venta, :fecha_ingreso, :imagen, :id_usuario, :id_categoria, :fyh_creacion)");

$fechaHora = date("Y-m-d H:i:s");

$sentencia->bindParam('codigo', $codigo);
$sentencia->bindParam('nombre', $nombre);
$sentencia->bindParam('descripcion', $descripcion);
$sentencia->bindParam('stock', $stock);
$sentencia->bindParam('stock_minimo', $stock_minimo);
$sentencia->bindParam('stock_maximo', $stock_maximo);
$sentencia->bindParam('precio_compra', $precio_compra);
$sentencia->bindParam('precio_venta', $precio_venta);
$sentencia->bindParam('fecha_ingreso', $fecha_ingreso);
$sentencia->bindParam('imagen', $filename);
$sentencia->bindParam('id_usuario', $id_usuario_actual); // Usar ID del usuario actual de la sesión
$sentencia->bindParam('id_categoria', $id_categoria);
$sentencia->bindParam('fyh_creacion', $fechaHora);

if ($sentencia->execute()) {
    $_SESSION['mensaje'] = "Se registró el producto correctamente";
    $_SESSION['icono'] = "success";
    header('Location: ' . $URL . '/almacen/');
} else {
    $_SESSION['mensaje'] = "Error: no se pudo registrar en la base de datos";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/almacen/create.php');
}