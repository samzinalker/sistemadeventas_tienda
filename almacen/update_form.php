<?php
include ('../app/config.php');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../login.php');
    exit();
}
$id_usuario = $_SESSION['id_usuario'];
$id_producto = $_GET['id_producto'] ?? null;

if ($id_producto) {
    // Solo recupera el producto si es del usuario autenticado
    $sql = "SELECT * FROM tb_almacen WHERE id_producto = :id_producto AND id_usuario = :id_usuario";
    $query = $pdo->prepare($sql);
    $query->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $query->execute();
    $producto = $query->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        // No autorizado o producto no existe
        echo '<div class="alert alert-danger">No tienes permiso para editar este producto o no existe.</div>';
        exit();
    }
} else {
    echo '<div class="alert alert-danger">ID de producto no proporcionado.</div>';
    exit();
}
?>

<div class="container mt-4">
    <h2>Editar Producto</h2>
    <form action="../app/controllers/almacen/update.php" method="post">
        <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($producto['id_producto']); ?>">
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
        </div>
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" class="form-control" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Stock</label>
            <input type="number" name="stock" class="form-control" value="<?php echo htmlspecialchars($producto['stock']); ?>" required>
        </div>
        <!-- Más campos según tu modelo -->
        <button type="submit" class="btn btn-success">Guardar cambios</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>