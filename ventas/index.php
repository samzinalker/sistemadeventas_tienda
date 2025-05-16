<?php
include ('../app/config.php');
include ('../layout/sesion.php');
include ('../layout/parte1.php');
include ('../app/controllers/ventas/listado_de_ventas.php');
include ('../app/controllers/clientes/listado_de_clientes.php');

// Verificar que solo se muestran ventas del usuario actual
$id_usuario_actual = $_SESSION['id_usuario'] ?? null;
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">
                        <i class="fas fa-file-invoice-dollar text-primary"></i> Mis Ventas Realizadas
                    </h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list-alt"></i> Histórico de Ventas
                            </h3>
                            <div class="card-tools">
                                <a href="<?php echo $URL; ?>/ventas/create.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Nueva Venta
                                </a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body" style="display: block;">
                            <div class="table-responsive">
                                <table id="example1" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr class="text-center">
                                            <th style="width: 5%">#</th>
                                            <th style="width: 10%">Nro de venta</th>
                                            <th style="width: 15%">Fecha</th>
                                            <th style="width: 20%">Cliente</th>
                                            <th style="width: 15%">Productos</th>
                                            <th style="width: 15%">Total pagado</th>
                                            <th style="width: 20%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $contador = 0;
                                    foreach ($ventas_datos as $ventas_dato){
                                        $id_venta = $ventas_dato['id_venta'];
                                        $nro_venta = $ventas_dato['nro_venta'];
                                        $contador = $contador + 1;
                                        $fecha_venta = date('d/m/Y H:i', strtotime($ventas_dato['fyh_creacion']));
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $contador ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info">
                                                <?php echo $nro_venta; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <i class="far fa-calendar-alt mr-1"></i> <?php echo $fecha_venta; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-tie mr-2 text-primary"></i>
                                                <?php echo $ventas_dato['nombre_cliente']; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <center>
                                            <!-- Botón para abrir modal con productos -->
                                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#Modal_productos<?php echo $id_venta; ?>">
                                                <i class="fa fa-shopping-basket"></i> Ver Productos
                                            </button>

                                            <!-- Modal productos -->
                                            <div class="modal fade" id="Modal_productos<?php echo $id_venta; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-xl">
                                                    <div class="modal-content">
                                                        <div class="modal-header" style="background-color: #2c61a3; color: white">
                                                            <h5 class="modal-title">
                                                                <i class="fas fa-shopping-cart mr-2"></i> 
                                                                Productos de la venta #<?php echo $nro_venta; ?>
                                                            </h5>
                                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-sm table-hover table-striped">
                                                                    <thead>
                                                                        <tr class="text-center">
                                                                            <th style="background-color:#4d66ca; color: white">Nro</th>
                                                                            <th style="background-color:#4d66ca; color: white">Producto</th>
                                                                            <th style="background-color:#4d66ca; color: white">Descripción</th>
                                                                            <th style="background-color:#4d66ca; color: white">Cantidad</th>
                                                                            <th style="background-color:#4d66ca; color: white">Precio</th>
                                                                            <th style="background-color:#4d66ca; color: white">Subtotal</th>
                                                                            <th style="background-color:#4d66ca; color: white">IVA (%)</th>
                                                                            <th style="background-color:#4d66ca; color: white">Monto IVA</th>
                                                                            <th style="background-color:#4d66ca; color: white">Total+IVA</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <?php 
                                                                    $contador_de_carrito = 0;
                                                                    $cantidad_total = 0;
                                                                    $precio_unitario_total = 0;
                                                                    $precio_subtotal = 0;
                                                                    $iva_total = 0;
                                                                    $precio_total = 0;

                                                                    // Consultar productos en esta venta
                                                                    $sql_carrito = "SELECT c.*, 
                                                                                  p.nombre as nombre_producto, 
                                                                                  p.descripcion as descripcion,
                                                                                  p.codigo as codigo, 
                                                                                  p.precio_venta as precio_venta
                                                                            FROM tb_carrito c
                                                                            INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
                                                                            WHERE c.nro_venta = :nro_venta 
                                                                            AND c.id_usuario = :id_usuario
                                                                            ORDER BY c.id_carrito ASC";
                                                                    $query_carrito = $pdo->prepare($sql_carrito);
                                                                    $query_carrito->bindParam(':nro_venta', $nro_venta, PDO::PARAM_INT);
                                                                    $query_carrito->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT);
                                                                    $query_carrito->execute();
                                                                    $carrito_datos = $query_carrito->fetchAll(PDO::FETCH_ASSOC);
                                                                    
                                                                    foreach ($carrito_datos as $carrito_dato) { 
                                                                        $contador_de_carrito++;
                                                                        $cantidad_total += $carrito_dato['cantidad'];
                                                                        $subtotal = $carrito_dato['cantidad'] * $carrito_dato['precio_venta'];
                                                                        $precio_subtotal += $subtotal;
                                                                        
                                                                        // Obtener el porcentaje de IVA (por defecto 12% si no está definido)
                                                                        $porcentaje_iva = isset($carrito_dato['porcentaje_iva']) ? $carrito_dato['porcentaje_iva'] : 12;
                                                                        $monto_iva = $subtotal * ($porcentaje_iva / 100);
                                                                        $iva_total += $monto_iva;
                                                                        $total_con_iva = $subtotal + $monto_iva;
                                                                        $precio_total += $total_con_iva;
                                                                        
                                                                        // Obtener una versión corta de la descripción (máximo 30 caracteres)
                                                                        $descripcion_corta = strlen($carrito_dato['descripcion']) > 30 ? 
                                                                            substr($carrito_dato['descripcion'], 0, 30) . "..." : 
                                                                            $carrito_dato['descripcion'];
                                                                        $id_carrito = $carrito_dato['id_carrito'];
                                                                    ?>
                                                                        <tr>
                                                                            <td class="text-center"><?php echo $contador_de_carrito; ?></td>
                                                                            <td><?php echo $carrito_dato['nombre_producto']; ?></td>
                                                                            <td>
                                                                                <div class="d-flex justify-content-between align-items-center">
                                                                                    <?php echo $descripcion_corta; ?>
                                                                                    <button type="button" class="btn btn-sm btn-outline-info ml-2" 
                                                                                            data-toggle="modal" 
                                                                                            data-target="#modal-descripcion<?php echo $id_carrito; ?>">
                                                                                        <i class="fas fa-search"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </td>
                                                                            <td class="text-center"><?php echo $carrito_dato['cantidad']; ?></td>
                                                                            <td class="text-right">$<?php echo number_format($carrito_dato['precio_venta'], 2); ?></td>
                                                                            <td class="text-right">$<?php echo number_format($subtotal, 2); ?></td>
                                                                            <td class="text-center">
                                                                                <span class="badge badge-primary">
                                                                                    <?php echo number_format($porcentaje_iva, 2); ?>%
                                                                                </span>
                                                                            </td>
                                                                            <td class="text-right">$<?php echo number_format($monto_iva, 2); ?></td>
                                                                            <td class="text-right">$<?php echo number_format($total_con_iva, 2); ?></td>
                                                                        </tr>
                                                                        
                                                                        <!-- Modal para mostrar la descripción completa -->
                                                                        <div class="modal fade" id="modal-descripcion<?php echo $id_carrito; ?>" 
                                                                             data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
                                                                            <div class="modal-dialog modal-dialog-centered">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-header" style="background-color: #343a40; color: white">
                                                                                        <h5 class="modal-title">
                                                                                            <i class="fas fa-file-alt mr-2"></i> Descripción de Producto
                                                                                        </h5>
                                                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                                            <span aria-hidden="true">&times;</span>
                                                                                        </button>
                                                                                    </div>
                                                                                    <div class="modal-body">
                                                                                        <h4 class="text-center mb-3"><?php echo htmlspecialchars($carrito_dato['nombre_producto']); ?></h4>
                                                                                        <div class="card">
                                                                                            <div class="card-header">
                                                                                                <h5 class="card-title mb-0">Descripción Detallada</h5>
                                                                                            </div>
                                                                                            <div class="card-body">
                                                                                                <p class="card-text" style="white-space: pre-line;">
                                                                                                    <?php echo htmlspecialchars($carrito_dato['descripcion']); ?>
                                                                                                </p>
                                                                                            </div>
                                                                                            <div class="card-footer bg-light">
                                                                                                <div class="d-flex justify-content-between">
                                                                                                    <span><b>Código:</b> <?php echo htmlspecialchars($carrito_dato['codigo']); ?></span>
                                                                                                    <span><b>Precio:</b> $<?php echo number_format($carrito_dato['precio_venta'], 2); ?></span>
                                                                                                    <span><b>Cantidad:</b> <?php echo $carrito_dato['cantidad']; ?></span>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php } ?>
                                                                    <!-- Filas de totales -->
                                                                    <tr>
                                                                        <th colspan="5" style="background-color:#aef77d;text-align:right">Subtotal:</th>
                                                                        <th style="background-color:#e0f933;text-align:right">
                                                                            $<?php echo number_format($precio_subtotal, 2); ?>
                                                                        </th>
                                                                        <th></th>
                                                                        <th></th>
                                                                        <th></th>
                                                                    </tr>
                                                                    <tr>
                                                                        <th colspan="5" style="background-color:#aef77d;text-align:right">IVA Total:</th>
                                                                        <th></th>
                                                                        <th></th>
                                                                        <th style="background-color:#e0f933;text-align:right">
                                                                            $<?php echo number_format($iva_total, 2); ?>
                                                                        </th>
                                                                        <th></th>
                                                                    </tr>
                                                                    <tr>
                                                                        <th colspan="5" style="background-color:#aef77d;text-align:right">Total con IVA:</th>
                                                                        <th></th>
                                                                        <th></th>
                                                                        <th></th>
                                                                        <th style="background-color:#e0f933;text-align:right">
                                                                            $<?php echo number_format($precio_total, 2); ?>
                                                                        </th>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            </center>
                                        </td>
                                        <td class="text-center">
                                            <h5>
                                                <span class="badge badge-success">
                                                    $<?php echo number_format($ventas_dato['total_pagado'], 2); ?>
                                                </span>
                                            </h5>
                                        </td>
                                        <td>
                                            <div class="btn-group d-flex justify-content-center">
                                                <!-- Botón Ver - Ahora abre un modal en lugar de redirigir -->
                                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" 
                                                        data-target="#modal-ver-venta-<?php echo $id_venta; ?>" title="Ver detalles">
                                                    <i class="fa fa-eye"></i> Ver
                                                </button>
                                                
                                                <!-- Botón Borrar - Ahora elimina mediante AJAX -->
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        onclick="eliminarVenta(<?php echo $id_venta; ?>, <?php echo $nro_venta; ?>)" title="Eliminar venta">
                                                    <i class="fa fa-trash"></i> Borrar
                                                </button>
                                                
                                                <a href="factura.php?id_venta=<?php echo $id_venta; ?>&nro_venta=<?php echo $nro_venta; ?>" 
                                                   class="btn btn-success btn-sm" 
                                                   target="_blank" title="Imprimir factura">
                                                    <i class="fa fa-print"></i> Imprimir
                                                </a>
                                            </div>
                                            
                                            <!-- Modal para Ver detalles de la venta -->
                                            <div class="modal fade" id="modal-ver-venta-<?php echo $id_venta; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-xl">
                                                    <div class="modal-content">
                                                        <div class="modal-header" style="background-color: #2c61a3; color: white">
                                                            <h5 class="modal-title">
                                                                <i class="fas fa-info-circle mr-2"></i> 
                                                                Detalles de la Venta #<?php echo $nro_venta; ?>
                                                            </h5>
                                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="card card-outline card-primary">
                                                                        <div class="card-header">
                                                                            <h3 class="card-title">
                                                                                <i class="fas fa-user mr-2"></i> Datos del Cliente
                                                                            </h3>
                                                                        </div>
                                                                        <div class="card-body">
                                                                            <?php
                                                                            // Obtener datos del cliente
                                                                            $id_cliente = $ventas_dato['id_cliente'];
                                                                            $sql_cliente = "SELECT * FROM tb_clientes WHERE id_cliente = :id_cliente";
                                                                            $query_cliente = $pdo->prepare($sql_cliente);
                                                                            $query_cliente->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
                                                                            $query_cliente->execute();
                                                                            $cliente = $query_cliente->fetch(PDO::FETCH_ASSOC);
                                                                            ?>
                                                                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($cliente['nombre_cliente']); ?></p>
                                                                            <p><strong>NIT/CI:</strong> <?php echo htmlspecialchars($cliente['nit_ci_cliente']); ?></p>
                                                                            <p><strong>Celular:</strong> <?php echo htmlspecialchars($cliente['celular_cliente']); ?></p>
                                                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($cliente['email_cliente']); ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="card card-outline card-success">
                                                                        <div class="card-header">
                                                                            <h3 class="card-title">
                                                                                <i class="fas fa-dollar-sign mr-2"></i> Datos de la Venta
                                                                            </h3>
                                                                        </div>
                                                                        <div class="card-body">
                                                                            <p><strong>Número de venta:</strong> <?php echo $nro_venta; ?></p>
                                                                            <p><strong>Fecha:</strong> <?php echo $fecha_venta; ?></p>
                                                                            <p><strong>Subtotal:</strong> $<?php echo number_format($precio_subtotal, 2); ?></p>
                                                                            <p><strong>IVA Total:</strong> $<?php echo number_format($iva_total, 2); ?></p>
                                                                            <p><strong>Total pagado:</strong> $<?php echo number_format($ventas_dato['total_pagado'], 2); ?></p>
                                                                            <p><strong>Cantidad de productos:</strong> <?php echo $cantidad_total; ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row mt-3">
                                                                <div class="col-md-12">
                                                                    <div class="card card-outline card-info">
                                                                        <div class="card-header">
                                                                            <h3 class="card-title">
                                                                                <i class="fas fa-list mr-2"></i> Lista de Productos
                                                                            </h3>
                                                                        </div>
                                                                        <div class="card-body p-0">
                                                                            <div class="table-responsive">
                                                                                <table class="table table-striped table-sm mb-0">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>Producto</th>
                                                                                            <th>Cantidad</th>
                                                                                            <th>Precio Unit.</th>
                                                                                            <th>Subtotal</th>
                                                                                            <th>IVA (%)</th>
                                                                                            <th>Monto IVA</th>
                                                                                            <th>Total+IVA</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?php foreach ($carrito_datos as $carrito_dato): 
                                                                                            $subtotal = $carrito_dato['cantidad'] * $carrito_dato['precio_venta'];
                                                                                            
                                                                                            // Obtener el porcentaje de IVA
                                                                                            $porcentaje_iva = isset($carrito_dato['porcentaje_iva']) ? $carrito_dato['porcentaje_iva'] : 12;
                                                                                            $monto_iva = $subtotal * ($porcentaje_iva / 100);
                                                                                            $total_con_iva = $subtotal + $monto_iva;
                                                                                        ?>
                                                                                        <tr>
                                                                                            <td><?php echo htmlspecialchars($carrito_dato['nombre_producto']); ?></td>
                                                                                            <td class="text-center"><?php echo $carrito_dato['cantidad']; ?></td>
                                                                                            <td class="text-right">$<?php echo number_format($carrito_dato['precio_venta'], 2); ?></td>
                                                                                            <td class="text-right">$<?php echo number_format($subtotal, 2); ?></td>
                                                                                            <td class="text-center">
                                                                                                <span class="badge badge-primary">
                                                                                                    <?php echo number_format($porcentaje_iva, 2); ?>%
                                                                                                </span>
                                                                                            </td>
                                                                                            <td class="text-right">$<?php echo number_format($monto_iva, 2); ?></td>
                                                                                            <td class="text-right">$<?php echo number_format($total_con_iva, 2); ?></td>
                                                                                        </tr>
                                                                                        <?php endforeach; ?>
                                                                                    </tbody>
                                                                                    <tfoot>
                                                                                        <tr>
                                                                                            <th colspan="3" class="text-right">SUBTOTAL:</th>
                                                                                            <th class="text-right">$<?php echo number_format($precio_subtotal, 2); ?></th>
                                                                                            <th></th>
                                                                                            <th></th>
                                                                                            <th></th>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th colspan="3" class="text-right">IVA TOTAL:</th>
                                                                                            <th></th>
                                                                                            <th></th>
                                                                                            <th class="text-right">$<?php echo number_format($iva_total, 2); ?></th>
                                                                                            <th></th>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th colspan="3" class="text-right">TOTAL CON IVA:</th>
                                                                                            <th></th>
                                                                                            <th></th>
                                                                                            <th></th>
                                                                                            <th class="text-right">$<?php echo number_format($precio_total, 2); ?></th>
                                                                                        </tr>
                                                                                    </tfoot>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <a href="factura.php?id_venta=<?php echo $id_venta; ?>&nro_venta=<?php echo $nro_venta; ?>" 
                                                               class="btn btn-success" target="_blank">
                                                                <i class="fa fa-print"></i> Imprimir Factura
                                                            </a>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resumen de ventas -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo count($ventas_datos); ?></h3>
                            <p>Ventas Totales</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            Total de ventas registradas <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                        <div class="inner">
                            <?php
                                $total_ventas = 0;
                                foreach($ventas_datos as $venta) {
                                    $total_ventas += $venta['total_pagado'];
                                }
                            ?>
                            <h3>$<?php echo number_format($total_ventas, 2); ?></h3>
                            <p>Ingresos Totales</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            Monto total de ventas <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <?php
                                $ventas_hoy = 0;
                                $fecha_hoy = date('Y-m-d');
                                foreach($ventas_datos as $venta) {
                                    $fecha_venta = date('Y-m-d', strtotime($venta['fyh_creacion']));
                                    if($fecha_venta === $fecha_hoy) {
                                        $ventas_hoy++;
                                    }
                                }
                            ?>
                            <h3><?php echo $ventas_hoy; ?></h3>
                            <p>Ventas de Hoy</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            Ventas realizadas hoy <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <?php
                                $ingreso_hoy = 0;
                                foreach($ventas_datos as $venta) {
                                    $fecha_venta = date('Y-m-d', strtotime($venta['fyh_creacion']));
                                    if($fecha_venta === $fecha_hoy) {
                                        $ingreso_hoy += $venta['total_pagado'];
                                    }
                                }
                            ?>
                            <h3>$<?php echo number_format($ingreso_hoy, 2); ?></h3>
                            <p>Ingresos de Hoy</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            Monto vendido hoy <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <!-- ./col -->
            </div>
            <!-- /.row -->
        </div>
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- Spinner para indicar carga -->
<div class="loading-overlay" id="loading-overlay" style="display: none;">
    <div class="spinner-container">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
        <p class="mt-2 text-white">Procesando, por favor espere...</p>
    </div>
