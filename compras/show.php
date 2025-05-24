<?php
include '../app/config.php';
include '../layout/sesion.php';
include '../app/models/ComprasModel.php'; // Necesitamos el modelo para obtener los detalles

// Verificar si se proporcionó un ID de compra
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    setMensaje("ID de compra no válido o no proporcionado.", "error");
    redirigir("/compras/"); // Redirigir al listado
    exit();
}
$id_compra_solicitada = (int)$_GET['id'];
$id_usuario_actual = (int)$_SESSION['id_usuario']; // De layout/sesion.php

$compraModel = new CompraModel($pdo);
$compra_detalle_completo = $compraModel->getCompraConDetallesPorId($id_compra_solicitada, $id_usuario_actual);

if (!$compra_detalle_completo) {
    setMensaje("Compra no encontrada o no tienes permiso para verla.", "error");
    redirigir("/compras/");
    exit();
}

include '../layout/parte1.php';
include '../layout/mensajes.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detalle de Compra: <?php echo htmlspecialchars($compra_detalle_completo['codigo_compra_referencia']); ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL;?>/">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo $URL;?>/compras/">Mis Compras</a></li>
                        <li class="breadcrumb-item active">Detalle de Compra</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="invoice p-3 mb-3">
                        <!-- title row -->
                        <div class="row">
                            <div class="col-12">
                                <h4>
                                    <i class="fas fa-file-invoice-dollar"></i> Compra Nro: <?php echo htmlspecialchars($compra_detalle_completo['codigo_compra_referencia']); ?>
                                    <small class="float-right">Fecha Compra: 
                                        <?php 
                                            $fecha_c = DateTime::createFromFormat('Y-m-d', $compra_detalle_completo['fecha_compra']);
                                            echo $fecha_c ? $fecha_c->format('d/m/Y') : 'N/A';
                                        ?>
                                    </small>
                                </h4>
                            </div>
                        </div>
                        <!-- info row -->
                        <div class="row invoice-info">
                            <div class="col-sm-4 invoice-col">
                                <strong>Proveedor:</strong>
                                <address>
                                    <strong><?php echo htmlspecialchars($compra_detalle_completo['nombre_proveedor']); ?></strong><br>
                                    <?php if(!empty($compra_detalle_completo['empresa_proveedor'])): ?>
                                        Empresa: <?php echo htmlspecialchars($compra_detalle_completo['empresa_proveedor']); ?><br>
                                    <?php endif; ?>
                                    <?php if(!empty($compra_detalle_completo['direccion_proveedor'])): ?>
                                        <?php echo htmlspecialchars($compra_detalle_completo['direccion_proveedor']); ?><br>
                                    <?php endif; ?>
                                    <?php if(!empty($compra_detalle_completo['celular_proveedor'])): ?>
                                        Celular: <?php echo htmlspecialchars($compra_detalle_completo['celular_proveedor']); ?><br>
                                    <?php endif; ?>
                                    <?php if(!empty($compra_detalle_completo['telefono_proveedor'])): ?>
                                        Teléfono: <?php echo htmlspecialchars($compra_detalle_completo['telefono_proveedor']); ?><br>
                                    <?php endif; ?>
                                    <?php if(!empty($compra_detalle_completo['email_proveedor'])): ?>
                                        Email: <?php echo htmlspecialchars($compra_detalle_completo['email_proveedor']); ?>
                                    <?php endif; ?>
                                </address>
                            </div>
                            <div class="col-sm-4 invoice-col">
                                <!-- Podría ir información de tu empresa/usuario si es relevante -->
                            </div>
                            <div class="col-sm-4 invoice-col">
                                <b>Referencia de Compra:</b> <?php echo htmlspecialchars($compra_detalle_completo['codigo_compra_referencia']); ?><br>
                                <b>Nro. Secuencial:</b> <?php echo htmlspecialchars($compra_detalle_completo['nro_compra']); ?><br>
                                <?php if(!empty($compra_detalle_completo['comprobante'])): ?>
                                    <b>Comprobante Prov.:</b> <?php echo htmlspecialchars($compra_detalle_completo['comprobante']); ?><br>
                                <?php endif; ?>
                                <b>Registrado por:</b> <?php echo htmlspecialchars($_SESSION['nombres']); // Nombre del usuario que registró ?> <br>
                                <b>Fecha Registro:</b> 
                                <?php 
                                    $fyh_c = DateTime::createFromFormat('Y-m-d H:i:s', $compra_detalle_completo['fyh_creacion']);
                                    echo $fyh_c ? $fyh_c->format('d/m/Y H:i:s') : 'N/A';
                                ?>
                            </div>
                        </div>
                        <!-- /.row -->

                        <!-- Table row -->
                        <div class="row mt-4">
                            <div class="col-12 table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Cód. Prod.</th>
                                        <th>Producto</th>
                                        <th>Cant.</th>
                                        <th>Precio U. ($)</th>
                                        <th>IVA %</th>
                                        <th>Subtotal ($)</th>
                                        <th>Monto IVA ($)</th>
                                        <th>Total Ítem ($)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                    $contador_items = 0;
                                    if (!empty($compra_detalle_completo['detalles'])) {
                                        foreach ($compra_detalle_completo['detalles'] as $item) {
                                            $contador_items++;
                                    ?>
                                        <tr>
                                            <td><?php echo $contador_items; ?></td>
                                            <td><?php echo htmlspecialchars($item['codigo_producto']); ?></td>
                                            <td><?php echo htmlspecialchars($item['nombre_producto']); ?></td>
                                            <td class="text-center"><?php echo htmlspecialchars(number_format((float)$item['cantidad'], 2, '.', ',')); ?></td>
                                            <td class="text-right"><?php echo htmlspecialchars(number_format((float)$item['precio_compra_unitario'], 2, '.', ',')); ?></td>
                                            <td class="text-center"><?php echo htmlspecialchars(number_format((float)$item['porcentaje_iva_item'], 2, '.', ',')); ?>%</td>
                                            <td class="text-right"><?php echo htmlspecialchars(number_format((float)$item['subtotal_item'], 2, '.', ',')); ?></td>
                                            <td class="text-right"><?php echo htmlspecialchars(number_format((float)$item['monto_iva_item'], 2, '.', ',')); ?></td>
                                            <td class="text-right font-weight-bold"><?php echo htmlspecialchars(number_format((float)$item['total_item'], 2, '.', ',')); ?></td>
                                        </tr>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="9" class="text-center">No hay productos detallados en esta compra.</td></tr>';
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.row -->

                        <div class="row mt-4">
                            <!-- accepted payments column -->
                            <div class="col-6">
                                <!-- Podría ir alguna nota o comentario sobre la compra si tuvieras ese campo -->
                            </div>
                            <!-- /.col -->
                            <div class="col-6">
                                <p class="lead">Resumen de la Compra:</p>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th style="width:50%">Subtotal General:</th>
                                            <td class="text-right">$<?php echo htmlspecialchars(number_format((float)$compra_detalle_completo['subtotal_general'], 2, '.', ',')); ?></td>
                                        </tr>
                                        <tr>
                                            <th>IVA General:</th>
                                            <td class="text-right">$<?php echo htmlspecialchars(number_format((float)$compra_detalle_completo['monto_iva_general'], 2, '.', ',')); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total Compra:</th>
                                            <td class="text-right"><strong>$<?php echo htmlspecialchars(number_format((float)$compra_detalle_completo['total_general'], 2, '.', ',')); ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- /.row -->

                        <!-- this row will not appear when printing -->
                        <div class="row no-print mt-3">
                            <div class="col-12">
                                <a href="javascript:window.print()" class="btn btn-default"><i class="fas fa-print"></i> Imprimir</a>
                                <a href="<?php echo $URL; ?>/compras/" class="btn btn-primary float-right" style="margin-right: 5px;">
                                    <i class="fas fa-arrow-left"></i> Volver al Listado
                                </a>
                                <!--
                                <button type="button" class="btn btn-success float-right"><i class="far fa-credit-card"></i> Submit Payment
                                </button>
                                <button type="button" class="btn btn-primary float-right" style="margin-right: 5px;">
                                    <i class="fas fa-download"></i> Generate PDF
                                </button>
                                -->
                            </div>
                        </div>
                    </div><!-- /.invoice -->
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include '../layout/parte2.php'; ?>