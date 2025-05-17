<?php
include ('../app/config.php');
include ('../layout/sesion.php');
include ('../layout/parte1.php');
include ('../app/controllers/compras/listado_de_compras.php');

// Incluir verificación explícita de usuario
$id_usuario_actual = $_SESSION['id_usuario'];

// Si no existe verificacion_usuario.php, está incluido en listado_de_compras.php
// Esto garantiza que solo se muestren las compras del usuario actual
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">
                        Mis Compras
                        <a href="create.php" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Registrar nueva compra
                        </a>
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
                            <h3 class="card-title">Compras registradas</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body" style="display: block;">
                            <div class="table table-responsive">
                                <table id="example1" class="table table-bordered table-striped table-sm">
                                    <thead>
                                    <tr>
                                        <th><center>Nro</center></th>
                                        <th><center>Nro de compra</center></th>
                                        <th><center>Producto</center></th>
                                        <th><center>Fecha</center></th>
                                        <th><center>Proveedor</center></th>
                                        <th><center>Comprobante</center></th>
                                        <th><center>Precio</center></th>
                                        <th><center>Cantidad</center></th>
                                        <th><center>Total</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $contador = 0;
                                    foreach ($compras_datos as $compras_dato){
                                        $id_compra = $compras_dato['id_compra']; 
                                        $nro_compra = $compras_dato['nro_compra'];
                                        $precio_compra = $compras_dato['precio_compra'];
                                        
                                        $cantidad = $compras_dato['cantidad'];
                                        $total = floatval($precio_compra) * intval($cantidad);
                                        $fecha_formateada = date('d/m/Y', strtotime($compras_dato['fecha_compra']));
                                        ?>
                                        <tr>
                                            <td><center><?php echo $contador = $contador + 1;?></center></td>
                                            <td><center><?php echo $compras_dato['nro_compra'];?></center></td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                                        data-target="#modal-producto<?php echo $id_compra;?>">
                                                    <?php echo $compras_dato['nombre_producto'];?>
                                                </button>
                                                <!-- modal para visualizar datos de los productos -->
                                                <div class="modal fade" id="modal-producto<?php echo $id_compra;?>">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header" style="background-color: #07b0d6;color: white">
                                                                <h4 class="modal-title">Datos del producto</h4>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-9">
                                                                        <div class="row">
                                                                            <div class="col-md-2">
                                                                                <div class="form-group">
                                                                                    <label for="">Código</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['codigo'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <label for="">Nombre del producto</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['nombre_producto'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                    <label for="">Descripción</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['descripcion'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-3">
                                                                                <div class="form-group">
                                                                                    <label for="">Stock</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['stock'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <div class="form-group">
                                                                                    <label for="">Stock mínimo</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['stock_minimo'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <div class="form-group">
                                                                                    <label for="">Stock máximo</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['stock_maximo'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <div class="form-group">
                                                                                    <label for="">Fecha de Ingreso</label>
                                                                                    <input type="text" value="<?php echo date('d/m/Y', strtotime($compras_dato['fecha_ingreso']));?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <label for="">Precio Compra</label>
                                                                                    <input type="text" value="$<?php echo number_format($compras_dato['precio_compra_producto'],2);?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <label for="">Precio Venta</label>
                                                                                    <input type="text" value="$<?php echo number_format($compras_dato['precio_venta_producto'],2);?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <label for="">Categoría</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['nombre_categoria'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="">Imagen del producto</label>
                                                                            <img src="<?php echo $URL."/almacen/img_productos/".$compras_dato['imagen'];?>" width="100%" alt="Imagen del producto">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                            </div>
                                                        </div>
                                                        <!-- /.modal-content -->
                                                    </div>
                                                    <!-- /.modal-dialog -->
                                                </div>
                                                <!-- /.modal -->
                                            </td>
                                            <td><center><?php echo $fecha_formateada;?></center></td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                                        data-target="#modal-proveedor<?php echo $id_compra;?>">
                                                    <?php echo $compras_dato['nombre_proveedor'];?>
                                                </button>

                                                <!-- modal para visualizar datos de los proveedores -->
                                                <div class="modal fade" id="modal-proveedor<?php echo $id_compra;?>">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header" style="background-color: #07b0d6;color: white">
                                                                <h4 class="modal-title">Datos del proveedor</h4>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">Nombre</label>
                                                                            <input type="text" value="<?php echo $compras_dato['nombre_proveedor'];?>" class="form-control" disabled>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">Celular</label>
                                                                            <div class="input-group">
                                                                                <input type="text" value="<?php echo $compras_dato['celular_proveedor'];?>" class="form-control" disabled>
                                                                                <div class="input-group-append">
                                                                                    <a href="https://wa.me/591<?php echo $compras_dato['celular_proveedor'];?>" target="_blank" class="btn btn-success">
                                                                                        <i class="fab fa-whatsapp"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">Teléfono</label>
                                                                            <input type="text" value="<?php echo $compras_dato['telefono_proveedor'];?>" class="form-control" disabled>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">Empresa</label>
                                                                            <input type="text" value="<?php echo $compras_dato['empresa'];?>" class="form-control" disabled>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">Email</label>
                                                                            <div class="input-group">
                                                                                <input type="text" value="<?php echo $compras_dato['email_proveedor'];?>" class="form-control" disabled>
                                                                                <div class="input-group-append">
                                                                                    <a href="mailto:<?php echo $compras_dato['email_proveedor'];?>" class="btn btn-primary">
                                                                                        <i class="far fa-envelope"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">Dirección</label>
                                                                            <input type="text" value="<?php echo $compras_dato['direccion_proveedor'];?>" class="form-control" disabled>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                            </div>
                                                        </div>
                                                        <!-- /.modal-content -->
                                                    </div>
                                                    <!-- /.modal-dialog -->
                                                </div>
                                                <!-- /.modal -->
                                            </td>
                                            <td><center><?php echo $compras_dato['comprobante'];?></center></td>
                                            <td><center>$<?php echo number_format(floatval($precio_compra), 2);?></center></td>
                                            <td><center><?php echo $cantidad;?></center></td>
                                            <td><center><strong>$<?php echo number_format($total, 2);?></strong></center></td>
                                            <td>
                                            <center>
    <div class="btn-group">
        <a href="show.php?id=<?php echo $id_compra; ?>" class="btn btn-info btn-sm">
            <i class="fa fa-eye"></i>
        </a>
        <a href="update.php?id=<?php echo $id_compra; ?>" class="btn btn-success btn-sm">
            <i class="fa fa-pencil-alt"></i>
        </a>
        <!-- Reemplaza el botón de borrar existente por este -->
        <!-- Botón Borrar - Con implementación directa del evento onClick -->
        <!-- Verifica que cada botón Borrar tenga estos atributos correctos -->
        <button type="button" class="btn btn-danger btn-sm boton-eliminar" 
                        data-id="<?php echo $id_compra; ?>" 
                        data-nro="<?php echo $nro_compra; ?>">
                    <i class="fa fa-trash"></i>
                </button>

<script>
    // Asociar el evento al botón específico de esta compra
    document.getElementById("btn_borrar_<?php echo $id_compra; ?>").addEventListener("click", function() {
        eliminarCompra(<?php echo $id_compra; ?>, <?php echo $nro_compra; ?>);
    });
</script>
    </div>
</center>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
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

<style>
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

/* Mejorar apariencia de botones */
.boton-eliminar:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: all 0.2s;
}
</style>

<script>
// Esta función se ejecutará cuando el documento esté listo
$(document).ready(function() {
    // Añadir evento click a todos los botones con clase boton-eliminar
    $('.boton-eliminar').on('click', function() {
        var idCompra = $(this).data('id');
        var nroCompra = $(this).data('nro');
        
        console.log("Botón borrar presionado - ID: " + idCompra + ", NRO: " + nroCompra);
        
        // Confirmar antes de eliminar
        Swal.fire({
            title: '¿Está seguro?',
            text: "¿Realmente desea eliminar la compra #" + nroCompra + "? Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar spinner
                $('#loading-overlay').show();
                
                // Realizar petición AJAX para eliminar
                $.ajax({
                    url: '../app/controllers/compras/borrar_compra_ajax.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_compra: idCompra,
                        nro_compra: nroCompra
                    },
                    success: function(response) {
                        // Ocultar spinner
                        $('#loading-overlay').hide();
                        
                        if (response.success) {
                            // Mostrar mensaje de éxito y recargar página
                            Swal.fire({
                                icon: 'success',
                                title: 'Compra eliminada',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            // Mostrar mensaje de error
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'No se pudo eliminar la compra'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Ocultar spinner
                        $('#loading-overlay').hide();
                        
                        // Mostrar mensaje de error
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo procesar su solicitud. Error: ' + error
                        });
                        console.error('Error AJAX:', error);
                        console.error('Detalles:', xhr.responseText);
                    }
                });
            }
        });
    });
});
</script>

<style>
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

/* Mejorar apariencia de botones */
.boton-eliminar:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: all 0.2s;
}
</style>


<?php include ('../layout/mensajes.php'); ?>
<?php include ('../layout/parte2.php'); ?>

<script>
  <!-- Añade esto dentro de las etiquetas <script> existentes al final del archivo -->
// Función para confirmar y eliminar compras
// Función para eliminar compra directamente desde compras/index.php mediante AJAX
function eliminarCompra(idCompra, nroCompra) {
    console.log("Iniciando proceso de eliminación para compra #" + nroCompra);
    
    // Usar SweetAlert2 para confirmación
    Swal.fire({
        title: '¿Está seguro?',
        text: "¿Realmente desea eliminar la compra #" + nroCompra + "? Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log("Confirmación recibida, procediendo a eliminar");
            
            // Mostrar el spinner de carga
            document.getElementById('loading-overlay').style.display = 'flex';
            
            // Crear un objeto FormData para enviar los datos
            var formData = new FormData();
            formData.append('id_compra', idCompra);
            formData.append('nro_compra', nroCompra);
            
            // Crear y configurar la solicitud fetch
            fetch('../app/controllers/compras/borrar_compra_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Respuesta de red no fue ok');
                }
                return response.json();
            })
            .then(data => {
                // Ocultar el spinner de carga
                document.getElementById('loading-overlay').style.display = 'none';
                
                if (data.success) {
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: 'Compra eliminada',
                        text: data.message || 'Compra eliminada correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Recargar la página para actualizar la lista de compras
                        window.location.reload();
                    });
                } else {
                    // Mostrar mensaje de error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudo eliminar la compra'
                    });
                }
            })
            .catch(error => {
                // Ocultar el spinner de carga
                document.getElementById('loading-overlay').style.display = 'none';
                
                console.error('Error:', error);
                // Mostrar mensaje de error
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo procesar su solicitud. Por favor, inténtelo de nuevo.'
                });
            });
        }
    });
}