<?php
// Mostrar solo los proveedores del usuario actual, independientemente del rol
$id_usuario = $_SESSION['id_usuario'];
$sql_proveedores = "SELECT * FROM tb_proveedores WHERE id_usuario = :id_usuario ORDER BY nombre_proveedor ASC";
$query_proveedores = $pdo->prepare($sql_proveedores);
$query_proveedores->bindParam(':id_usuario', $id_usuario);
$query_proveedores->execute();
$proveedores_datos = $query_proveedores->fetchAll(PDO::FETCH_ASSOC);
?>