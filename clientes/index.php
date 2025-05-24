<?php
include('../app/config.php');
include('../app/utils/funciones_globales.php');
include('../layout/sesion.php');
include('../app/models/ClienteModel.php');
include('../layout/parte1.php');

$modulo_abierto = 'clientes';
$pagina_activa = 'clientes';

// Obtener provincias de Ecuador
$sql_provincias = "SELECT * FROM tb_provincias_ecuador ORDER BY nombre_provincia ASC";
$query_provincias = $pdo->prepare($sql_provincias);
$query_provincias->execute();
$provincias_ecuador = $query_provincias->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-users"></i> Mis Clientes
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-create-cliente">
                            <i class="fas fa-plus"></i> Nuevo Cliente
                        </button>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL;?>/">Inicio</a></li>
                        <li class="breadcrumb-item active">Clientes</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            
            <!-- Información Legal SRI -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Información Legal Ecuador</h5>
                        <strong>Cliente Genérico:</strong> Permitido para ventas ≤ $200 sin identificación del comprador. 
                        <strong>RUC Opcional:</strong> Solo requerido para facturas > $200 o cuando el cliente lo solicite.
                        <em>Cumplimiento SRI: Ventas informales permitidas según Art. 103 RLORTI.</em>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Listado de Clientes</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-success" id="btn-consumidor-final">
                                    <i class="fas fa-user-plus"></i> Crear "Consumidor Final"
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tabla-clientes" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Documento</th>
                                        <th>Tipo</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Cliente -->
