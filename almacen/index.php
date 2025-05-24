<?php
include ('../app/config.php');
include ('../app/utils/funciones_globales.php'); // Para sanear()
// CategoriaModel debe estar disponible para poblar el select en los modales
include ('../app/models/CategoriaModel.php'); 

include ('../layout/sesion.php'); // Verifica sesión
include ('../layout/parte1.php'); // Cabecera HTML, CSS, y menú

// Controlador para listar productos y categorías para los modales
include ('../app/controllers/almacen/listado_de_productos.php'); // Define $productos_datos y $categorias_select_datos

$modulo_abierto = 'almacen'; // Para el menú lateral
$pagina_activa = 'almacen_listado'; // Para resaltar en el menú
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Mis Productos en Almacén
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-create-producto">
                           <i class="fa fa-plus"></i> Registrar Nuevo Producto
                        </button>
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PARA CREAR PRODUCTO -->
    <div class="modal fade" id="modal-create-producto" tabindex="-1" role="dialog" aria-labelledby="modalCreateProductoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCreateProductoLabel">Registrar Nuevo Producto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="form-create-producto" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label for="nombre_create">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre_create" name="nombre" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="id_categoria_create">Categoría <span class="text-danger">*</span></label>
                                <select class="form-control" id="id_categoria_create" name="id_categoria" required>
                                    <option value="">Seleccione...</option>
                                    <?php if(!empty($categorias_select_datos)): foreach ($categorias_select_datos as $cat): ?>
                                        <option value="<?php echo $cat['id_categoria']; ?>"><?php echo sanear($cat['nombre_categoria']); ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descripcion_create">Descripción</label>
                            <textarea class="form-control" id="descripcion_create" name="descripcion" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group"><label for="stock_create">Stock <span class="text-danger">*</span></label><input type="number" class="form-control" id="stock_create" name="stock" required min="0"></div>
                            <div class="col-md-4 form-group"><label for="stock_minimo_create">Stock Mínimo</label><input type="number" class="form-control" id="stock_minimo_create" name="stock_minimo" min="0" value="0"></div>
                            <div class="col-md-4 form-group"><label for="stock_maximo_create">Stock Máximo</label><input type="number" class="form-control" id="stock_maximo_create" name="stock_maximo" min="0" value="0"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group"><label for="precio_compra_create">P. Compra <span class="text-danger">*</span></label><input type="number" class="form-control" id="precio_compra_create" name="precio_compra" step="0.01" required min="0.01"></div>
                            <div class="col-md-4 form-group"><label for="precio_venta_create">P. Venta <span class="text-danger">*</span></label><input type="number" class="form-control" id="precio_venta_create" name="precio_venta" step="0.01" required min="0.01"></div>
                            <div class="col-md-4 form-group"><label for="fecha_ingreso_create">F. Ingreso <span class="text-danger">*</span></label><input type="date" class="form-control" id="fecha_ingreso_create" name="fecha_ingreso" required value="<?php echo date('Y-m-d'); ?>"></div>
                        </div>
                        <div class="form-group">
                            <label for="imagen_producto_create">Imagen</label>
                            <input type="file" class="form-control-file" id="imagen_producto_create" name="imagen_producto" accept="image/*">
                            <img id="preview_imagen_create" src="#" alt="Vista previa" class="mt-2 img-thumbnail" style="max-height: 100px; display: none;"/>
                        </div>
                        <div id="error_message_create" class="alert alert-danger" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA ACTUALIZAR PRODUCTO -->
    <div class="modal fade" id="modal-update-producto" tabindex="-1" role="dialog" aria-labelledby="modalUpdateProductoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalUpdateProductoLabel">Actualizar Producto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="form-update-producto" enctype="multipart/form-data">
                    <input type="hidden" id="id_producto_update" name="id_producto_update">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3 form-group"><label>Código:</label><input type="text" class="form-control" id="codigo_update_display" disabled></div>
                            <div class="col-md-5 form-group"><label for="nombre_update">Nombre <span class="text-danger">*</span></label><input type="text" class="form-control" id="nombre_update" name="nombre_update" required></div>
                            <div class="col-md-4 form-group">
                                <label for="id_categoria_update">Categoría <span class="text-danger">*</span></label>
                                <select class="form-control" id="id_categoria_update" name="id_categoria_update" required>
                                     <?php if(!empty($categorias_select_datos)): foreach ($categorias_select_datos as $cat): ?>
                                        <option value="<?php echo $cat['id_categoria']; ?>"><?php echo sanear($cat['nombre_categoria']); ?></option>
                                    <?php endforeach; endif;?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group"><label for="descripcion_update">Descripción</label><textarea class="form-control" id="descripcion_update" name="descripcion_update" rows="2"></textarea></div>
                        <div class="row">
                            <div class="col-md-4 form-group"><label for="stock_update">Stock <span class="text-danger">*</span></label><input type="number" class="form-control" id="stock_update" name="stock_update" required min="0"></div>
                            <div class="col-md-4 form-group"><label for="stock_minimo_update">Stock Mínimo</label><input type="number" class="form-control" id="stock_minimo_update" name="stock_minimo_update" min="0"></div>
                            <div class="col-md-4 form-group"><label for="stock_maximo_update">Stock Máximo</label><input type="number" class="form-control" id="stock_maximo_update" name="stock_maximo_update" min="0"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group"><label for="precio_compra_update">P. Compra <span class="text-danger">*</span></label><input type="number" class="form-control" id="precio_compra_update" name="precio_compra_update" step="0.01" required min="0.01"></div>
                            <div class="col-md-4 form-group"><label for="precio_venta_update">P. Venta <span class="text-danger">*</span></label><input type="number" class="form-control" id="precio_venta_update" name="precio_venta_update" step="0.01" required min="0.01"></div>
                            <div class="col-md-4 form-group"><label for="fecha_ingreso_update">F. Ingreso <span class="text-danger">*</span></label><input type="date" class="form-control" id="fecha_ingreso_update" name="fecha_ingreso_update" required></div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label for="imagen_producto_update">Cambiar Imagen (opcional)</label>
                                <input type="file" class="form-control-file" id="imagen_producto_update" name="imagen_producto_update" accept="image/*">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Imagen Actual:</label>
                                <img id="preview_imagen_update" src="#" alt="Imagen actual" class="img-thumbnail" style="max-height: 100px;"/>
                            </div>
                        </div>
                        <div id="error_message_update" class="alert alert-danger" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Actualizar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA VER PRODUCTO -->
    <div class="modal fade" id="modal-show-producto" tabindex="-1" role="dialog" aria-labelledby="modalShowProductoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="show_product_name_title_modal">Detalles del Producto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <dl class="row">
                                <dt class="col-sm-4">Código:</dt><dd class="col-sm-8" id="show_codigo"></dd>
                                <dt class="col-sm-4">Categoría:</dt><dd class="col-sm-8" id="show_categoria"></dd>
                                <dt class="col-sm-4">Nombre:</dt><dd class="col-sm-8" id="show_nombre_prod"></dd>
                                <dt class="col-sm-4">Descripción:</dt><dd class="col-sm-8" id="show_descripcion_prod"></dd>
                                <dt class="col-sm-4">Stock:</dt><dd class="col-sm-8"><span id="show_stock"></span> (Mín: <span id="show_stock_minimo"></span>, Máx: <span id="show_stock_maximo"></span>)</dd>
                                <dt class="col-sm-4">P. Compra:</dt><dd class="col-sm-8" id="show_precio_compra"></dd>
                                <dt class="col-sm-4">P. Venta:</dt><dd class="col-sm-8" id="show_precio_venta"></dd>
                                <dt class="col-sm-4">F. Ingreso:</dt><dd class="col-sm-8" id="show_fecha_ingreso"></dd>
                                <dt class="col-sm-4">F. Creación:</dt><dd class="col-sm-8" id="show_fyh_creacion"></dd>
                                <dt class="col-sm-4">Últ. Act.:</dt><dd class="col-sm-8" id="show_fyh_actualizacion"></dd>
                            </dl>
                        </div>
                        <div class="col-md-4 text-center">
                            <img id="show_imagen_prod" src="#" alt="Imagen del producto" class="img-fluid img-thumbnail" style="max-height: 200px; margin-top:10px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- MODAL PARA ELIMINAR PRODUCTO -->
    <div class="modal fade" id="modal-delete-producto" tabindex="-1" role="dialog" aria-labelledby="modalDeleteProductoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalDeleteProductoLabel">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_producto_delete">
                    <p>¿Está seguro de eliminar el producto: <strong id="nombre_producto_delete_display"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btn_delete_confirm_producto">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header"><h3 class="card-title">Listado de Productos</h3></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabla_productos" class="table table-bordered table-striped table-sm">
                                    <thead>
                                    <tr>
                                        <th><center>Nro</center></th>
                                        <th><center>Código</center></th>
                                        <th><center>Categoría</center></th>
                                        <th><center>Imagen</center></th>
                                        <th><center>Nombre</center></th>
                                        <th><center>Stock</center></th>
                                        <th><center>P. Venta</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $contador_prod = 0;
                                    if (!empty($productos_datos)) {
                                        foreach ($productos_datos as $item){
                                            $id_producto_loop = $item['id_producto']; // Renombrar para evitar conflicto con $id_producto
                                            $stock_actual = $item['stock'];
                                            $stock_minimo = $item['stock_minimo'] ?? 0;
                                            $stock_maximo = $item['stock_maximo'] ?? 0; // Si es 0 o null, no se considera para advertencia de exceso

                                            $stock_display_class = '';
                                            if ($stock_actual < $stock_minimo) {
                                                $stock_display_class = 'text-danger font-weight-bold';
                                            } elseif ($stock_maximo > 0 && $stock_actual > $stock_maximo) {
                                                $stock_display_class = 'text-warning font-weight-bold';
                                            }
                                            ?>
                                            <tr id="fila_producto_<?php echo $id_producto_loop; ?>">
                                                <td><center><?php echo ++$contador_prod; ?></center></td>
                                                <td><?php echo sanear($item['codigo']);?></td>
                                                <td><?php echo sanear($item['categoria']);?></td>
                                                <td>
                                                    <img src="<?php echo $URL."/almacen/img_productos/".sanear($item['imagen'] ? $item['imagen'] : 'default_product.png');?>" 
                                                         width="50px" alt="Img" class="img-thumbnail">
                                                </td>
                                                <td><?php echo sanear($item['nombre']);?></td>
                                                <td class="<?php echo $stock_display_class; ?>"><center><?php echo $item['stock'];?></center></td>
                                                <td><center><?php echo number_format((float)$item['precio_venta'], 2);?></center></td>
                                                <td>
                                                    <center>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-info btn-xs btn-show-producto" data-id="<?php echo $id_producto_loop; ?>" title="Ver Detalles">
                                                                <i class="fa fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-success btn-xs btn-edit-producto" data-id="<?php echo $id_producto_loop; ?>" title="Editar">
                                                                <i class="fa fa-pencil-alt"></i> <!-- Asegúrate que este ícono esté aquí -->
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-xs btn-delete-producto" data-id="<?php echo $id_producto_loop; ?>" data-nombre="<?php echo sanear($item['nombre']); ?>" title="Eliminar">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </center>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="8"><center>No tienes productos registrados.</center></td></tr>';
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->

<?php include ('../layout/mensajes.php'); ?>
<?php include ('../layout/parte2.php'); ?>

<script>
$(document).ready(function () {
    var tablaProductos = $("#tabla_productos").DataTable({
        "pageLength": 5, // Mostrar 5 por defecto, puedes cambiarlo
        "language": { /* ... tu config de idioma ... */ 
            "emptyTable": "No hay productos registrados",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ productos",
            "infoEmpty": "Mostrando 0 a 0 de 0 productos",
            "infoFiltered": "(Filtrado de _MAX_ total productos)",
            "lengthMenu": "Mostrar _MENU_ productos",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron resultados",
            "paginate": {"first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior"}
        },
        "responsive": true, "lengthChange": true, "autoWidth": false,
        "buttons": [
            { extend: 'copy', text: 'Copiar', exportOptions: { columns: [0,1,2,4,5,6] } }, // Ajusta columnas según necesidad
            { extend: 'excel', text: 'Excel', exportOptions: { columns: [0,1,2,4,5,6] } },
            { extend: 'pdf', text: 'PDF', exportOptions: { columns: [0,1,2,4,5,6] }, orientation: 'landscape' },
            { extend: 'print', text: 'Imprimir', exportOptions: { columns: [0,1,2,4,5,6] } },
            { extend: 'colvis', text: 'Columnas Visibles' }
        ]
    }).buttons().container().appendTo('#tabla_productos_wrapper .col-md-6:eq(0)');

    function mostrarAlerta(title, text, icon, callback) { // Añadimos un callback opcional
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            timer: icon === 'success' ? 2500 : 4000, // Aumentamos timer para éxito a 2.5s, otros a 4s
            showConfirmButton: icon !== 'success', // Muestra botón OK para errores/advertencias
            allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
            allowEscapeKey: false // Evita que se cierre con la tecla Esc
        }).then((result) => { // Usamos .then() para ejecutar el callback después de que la alerta se cierre (automáticamente o por el usuario)
            if (callback && typeof callback === 'function') {
                callback();
            }
        });
    }

    function recargarTablaProductos() { 
        // Idealmente, si DataTables está configurado para carga AJAX:
        // tablaProductos.ajax.reload(null, false); 
        // Como fallback simple, recargamos la página:
        location.reload(); 
    }

    // --- Lógica para CREAR Producto ---
    $('#imagen_producto_create').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) { $('#preview_imagen_create').attr('src', e.target.result).show(); }
            reader.readAsDataURL(file);
        } else { $('#preview_imagen_create').hide(); }
    });

    $('#modal-create-producto').on('hidden.bs.modal', function () {
        $('#form-create-producto')[0].reset();
        $('#preview_imagen_create').hide().attr('src', '#');
        $('#error_message_create').hide().text('');
        $('#fecha_ingreso_create').val('<?php echo date('Y-m-d'); ?>'); // Resetear fecha
    });

    $('#form-create-producto').submit(function (e) {
            e.preventDefault();
            $('#error_message_create').hide();
            var formData = new FormData(this);

            $.ajax({
            url: "../app/controllers/almacen/create_producto.php",
            type: "POST", data: formData, contentType: false, processData: false, dataType: "json",
            success: function(response) {
                if (response.status === 'success') {
                    $('#modal-create-producto').modal('hide');
                    // Mostrar alerta y LUEGO recargar
                    mostrarAlerta('¡Éxito!', response.message, 'success', function() {
                        recargarTablaProductos();
                    });
                } else {
                    // Para errores en el modal, no recargamos, solo mostramos el error en el modal.
                    $('#error_message_create').text(response.message || 'Error desconocido.').show();
                    // Opcionalmente, un SweetAlert también para errores de modal si se prefiere:
                    // mostrarAlerta('Error', response.message || 'No se pudo crear.', response.status || 'error');
                }
                if (response.redirectTo) { // Si la sesión expira
                     mostrarAlerta('Sesión Expirada', response.message, 'warning', function() {
                        window.location.href = response.redirectTo;
                    });
                }
            },
            error: function() {
                 // Error de conexión, mostrar en el modal o con SweetAlert
                $('#error_message_create').text('Error de conexión con el servidor.').show();
                // mostrarAlerta('Error de Conexión', 'No se pudo contactar al servidor.', 'error');
            }
        });
    });

    // --- Lógica para MOSTRAR y EDITAR Producto ---
    function popularModalShow(producto) {
        $('#show_product_name_title_modal').text("Detalles: " + (producto.nombre || 'N/A'));
        $('#show_codigo').text(producto.codigo || 'N/A');
        $('#show_categoria').text(producto.categoria || 'N/A');
        $('#show_nombre_prod').text(producto.nombre || 'N/A');
        $('#show_descripcion_prod').text(producto.descripcion || 'N/A');
        $('#show_stock').text(producto.stock || '0');
        $('#show_stock_minimo').text(producto.stock_minimo || '0');
        $('#show_stock_maximo').text(producto.stock_maximo || '0');
        $('#show_precio_compra').text(parseFloat(producto.precio_compra || 0).toFixed(2));
        $('#show_precio_venta').text(parseFloat(producto.precio_venta || 0).toFixed(2));
        $('#show_fecha_ingreso').text(producto.fecha_ingreso ? new Date(producto.fecha_ingreso + 'T00:00:00Z').toLocaleDateString() : 'N/A'); // Asegurar UTC para fecha
        $('#show_fyh_creacion').text(producto.fyh_creacion ? new Date(producto.fyh_creacion).toLocaleString() : 'N/A');
        $('#show_fyh_actualizacion').text(producto.fyh_actualizacion && producto.fyh_actualizacion !== '0000-00-00 00:00:00' ? new Date(producto.fyh_actualizacion).toLocaleString() : 'N/A');
        $('#show_imagen_prod').attr('src', producto.imagen_url || '<?php echo $URL . "/almacen/img_productos/default_product.png"; ?>');
        $('#modal-show-producto').modal('show');
    }
    
    function popularModalUpdate(producto) {
        $('#id_producto_update').val(producto.id_producto);
        $('#codigo_update_display').val(producto.codigo);
        $('#nombre_update').val(producto.nombre);
        $('#id_categoria_update').val(producto.id_categoria);
        $('#descripcion_update').val(producto.descripcion);
        $('#stock_update').val(producto.stock);
        $('#stock_minimo_update').val(producto.stock_minimo);
        $('#stock_maximo_update').val(producto.stock_maximo);
        $('#precio_compra_update').val(producto.precio_compra);
        $('#precio_venta_update').val(producto.precio_venta);
        $('#fecha_ingreso_update').val(producto.fecha_ingreso); // Formato YYYY-MM-DD
        // $('#imagen_actual_hidden').val(producto.imagen); // No es necesario si el backend lo maneja
        $('#preview_imagen_update').attr('src', producto.imagen_url || '<?php echo $URL . "/almacen/img_productos/default_product.png"; ?>');
        $('#imagen_producto_update').val(''); // Limpiar input file
        $('#error_message_update').hide();
        $('#modal-update-producto').modal('show');
    }

    $('#tabla_productos tbody').on('click', '.btn-show-producto, .btn-edit-producto', function () {
        var id_producto = $(this).data('id'); // Verifica que 'data-id' esté en los botones HTML
        var esParaEditar = $(this).hasClass('btn-edit-producto');

        // Verifica la URL y el parámetro
        $.get("../app/controllers/almacen/get_producto.php", { id_producto: id_producto }, function(response) {
            // El tercer parámetro "json" en $.get es importante para que jQuery parsee automáticamente la respuesta
            if (response.status === 'success' && response.data) {
                if(esParaEditar) {
                    popularModalUpdate(response.data);
                } else {
                    popularModalShow(response.data);
                }
            } else {
                mostrarAlerta('Error al Cargar', response.message || 'No se pudo cargar la información del producto.', 'error');
                if (response.redirectTo) { // Si la sesión expiró
                    window.location.href = response.redirectTo;
                }
            }
        }, "json").fail(function(jqXHR, textStatus, errorThrown) { // Manejo de error de la petición AJAX
            console.error("Error en AJAX a get_producto.php:", textStatus, errorThrown);
            console.error("Respuesta del servidor:", jqXHR.responseText); // Muestra lo que devolvió el servidor
            mostrarAlerta('Error de Conexión', 'No se pudo obtener datos del producto. Revise la consola para más detalles.', 'error');
        });
    });
    
    $('#modal-update-producto').on('hidden.bs.modal', function () {
        $('#form-update-producto')[0].reset();
        $('#preview_imagen_update').attr('src', '#');
        $('#error_message_update').hide().text('');
    });

    $('#form-update-producto').submit(function (e) {
        e.preventDefault();
        $('#error_message_update').hide();
        var formData = new FormData(this);
        // formData.append('id_producto', $('#id_producto_update').val()); // Ya está en el form como hidden

        $.ajax({
            url: "../app/controllers/almacen/update_producto.php",
            type: "POST", data: formData, contentType: false, processData: false, dataType: "json",
            success: function(response) {
                if (response.status === 'success') {
                    $('#modal-update-producto').modal('hide');
                    // Mostrar alerta y LUEGO recargar
                    mostrarAlerta('¡Éxito!', response.message, 'success', function() {
                        recargarTablaProductos();
                    });
                } else {
                     // Para errores en el modal, no recargamos, solo mostramos el error en el modal.
                    $('#error_message_update').text(response.message || 'Error desconocido.').show();
                     // Opcionalmente, un SweetAlert también para errores de modal si se prefiere:
                    // mostrarAlerta('Error', response.message || 'No se pudo actualizar.', response.status || 'error');
                }
                 if (response.redirectTo) { // Si la sesión expira
                     mostrarAlerta('Sesión Expirada', response.message, 'warning', function() {
                        window.location.href = response.redirectTo;
                    });
                }
            },
            error: function() {
                // Error de conexión, mostrar en el modal o con SweetAlert
                $('#error_message_update').text('Error de conexión con el servidor.').show();
                // mostrarAlerta('Error de Conexión', 'No se pudo contactar al servidor.', 'error');
            }
        });
    });

    // --- Lógica para ELIMINAR Producto ---
    $('#tabla_productos tbody').on('click', '.btn-delete-producto', function () {
        $('#id_producto_delete').val($(this).data('id'));
        $('#nombre_producto_delete_display').text($(this).data('nombre'));
        $('#modal-delete-producto').modal('show');
    });

    $('#btn_delete_confirm_producto').click(function () {
        var id_producto = $('#id_producto_delete').val();
        $.post("../app/controllers/almacen/delete_producto.php", { id_producto: id_producto }, function (response) {
            $('#modal-delete-producto').modal('hide');
            if (response.status === 'success') {
                // Mostrar alerta y LUEGO recargar
                mostrarAlerta('¡Eliminado!', response.message, 'success', function() {
                    recargarTablaProductos();
                });
            } else {
                // Para errores o advertencias (ej. producto en uso)
                mostrarAlerta(response.status === 'warning' ? 'Advertencia' : 'Error', response.message || 'No se pudo eliminar.', response.status || 'error');
                // No recargamos la tabla aquí para que el usuario vea el estado actual si la eliminación falló.
            }
            if (response.redirectTo) { // Si la sesión expira
                 mostrarAlerta('Sesión Expirada', response.message, 'warning', function() {
                    window.location.href = response.redirectTo;
                });
            }
        }, "json").fail(function() {
            $('#modal-delete-producto').modal('hide');
            mostrarAlerta('Error de Conexión', 'No se pudo contactar al servidor.', 'error');
        });
    });
});
</script>