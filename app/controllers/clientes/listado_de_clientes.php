<?php
// Este script es incluido por index.php, donde config.php y sesion.php ya están cargados.
require_once __DIR__ . '/../../models/ClienteModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    if (isset($URL)) { redirigir('/login/'); } else { header('Location: ../../login/'); exit(); }
}
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

// Obtener los clientes del usuario
$clienteModel = new ClienteModel($pdo, $URL);

// Preparar consulta para obtener solo los clientes del usuario actual
$sql = "SELECT * FROM tb_clientes WHERE id_usuario = :id_usuario";
$query = $pdo->prepare($sql);
$query->bindParam(':id_usuario', $id_usuario_logueado, PDO::PARAM_INT);
$query->execute();
$clientes_datos = $query->fetchAll(PDO::FETCH_ASSOC);
?>