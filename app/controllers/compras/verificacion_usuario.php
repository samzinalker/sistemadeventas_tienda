<?php
// Verificar si el usuario puede acceder a esta compra específica
$id_compra_get = $_GET['id'];
$id_usuario_actual = $_SESSION['id_usuario'];

$verificar = $pdo->prepare("SELECT id_usuario FROM tb_compras WHERE id_compra = :id_compra");
$verificar->bindParam(':id_compra', $id_compra_get, PDO::PARAM_INT);
$verificar->execute();
$compra = $verificar->fetch(PDO::FETCH_ASSOC);

if (!$compra || $compra['id_usuario'] != $id_usuario_actual) {
    $_SESSION['mensaje'] = "No tienes permiso para ver esta compra";
    $_SESSION['icono'] = "error";
    echo "<script>location.href = '$URL/compras';</script>";
    exit();
}
?>