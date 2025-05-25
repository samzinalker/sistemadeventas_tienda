<?php
require_once '../config.php';
require_once '../utils/funciones_globales.php';
include('../layout/sesion.php');
include('../layout/parte1.php');
include('../app/controllers/compras/listado_de_compras.php');
require_once '../models/ComprasModel.php';
include('../app/models/ProveedorModel.php');
include('../app/models/AlmacenModel.php');

$modulo_abierto = 'compras';
$pagina_activa = 'compras_edit';

// Validar que se envió un ID
if (!isset($_GET['id'])) {
    setMensaje("ID de compra no proporcionado.", "error");
    redirigir("/compras/");
    exit();
}

$id_compra = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id_compra) {
    setMensaje("ID de compra no válido.", "error");
    redirigir("/compras/");
    exit();
}

// Obtener datos de la compra
try {
    $compraModel = new CompraModel($pdo);
    $compra_datos = $compraModel->getCompraConDetallesPorId($id_compra, $_SESSION['id_usuario']);
    
    if (!$compra_datos) {
        setMensaje("Compra no encontrada o no tiene permisos para editarla.", "error");
        redirigir("/compras/");
        exit();
    }
    
    // Obtener lista de proveedores para el select
    $proveedorModel = new ProveedorModel($pdo, $URL);
    $proveedores_datos = $proveedorModel->getProveedoresByUsuarioId($_SESSION['id_usuario']);
    
    // Obtener lista de productos para el select
    $almacenModel = new AlmacenModel($pdo);
    $productos_datos = $almacenModel->getProductosByUsuarioId($_SESSION['id_usuario']);
    
} catch (Exception $e) {
    error_log("Error al obtener datos de compra para editar: " . $e->getMessage());
    setMensaje("Error al cargar los datos de la compra.", "error");
    redirigir("/compras/");
    exit();
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Editar Compra #<?php echo sanear($compra_datos['cabecera']['codigo_compra_referencia']); ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/compras">Compras</a></li>
                        <li class="breadcrumb-item active">Editar Compra</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <form action="<?php echo $URL; ?>/app/controllers/compras/update.php" method="post" id="form_edit_compra">
                <input type="hidden" name="id_compra" value="<?php echo $id_compra; ?>">
                
                <div class="row">
                    <!-- Información de la Compra -->
                    <div class="col-md-8">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-edit"></i> Datos de la Compra
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Código de Compra</label>
                                            <input type="text" class="form-control" value="<?php echo sanear($compra_datos['cabecera']['codigo_compra_referencia']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de Compra <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="fecha_compra" 
                                                   value="<?php echo sanear($compra_datos['cabecera']['fecha_compra']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Proveedor <span class="text-danger">*</span></label>
                                            <select class="form-control" name="id_proveedor" required>
                                                <option value="">Seleccione un proveedor</option>
                                                <?php foreach ($proveedores_datos as $proveedor): ?>
                                                    <option value="<?php echo $proveedor['id_proveedor']; ?>"
                                                            <?php echo ($proveedor['id_proveedor'] == $compra_datos['cabecera']['id_proveedor']) ? 'selected' : ''; ?>>
                                                        <?php echo sanear($proveedor['nombre_proveedor']); ?>
                                                        <?php if ($proveedor['empresa']): ?>
                                                            - <?php echo sanear($proveedor['empresa']); ?>
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Comprobante</label>
                                            <input type="text" class="form-control" name="comprobante" 
                                                   value="<?php echo sanear($compra_datos['cabecera']['comprobante'] ?? ''); ?>" 
                                                   placeholder="Número de factura o comprobante">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resumen -->
                    <div class="col-md-4">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-calculator"></i> Resumen de Compra
                                </h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Subtotal:</strong></td>
                                        <td class="text-right">
                                            <span id="subtotal_display">$<?php echo number_format($compra_datos['cabecera']['subtotal_general'], 2); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>IVA:</strong></td>
                                        <td class="text-right">
                                            <span id="iva_display">$<?php echo number_format($compra_datos['cabecera']['monto_iva_general'], 2); ?></span>
                                        </td>
                                    </tr>
                                    <tr class="border-top">
                                        <td><strong>Total:</strong></td>
                                        <td class="text-right">
                                            <h4><span id="total_display">$<?php echo number_format($compra_datos['cabecera']['total_general'], 2); ?></span></h4>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- Campos ocultos para enviar los totales -->
                                <input type="hidden" id="subtotal_general" name="subtotal_general" value="<?php echo $compra_datos['cabecera']['subtotal_general']; ?>">
                                <input type="hidden" id="monto_iva_general" name="monto_iva_general" value="<?php echo $compra_datos['cabecera']['monto_iva_general']; ?>">
                                <input type="hidden" id="total_general" name="total_general" value="<?php echo $compra_datos['cabecera']['total_general']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Productos de la Compra -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-box"></i> Productos de la Compra
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-primary btn-sm" id="btn_agregar_producto">
                                        <i class="fas fa-plus"></i> Agregar Producto
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="tabla_productos_compra">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th width="100">Cantidad</th>
                                                <th width="120">Precio Unitario</th>
                                                <th width="80">IVA (%)</th>
                                                <th width="120">Subtotal</th>
                                                <th width="100">IVA</th>
                                                <th width="120">Total</th>
                                                <th width="80">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_productos">
                                            <?php foreach ($compra_datos['detalles'] as $index => $detalle): ?>
                                            <tr data-row="<?php echo $index; ?>">
                                                <td>
                                                    <select class="form-control producto-select" name="item_id_producto[]" required>
                                                        <option value="">Seleccione producto</option>
                                                        <?php foreach ($productos_datos as $producto): ?>
                                                            <option value="<?php echo $producto['id_producto']; ?>"
                                                                    data-precio="<?php echo $producto['precio_compra']; ?>"
                                                                    <?php echo ($producto['id_producto'] == $detalle['id_producto']) ? 'selected' : ''; ?>>
                                                                <?php echo sanear($producto['codigo'] . ' - ' . $producto['nombre']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control cantidad-input" 
                                                           name="item_cantidad[]" step="0.01" min="0.01" 
                                                           value="<?php echo $detalle['cantidad']; ?>" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control precio-input" 
                                                           name="item_precio_unitario[]" step="0.01" min="0" 
                                                           value="<?php echo $detalle['precio_compra_unitario']; ?>" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control iva-input" 
                                                           name="item_porcentaje_iva[]" step="0.01" min="0" max="100" 
                                                           value="<?php echo $detalle['porcentaje_iva_item']; ?>" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control subtotal-item" 
                                                           value="<?php echo $detalle['subtotal_item']; ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control iva-item" 
                                                           value="<?php echo $detalle['monto_iva_item']; ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control total-item" 
                                                           value="<?php echo $detalle['total_item']; ?>" readonly>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botones de Acción -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <a href="<?php echo $URL; ?>/compras/" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-success" id="btn_actualizar_compra">
                                    <i class="fas fa-save"></i> Actualizar Compra
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Productos disponibles para el JS -->
<script>
const productosDisponibles = <?php echo json_encode($productos_datos); ?>;
</script>

<?php include('../layout/mensajes.php'); ?>
<?php include('../layout/parte2.php'); ?>

<script>
$(document).ready(function() {
    let contadorFilas = <?php echo count($compra_datos['detalles']); ?>;
    
    // Función para calcular totales de una fila
    function calcularTotalFila(row) {
        const cantidad = parseFloat(row.find('.cantidad-input').val()) || 0;
        const precio = parseFloat(row.find('.precio-input').val()) || 0;
        const iva = parseFloat(row.find('.iva-input').val()) || 0;
        
        const subtotal = cantidad * precio;
        const montoIva = subtotal * (iva / 100);
        const total = subtotal + montoIva;
        
        row.find('.subtotal-item').val(subtotal.toFixed(2));
        row.find('.iva-item').val(montoIva.toFixed(2));
        row.find('.total-item').val(total.toFixed(2));
        
        calcularTotalesGenerales();
    }
    
    // Función para calcular totales generales
    function calcularTotalesGenerales() {
        let subtotalGeneral = 0;
        let ivaGeneral = 0;
        
        $('#tbody_productos tr').each(function() {
            const subtotal = parseFloat($(this).find('.subtotal-item').val()) || 0;
            const iva = parseFloat($(this).find('.iva-item').val()) || 0;
            
            subtotalGeneral += subtotal;
            ivaGeneral += iva;
        });
        
        const totalGeneral = subtotalGeneral + ivaGeneral;
        
        $('#subtotal_display').text('$' + subtotalGeneral.toFixed(2));
        $('#iva_display').text('$' + ivaGeneral.toFixed(2));
        $('#total_display').text('$' + totalGeneral.toFixed(2));
        
        $('#subtotal_general').val(subtotalGeneral.toFixed(2));
        $('#monto_iva_general').val(ivaGeneral.toFixed(2));
        $('#total_general').val(totalGeneral.toFixed(2));
    }
    
    // Eventos para recalcular cuando cambien los valores
    $(document).on('input', '.cantidad-input, .precio-input, .iva-input', function() {
        calcularTotalFila($(this).closest('tr'));
    });
    
    // Evento para autocompletar precio al seleccionar producto
    $(document).on('change', '.producto-select', function() {
        const precio = $(this).find('option:selected').data('precio') || 0;
        $(this).closest('tr').find('.precio-input').val(precio).trigger('input');
    });
    
    // Agregar nueva fila de producto
    $('#btn_agregar_producto').click(function() {
        const nuevaFila = `
            <tr data-row="${contadorFilas}">
                <td>
                    <select class="form-control producto-select" name="item_id_producto[]" required>
                        <option value="">Seleccione producto</option>
                        ${productosDisponibles.map(producto => 
                            `<option value="${producto.id_producto}" data-precio="${producto.precio_compra}">
                                ${producto.codigo} - ${producto.nombre}
                            </option>`
                        ).join('')}
                    </select>
                </td>
                <td><input type="number" class="form-control cantidad-input" name="item_cantidad[]" step="0.01" min="0.01" value="1" required></td>
                <td><input type="number" class="form-control precio-input" name="item_precio_unitario[]" step="0.01" min="0" value="0" required></td>
                <td><input type="number" class="form-control iva-input" name="item_porcentaje_iva[]" step="0.01" min="0" max="100" value="0" required></td>
                <td><input type="number" class="form-control subtotal-item" value="0" readonly></td>
                <td><input type="number" class="form-control iva-item" value="0" readonly></td>
                <td><input type="number" class="form-control total-item" value="0" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        
        $('#tbody_productos').append(nuevaFila);
        contadorFilas++;
    });
    
    // Eliminar fila
    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        calcularTotalesGenerales();
    });
    
    // Validación del formulario
    $('#form_edit_compra').submit(function(e) {
        if ($('#tbody_productos tr').length === 0) {
            e.preventDefault();
            Swal.fire('Error', 'Debe agregar al menos un producto a la compra.', 'error');
            return false;
        }
        
        $('#btn_actualizar_compra').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
    });
    
    // Calcular totales iniciales
    calcularTotalesGenerales();
});
</script>