<div class="modal fade" id="modal-create-cliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Nuevo Cliente
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="form-create-cliente">
                <div class="modal-body">
                    
                    <!-- Tipo de Cliente -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Cliente <span class="text-danger">*</span></label>
                                <select class="form-control" id="tipo_cliente_create" name="tipo_cliente" required>
                                    <option value="">Seleccione...</option>
                                    <option value="consumidor_final">Consumidor Final (Genérico)</option>
                                    <option value="cedula">Persona con Cédula</option>
                                    <option value="ruc">Persona con RUC</option>
                                    <option value="pasaporte">Extranjero con Pasaporte</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Estado</label>
                                <select class="form-control" name="estado">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Datos Básicos -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nombre_cliente" required 
                                       placeholder="Ej: Juan Pérez / CONSUMIDOR FINAL">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label id="label_documento">Documento</label>
                                <input type="text" class="form-control" name="nit_ci_cliente" 
                                       id="documento_create" placeholder="Cédula/RUC/Pasaporte">
                                <small class="form-text text-muted" id="documento_help">
                                    Opcional para consumidor final
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Celular/WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="celular_cliente" required 
                                       placeholder="0999123456">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono Fijo</label>
                                <input type="text" class="form-control" name="telefono_fijo" 
                                       placeholder="042123456">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email_cliente" 
                               placeholder="cliente@ejemplo.com">
                    </div>

                    <!-- Ubicación -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" class="form-control" name="direccion" 
                                       placeholder="Av. Principal 123 y Secundaria">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ciudad</label>
                                <input type="text" class="form-control" name="ciudad" 
                                       placeholder="Guayaquil">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Provincia</label>
                                <select class="form-control" name="provincia">
                                    <option value="">Seleccione...</option>
                                    <?php foreach($provincias_ecuador as $provincia): ?>
                                        <option value="<?php echo sanear($provincia['nombre_provincia']); ?>">
                                            <?php echo sanear($provincia['nombre_provincia']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Nacimiento</label>
                                <input type="date" class="form-control" name="fecha_nacimiento">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="2" 
                                  placeholder="Notas adicionales sobre el cliente..."></textarea>
                    </div>

                    <div id="validation-errors" class="alert alert-danger" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="modal-edit-cliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit"></i> Editar Cliente
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="form-edit-cliente">
                <input type="hidden" name="id_cliente" id="edit_id_cliente">
                <div class="modal-body">
                    <!-- Los mismos campos que en crear, con IDs diferentes -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Cliente <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_tipo_cliente" name="tipo_cliente" required>
                                    <option value="consumidor_final">Consumidor Final (Genérico)</option>
                                    <option value="cedula">Persona con Cédula</option>
                                    <option value="ruc">Persona con RUC</option>
                                    <option value="pasaporte">Extranjero con Pasaporte</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Estado</label>
                                <select class="form-control" name="estado" id="edit_estado">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nombre_cliente" id="edit_nombre" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label id="edit_label_documento">Documento</label>
                                <input type="text" class="form-control" name="nit_ci_cliente" id="edit_documento">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Celular/WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="celular_cliente" id="edit_celular" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono Fijo</label>
                                <input type="text" class="form-control" name="telefono_fijo" id="edit_telefono">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email_cliente" id="edit_email">
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" class="form-control" name="direccion" id="edit_direccion">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ciudad</label>
                                <input type="text" class="form-control" name="ciudad" id="edit_ciudad">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Provincia</label>
                                <select class="form-control" name="provincia" id="edit_provincia">
                                    <option value="">Seleccione...</option>
                                    <?php foreach($provincias_ecuador as $provincia): ?>
                                        <option value="<?php echo sanear($provincia['nombre_provincia']); ?>">
                                            <?php echo sanear($provincia['nombre_provincia']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Nacimiento</label>
                                <input type="date" class="form-control" name="fecha_nacimiento" id="edit_fecha_nacimiento">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea class="form-control" name="observaciones" id="edit_observaciones" rows="2"></textarea>
                    </div>

                    <div id="edit-validation-errors" class="alert alert-danger" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Actualizar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar Cliente -->
<div class="modal fade" id="modal-delete-cliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="delete_id_cliente">
                <p>¿Está seguro de eliminar al cliente: <strong id="delete_nombre_cliente"></strong>?</p>
                <p class="text-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-delete">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<?php include('../layout/mensajes.php'); ?>
<?php include('../layout/parte2.php'); ?>

<script>
$(document).ready(function() {
    let tablaClientes;
    const urlBase = '<?php echo $URL; ?>';

    // Inicializar DataTable
    function inicializarTabla() {
        tablaClientes = $('#tabla-clientes').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '../app/controllers/clientes/controller_buscar_clientes_dt.php',
                type: 'POST',
                error: function(xhr, error, thrown) {
                    console.error('Error DataTable:', error);
                    Swal.fire('Error', 'Error al cargar los datos', 'error');
                }
            },
            columns: [
                { data: null, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: 'nombre_cliente' },
                { 
                    data: 'nit_ci_cliente',
                    render: function(data, type, row) {
                        if (data === '9999999999999' || data === 'N/A') {
                            return '<span class="badge badge-secondary">Sin documento</span>';
                        }
                        return data;
                    }
                },
                { 
                    data: 'tipo_documento',
                    render: function(data) {
                        const tipos = {
                            'consumidor_final': '<span class="badge badge-primary">Consumidor Final</span>',
                            'cedula': '<span class="badge badge-success">Cédula</span>',
                            'ruc': '<span class="badge badge-warning">RUC</span>',
                            'pasaporte': '<span class="badge badge-info">Pasaporte</span>',
                            'extranjero': '<span class="badge badge-dark">Extranjero</span>'
                        };
                        return tipos[data] || '<span class="badge badge-secondary">N/A</span>';
                    }
                },
                { data: 'celular_cliente' },
                { 
                    data: 'email_cliente',
                    render: function(data) {
                        return data && data !== 'N/A' ? data : '<span class="text-muted">Sin email</span>';
                    }
                },
                { 
                    data: 'estado',
                    render: function(data) {
                        return data === 'activo' 
                            ? '<span class="badge badge-success">Activo</span>'
                            : '<span class="badge badge-danger">Inactivo</span>';
                    }
                },
                { 
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info btn-edit" data-id="${row.id_cliente}" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-delete" data-id="${row.id_cliente}" 
                                        data-nombre="${row.nombre_cliente}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                url: '../public/templeates/AdminLTE-3.2.0/plugins/datatables-plugins/i18n/es_es.json'
            },
            responsive: true,
            pageLength: 10,
            order: [[1, 'asc']], // Ordenar por nombre
            buttons: [
                { extend: 'copy', text: 'Copiar', exportOptions: { columns: [0,1,2,3,4,5,6] } },
                { extend: 'excel', text: 'Excel', exportOptions: { columns: [0,1,2,3,4,5,6] } },
                { extend: 'pdf', text: 'PDF', exportOptions: { columns: [0,1,2,3,4,5,6] } },
                { extend: 'print', text: 'Imprimir', exportOptions: { columns: [0,1,2,3,4,5,6] } }
            ]
        }).buttons().container().appendTo('#tabla-clientes_wrapper .col-md-6:eq(0)');
    }

    // Funciones de validación
    function validarDocumento(documento, tipo) {
        documento = documento.replace(/[^0-9]/g, ''); // Solo números
        
        switch(tipo) {
            case 'cedula':
                return validarCedula(documento);
            case 'ruc':
                return validarRuc(documento);
            case 'pasaporte':
            case 'extranjero':
                return { valido: true, mensaje: 'Documento aceptado' };
            case 'consumidor_final':
                return { valido: true, mensaje: 'Sin validación requerida' };
            default:
                return { valido: false, mensaje: 'Tipo de documento no válido' };
        }
    }

    function validarCedula(cedula) {
        if (cedula.length !== 10) {
            return { valido: false, mensaje: 'La cédula debe tener 10 dígitos' };
        }

        const provincia = parseInt(cedula.substring(0, 2));
        if (provincia < 1 || provincia > 24) {
            return { valido: false, mensaje: 'Código de provincia inválido' };
        }

        const tercerDigito = parseInt(cedula[2]);
        if (tercerDigito > 5) {
            return { valido: false, mensaje: 'Tercer dígito de cédula inválido' };
        }

        // Algoritmo verificador
        const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        let suma = 0;
        
        for (let i = 0; i < 9; i++) {
            let producto = parseInt(cedula[i]) * coeficientes[i];
            if (producto >= 10) producto -= 9;
            suma += producto;
        }

        const digitoVerificador = suma % 10 === 0 ? 0 : 10 - (suma % 10);
        
        if (digitoVerificador !== parseInt(cedula[9])) {
            return { valido: false, mensaje: 'Dígito verificador incorrecto' };
        }

        return { valido: true, mensaje: 'Cédula válida' };
    }

    function validarRuc(ruc) {
        if (ruc.length !== 13) {
            return { valido: false, mensaje: 'El RUC debe tener 13 dígitos' };
        }

        const provincia = parseInt(ruc.substring(0, 2));
        if (provincia < 1 || provincia > 24) {
            return { valido: false, mensaje: 'Código de provincia en RUC inválido' };
        }

        const tercerDigito = parseInt(ruc[2]);
        
        if (tercerDigito <= 5) {
            // RUC persona natural
            const cedula = ruc.substring(0, 10);
            const validacionCedula = validarCedula(cedula);
            if (!validacionCedula.valido) {
                return { valido: false, mensaje: 'RUC con cédula base inválida' };
            }
            
            const establecimiento = ruc.substring(10, 13);
            if (establecimiento !== '001') {
                return { valido: false, mensaje: 'Código de establecimiento RUC inválido' };
            }
        } else if (tercerDigito === 6 || tercerDigito === 9) {
            // RUC jurídica o pública - validación básica
        } else {
            return { valido: false, mensaje: 'Tipo de RUC no reconocido' };
        }

        return { valido: true, mensaje: 'RUC válido' };
    }

    // Eventos de cambio de tipo de cliente
    function manejarCambioTipoCliente(selector, isEdit = false) {
        $(selector).on('change', function() {
            const tipo = $(this).val();
            const documentoInput = isEdit ? '#edit_documento' : '#documento_create';
            const documentoLabel = isEdit ? '#edit_label_documento' : '#label_documento';
            const documentoHelp = isEdit ? '' : '#documento_help';
            
            switch(tipo) {
                case 'consumidor_final':
                    $(documentoLabel).text('Documento (No requerido)');
                    $(documentoInput).attr('placeholder', 'Opcional para consumidor final');
                    $(documentoInput).attr('required', false);
                    if (documentoHelp) $(documentoHelp).text('Opcional para consumidor final');
                    break;
                case 'cedula':
                    $(documentoLabel).text('Cédula de Identidad *');
                    $(documentoInput).attr('placeholder', '1234567890');
                    $(documentoInput).attr('required', true);
                    if (documentoHelp) $(documentoHelp).text('10 dígitos obligatorios');
                    break;
                case 'ruc':
                    $(documentoLabel).text('RUC *');
                    $(documentoInput).attr('placeholder', '1234567890001');
                    $(documentoInput).attr('required', true);
                    if (documentoHelp) $(documentoHelp).text('13 dígitos obligatorios');
                    break;
                case 'pasaporte':
                    $(documentoLabel).text('Pasaporte *');
                    $(documentoInput).attr('placeholder', 'A12345678');
                    $(documentoInput).attr('required', true);
                    if (documentoHelp) $(documentoHelp).text('Formato de pasaporte');
                    break;
                case 'extranjero':
                    $(documentoLabel).text('Documento Extranjero');
                    $(documentoInput).attr('placeholder', 'Documento del país de origen');
                    $(documentoInput).attr('required', false);
                    if (documentoHelp) $(documentoHelp).text('Opcional');
                    break;
            }
        });
    }

    // Crear cliente "Consumidor Final" automático
    $('#btn-consumidor-final').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando...');
        
        $.post('../app/controllers/clientes/crear_consumidor_final.php')
            .done(function(response) {
                if (response.status === 'success') {
                    Swal.fire('¡Éxito!', response.message, 'success');
                    tablaClientes.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            })
            .fail(function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            })
            .always(function() {
                btn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> Crear "Consumidor Final"');
            });
    });

    // Crear cliente
    $('#form-create-cliente').on('submit', function(e) {
        e.preventDefault();
        $('#validation-errors').hide();
        
        const formData = new FormData(this);
        const documento = formData.get('nit_ci_cliente');
        const tipo = formData.get('tipo_cliente');
        
        // Validar documento si se proporciona
        if (documento && documento.trim() !== '') {
            const validacion = validarDocumento(documento, tipo);
            if (!validacion.valido) {
                $('#validation-errors').html(validacion.mensaje).show();
                return;
            }
        }

        $.ajax({
            url: '../app/controllers/clientes/create_cliente.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#modal-create-cliente').modal('hide');
                    Swal.fire('¡Éxito!', response.message, 'success');
                    tablaClientes.ajax.reload(null, false);
                } else {
                    $('#validation-errors').html(response.message).show();
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
    });

    // Editar cliente
    $(document).on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        
        $.get('../app/controllers/clientes/get_cliente.php', { id_cliente: id })
            .done(function(response) {
                if (response.status === 'success') {
                    const cliente = response.data;
                    
                    $('#edit_id_cliente').val(cliente.id_cliente);
                    $('#edit_nombre').val(cliente.nombre_cliente);
                    $('#edit_documento').val(cliente.nit_ci_cliente);
                    $('#edit_tipo_cliente').val(cliente.tipo_documento).trigger('change');
                    $('#edit_celular').val(cliente.celular_cliente);
                    $('#edit_telefono').val(cliente.telefono_fijo);
                    $('#edit_email').val(cliente.email_cliente);
                    $('#edit_direccion').val(cliente.direccion);
                    $('#edit_ciudad').val(cliente.ciudad);
                    $('#edit_provincia').val(cliente.provincia);
                    $('#edit_fecha_nacimiento').val(cliente.fecha_nacimiento);
                    $('#edit_observaciones').val(cliente.observaciones);
                    $('#edit_estado').val(cliente.estado);
                    
                    $('#modal-edit-cliente').modal('show');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            })
            .fail(function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            });
    });

    $('#form-edit-cliente').on('submit', function(e) {
        e.preventDefault();
        $('#edit-validation-errors').hide();
        
        const formData = new FormData(this);
        const documento = formData.get('nit_ci_cliente');
        const tipo = formData.get('tipo_cliente');
        
        // Validar documento si se proporciona
        if (documento && documento.trim() !== '' && documento !== '9999999999999') {
            const validacion = validarDocumento(documento, tipo);
            if (!validacion.valido) {
                $('#edit-validation-errors').html(validacion.mensaje).show();
                return;
            }
        }

        $.ajax({
            url: '../app/controllers/clientes/update_cliente.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#modal-edit-cliente').modal('hide');
                    Swal.fire('¡Éxito!', response.message, 'success');
                    tablaClientes.ajax.reload(null, false);
                } else {
                    $('#edit-validation-errors').html(response.message).show();
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
    });

    // Eliminar cliente
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        
        $('#delete_id_cliente').val(id);
        $('#delete_nombre_cliente').text(nombre);
        $('#modal-delete-cliente').modal('show');
    });

    $('#btn-confirmar-delete').on('click', function() {
        const id = $('#delete_id_cliente').val();
        
        $.post('../app/controllers/clientes/delete_cliente.php', { id_cliente: id })
            .done(function(response) {
                $('#modal-delete-cliente').modal('hide');
                if (response.status === 'success') {
                    Swal.fire('¡Eliminado!', response.message, 'success');
                    tablaClientes.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            })
            .fail(function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            });
    });

    // Limpiar modales al cerrar
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form')[0]?.reset();
        $(this).find('.alert').hide();
    });

    // Inicializar
    inicializarTabla();
    manejarCambioTipoCliente('#tipo_cliente_create', false);
    manejarCambioTipoCliente('#edit_tipo_cliente', true);
});
</script>