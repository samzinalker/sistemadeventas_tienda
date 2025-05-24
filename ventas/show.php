<?php
include '../app/config.php'; // $URL, $pdo, $fechaHora
include '../layout/sesion.php'; // Verifica sesión, establece $id_usuario_sesion, etc.
include '../layout/parte1.php'; // Cabecera HTML, CSS, jQuery, y menú lateral

// Incluir el modelo de Ventas
require_once __DIR__ . '/../app/models/VentasModel.php';

// Verificar si se proporcionó un ID de venta
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    // Si no hay ID o no es un entero, redirigir o mostrar error
    $_SESSION['mensaje'] = "Error: ID de venta no válido.";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/ventas/');
    exit;
}

$id_venta_get = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$venta_info = null;
$detalles_venta = [];

if ($id_usuario_sesion) { // Asegurarse de que el ID de usuario de sesión esté disponible
    try {
        $ventasModel = new VentasModel($pdo, $id_usuario_sesion);
        $venta_info = $ventasModel->getVentaById($id_venta_get);
        
        if ($venta_info) {
            $detalles_venta = $ventasModel->getDetallesVentaById($id_venta_get);
        } else {
            $_SESSION['mensaje'] = "Venta no encontrada o no tiene permiso para verla.";
            $_SESSION['icono'] = "warning";
            // Podrías redirigir a la lista de ventas si la venta no se encuentra o no pertenece al usuario
            // header('Location: ' . $URL . '/ventas/');
            // exit;
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al cargar datos de la venta: " . $e->getMessage();
        $_SESSION['icono'] = "error";
        // Loggear el error: error_log($e->getMessage());
    }
} else {
    $_SESSION['mensaje'] = "Error: Sesión de usuario no encontrada.";
    $_SESSION['icono'] = "error";
    // header('Location: ' . $URL . '/login.php'); // O a donde corresponda
    // exit;
}

// Para mostrar mensajes flash (SweetAlert)
include '../layout/mensajes.php'; 
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-file-invoice-dollar"></i> Detalle de Venta: 
                        <?php echo htmlspecialchars($venta_info && $venta_info['codigo_venta_referencia'] ? $venta_info['codigo_venta_referencia'] : 'N/A'); ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/ventas/">Ventas</a></li>
                        <li class="breadcrumb-item active">Detalle de Venta</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <?php if ($venta_info): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">Información General de la Venta</h3>
                                <div class="card-tools">
                                     <a href="<?php echo $URL; ?>/ventas/create.php" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Nueva Venta</a>
                                     <a href="<?php echo $URL; ?>/ventas/" class="btn btn-sm btn-secondary"><i class="fas fa-list"></i> Listado de Ventas</a>
                                     <!-- Podrías añadir un botón de Imprimir/PDF aquí -->
                                     <button onclick="window.print();" class="btn btn-sm btn-info"><i class="fas fa-print"></i> Imprimir</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row invoice-info">
                                    <div class="col-sm-4 invoice-col">
                                        <strong>Cliente:</strong><br>
                                        <?php echo htmlspecialchars($venta_info['nombre_cliente']); ?><br>
                                        <?php if (!empty($venta_info['nit_ci_cliente'])): ?>
                                            Documento: <?php echo htmlspecialchars($venta_info['nit_ci_cliente']); ?><br>
                                        <?php endif; ?>
                                        <!-- Aquí podrías añadir más datos del cliente si los trajeras en la consulta -->
                                    </div>
                                    <div class="col-sm-4 invoice-col">
                                        <strong>Vendedor:</strong><br>
                                        <?php echo htmlspecialchars($venta_info['nombre_vendedor']); ?><br>
                                        <br>
                                        <strong>Observaciones:</strong><br>
                                        <?php echo !empty($venta_info['observaciones']) ? nl2br(htmlspecialchars($venta_info['observaciones'])) : 'Ninguna'; ?>
                                    </div>
                                    <div class="col-sm-4 invoice-col">
                                        <b>Nro. Venta: <?php echo htmlspecialchars($venta_info['codigo_venta_referencia']); ?></b><br>
                                        <b>Fecha de Venta:</b> <?php echo date("d/m/Y", strtotime($venta_info['fecha_venta'])); ?><br>
                                        <?php if(!empty($venta_info['tipo_comprobante'])): ?>
                                            <b>Tipo Comprobante:</b> <?php echo htmlspecialchars(strtoupper($venta_info['tipo_comprobante'])); ?><br>
                                        <?php endif; ?>
                                        <?php if(!empty($venta_info['nro_comprobante_fisico'])): ?>
                                            <b>Nro. Comprobante Físico:</b> <?php echo htmlspecialchars($venta_info['nro_comprobante_fisico']); ?><br>
                                        <?php endif; ?>
                                        <b>Estado Venta:</b> <span class="badge badge-<?php 
                                            switch (strtolower($venta_info['estado_venta'])) {
                                                case 'pagada': echo 'success'; break;
                                                case 'pendiente': echo 'warning'; break;
                                                case 'anulada': echo 'danger'; break;
                                                case 'entregada': echo 'info'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>"><?php echo htmlspecialchars(strtoupper($venta_info['estado_venta'])); ?></span><br>
                                    </div>
                                </div>
                                <hr>
                                
                                <h5 class="mt-3">Productos Vendidos</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 10%;">Código</th>
                                                <th>Producto</th>
                                                <th style="width: 10%; text-align: center;">Cantidad</th>
                                                <th style="width: 15%; text-align: right;">P. Venta Unit.</th>
                                                <th style="width: 10%; text-align: center;">% IVA</th>
                                                <th style="width: 15%; text-align: right;">Subtotal Item</th>
                                                <!-- Podrías añadir descuento por item si lo implementas -->
                                                <th style="width: 15%; text-align: right;">Total Item (+IVA)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($detalles_venta)): ?>
                                                <?php foreach ($detalles_venta as $detalle): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($detalle['codigo_producto']); ?></td>
                                                        <td><?php echo htmlspecialchars($detalle['nombre_producto']); ?></td>
                                                        <td style="text-align: center;"><?php echo htmlspecialchars(number_format(floatval($detalle['cantidad']), 2)); ?></td>
                                                        <td style="text-align: right;"><?php echo htmlspecialchars(number_format(floatval($detalle['precio_venta_unitario']), 2)); ?></td>
                                                        <td style="text-align: center;"><?php echo htmlspecialchars(number_format(floatval($detalle['porcentaje_iva_item']), 2)); ?>%</td>
                                                        <td style="text-align: right;"><?php echo htmlspecialchars(number_format(floatval($detalle['subtotal_item']), 2)); ?></td>
                                                        <td style="text-align: right;"><?php echo htmlspecialchars(number_format(floatval($detalle['total_item']), 2)); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No se encontraron detalles para esta venta.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <!-- Espacio para notas adicionales o métodos de pago si se añaden -->
                                    </div>
                                    <div class="col-md-6">
                                        <p class="lead">Resumen Financiero</p>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <tr>
                                                    <th style="width:50%">Subtotal General:</th>
                                                    <td class="text-right">$<?php echo htmlspecialchars(number_format(floatval($venta_info['subtotal_general']), 2)); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>IVA Total (<?php 
                                                        // Calcular un % promedio de IVA si es necesario, o simplemente mostrar el monto.
                                                        // Si todos los items tienen el mismo IVA, se podría mostrar ese %.
                                                        // Por ahora, solo el monto.
                                                        // echo ($venta_info['subtotal_general'] > 0) ? number_format(($venta_info['monto_iva_general'] / $venta_info['subtotal_general']) * 100, 2) : '0.00'; echo "%";
                                                    ?>):</th>
                                                    <td class="text-right">$<?php echo htmlspecialchars(number_format(floatval($venta_info['monto_iva_general']), 2)); ?></td>
                                                </tr>
                                                <?php if (floatval($venta_info['descuento_general']) > 0): ?>
                                                <tr>
                                                    <th>Descuento General:</th>
                                                    <td class="text-right text-danger">-$<?php echo htmlspecialchars(number_format(floatval($venta_info['descuento_general']), 2)); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                                <tr>
                                                    <th>Total General:</th>
                                                    <td class="text-right font-weight-bold" style="font-size: 1.2rem;">$<?php echo htmlspecialchars(number_format(floatval($venta_info['total_general']), 2)); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div><!-- /.card-body -->
                        </div><!-- /.card -->
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading"><i class="icon fas fa-exclamation-triangle"></i> Venta no encontrada</h4>
                    <p>La venta que está intentando ver no existe, ha sido eliminada o no tiene permisos para acceder a ella.</p>
                    <hr>
                    <p class="mb-0">Por favor, verifique el ID o <a href="<?php echo $URL; ?>/ventas/" class="alert-link">vuelva al listado de ventas</a>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../layout/parte2.php'; ?>
<script>
    // No se necesita JavaScript específico para esta página de visualización básica,
    // a menos que quieras añadir interacciones como modales, etc.
    // La funcionalidad de impresión es básica del navegador.
</script>