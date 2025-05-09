<?php
include('../app/config.php');
if (session_status() == PHP_SESSION_NONE) session_start();

$id_usuario_actual = $_SESSION['id_usuario'];
$sql_carrito = "SELECT c.*, 
                       pro.nombre AS nombre_producto, 
                       pro.descripcion AS descripcion, 
                       pro.precio_venta AS precio_venta, 
                       pro.stock AS stock 
                FROM tb_carrito c
                INNER JOIN tb_almacen pro ON c.id_producto = pro.id_producto
                WHERE c.id_usuario = :id_usuario AND c.nro_venta = 0";
$query_carrito = $pdo->prepare($sql_carrito);
$query_carrito->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT);
$query_carrito->execute();
$carrito_datos = $query_carrito->fetchAll(PDO::FETCH_ASSOC);

$contador_de_carrito = 0;
$cantidad_total = 0;
$precio_total = 0;
?>
<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover table-striped">
        <thead>
            <tr>
                <th style="background-color:#4d66ca; text-align:center">Nro</th>
                <th style="background-color:#4d66ca; text-align:center">Producto</th>
                <th style="background-color:#4d66ca; text-align:center">Descripción</th>
                <th style="background-color:#4d66ca; text-align:center">Cantidad</th>
                <th style="background-color:#4d66ca; text-align:center">Precio Unitario</th>
                <th style="background-color:#4d66ca; text-align:center">Subtotal</th>
                <th style="background-color:#4d66ca; text-align:center">Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($carrito_datos as $carrito_dato): 
                $contador_de_carrito++;
                $subtotal = $carrito_dato['cantidad'] * $carrito_dato['precio_venta'];
                $cantidad_total += $carrito_dato['cantidad'];
                $precio_total += $subtotal;
            ?>
                <tr>
                    <td><center><?php echo $contador_de_carrito;?></center></td>
                    <td><center><?php echo htmlspecialchars($carrito_dato['nombre_producto']); ?></center></td>
                    <td><center><?php echo htmlspecialchars($carrito_dato['descripcion']); ?></center></td>
                    <td><center><?php echo $carrito_dato['cantidad']; ?></center></td>
                    <td><center><?php echo number_format($carrito_dato['precio_venta'],2); ?></center></td>
                    <td><center><?php echo number_format($subtotal,2); ?></center></td>
                    <td>
                        <center>
                            <form action="../app/controllers/ventas/borrar_carrito.php" method="post" style="display:inline;">
                                <input type="hidden" name="id_carrito" value="<?php echo $carrito_dato['id_carrito']; ?>"> 
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i>Borrar</button>
                            </form>
                        </center>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <th colspan="3" style="background-color:#aef77d;text-align:right">Total</th>
                <th><center><?php echo $cantidad_total;?></center></th>
                <th></th>
                <th style="background-color:#e0f933">
                    <center>
                        <span id="total_carrito"><?php echo number_format($precio_total,2);?></span>
                    </center>
                </th>
                <th></th>
            </tr>
        </tbody>
    </table>
</div>