</div>

<?php include ('../layout/mensajes.php'); ?>
<?php include ('../layout/parte2.php'); ?>

<style>
/* Estilos personalizados */
.table th {
    background-color: #f4f6f9;
    font-weight: 600;
}

.btn-group .btn {
    margin-right: 3px;
}

.badge-success {
    font-size: 100%;
    padding: 0.5em 0.75em;
}

.badge-info {
    font-size: 100%;
    padding: 0.5em 0.75em;
}

.card-primary.card-outline {
    border-top: 3px solid #2c61a3;
}

.btn-primary {
    background-color: #2c61a3;
    border-color: #2c61a3;
}

.btn-primary:hover {
    background-color: #1d4580;
    border-color: #1d4580;
}

/* Estilos para los modales de descripción */
.modal-header {
    border-radius: 3px 3px 0 0;
}

.modal-descripcion-texto {
    max-height: 300px;
    overflow-y: auto;
}

/* Efectos de hover para filas de tabla */
#example1 tbody tr:hover {
    background-color: #f1f7fd !important;
}

/* Mejoras para la pantalla de resumen */
.small-box {
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.small-box .icon {
    right: 15px;
    top: 15px;
    font-size: 70px;
}

/* Estilos para el spinner de carga */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
}

.spinner-container {
    text-align: center;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Estilos para las tablas de productos con IVA */
.badge-primary {
    background-color: #2c61a3;
}

/* Estilo para las filas de totales */
tfoot th {
    background-color: #f8f9fa;
    font-weight: bold;
}
</style>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay ventas registradas",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ ventas",
                "infoEmpty": "Mostrando 0 a 0 de 0 ventas",
                "infoFiltered": "(Filtrado de _MAX_ total ventas)",
                "lengthMenu": "Mostrar _MENU_ ventas",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true,
            "ordering": true,
            "order": [[0, "desc"]],
            buttons: [{
                extend: 'collection',
                text: 'Reportes',
                orientation: 'landscape',
                buttons: [{
                    text: 'Copiar',
                    extend: 'copy',
                }, {
                    extend: 'pdf'
                },{
                    extend: 'csv'
                },{
                    extend: 'excel'
                },{
                    text: 'Imprimir',
                    extend: 'print'
                }]
            },
            {
                extend: 'colvis',
                text: 'Visor de columnas',
                collectionLayout: 'fixed three-column'
            }],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
    
    // Función para eliminar venta directamente desde ventas/index.php mediante AJAX
    function eliminarVenta(idVenta, nroVenta) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¿Realmente desea eliminar la venta #" + nroVenta + "? Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar el spinner de carga
                $('#loading-overlay').show();
                
                // Solicitud AJAX para eliminar la venta
                $.ajax({
                    url: '../app/controllers/ventas/borrar_venta_ajax.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_venta: idVenta,
                        nro_venta: nroVenta
                    },
                    success: function(response) {
                        // Ocultar el spinner de carga
                        $('#loading-overlay').hide();
                        
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Venta eliminada',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Recargar la página para actualizar la lista de ventas
                                location.reload();
                            });
                        } else {
                            // Mostrar mensaje de error
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'No se pudo eliminar la venta'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Ocultar el spinner de carga
                        $('#loading-overlay').hide();
                        
                        // Mostrar mensaje de error
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo procesar su solicitud. Por favor, inténtelo de nuevo.'
                        });
                        console.error('Error en la solicitud AJAX:', error);
                    }
                });
            }
        });
    }
</script>