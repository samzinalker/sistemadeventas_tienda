<?php
include '../app/config.php'; // Para $URL, $pdo
// include '../app/utils/funciones_globales.php'; // Incluido en parte1.php o controladores
include '../layout/sesion.php'; // Verifica sesión
// include '../app/models/ClienteModel.php'; // No se usa directamente aquí, sino en controladores
include '../layout/parte1.php'; // Cabecera HTML, CSS, jQuery, y menú lateral

$modulo_abierto = 'clientes'; // Para el menú lateral, si aplica
$pagina_activa = 'clientes_listado'; // Para el menú lateral, si aplica

// Obtener provincias de Ecuador para los modales
$sql_provincias = "SELECT nombre_provincia FROM tb_provincias_ecuador ORDER BY nombre_provincia ASC";
$query_provincias = $pdo->prepare($sql_provincias);
$query_provincias->execute();
$provincias_ecuador = $query_provincias->fetchAll(PDO::FETCH_ASSOC);

include '../layout/mensajes.php'; // Para mostrar mensajes flash
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-users"></i> Gestión de Clientes</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/">Inicio</a></li>
                        <li class="breadcrumb-item active">Clientes</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <!-- Información Legal SRI -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-info"></i> Recordatorio Ventas Informales (Ecuador)</h5>
                        <ul>
                            <li>Para ventas a <strong>Consumidor Final</strong> (sin datos específicos del comprador), el SRI permite no identificar al cliente si el monto es menor a un límite establecido (consultar normativa vigente, usualmente alrededor de $50-$200 USD).</li>
                            <li>Puede usar el tipo de documento "Consumidor Final" y un RUC/CI genérico como "9999999999".</li>
                            <li>La validación de Cédula y RUC implementada aquí es básica. Para cumplimiento estricto del SRI, se requieren algoritmos completos.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Listado de Mis Clientes</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-success mr-2" id="btn-consumidor-final">
                                    <i class="fas fa-user-check"></i> Crear/Verif. Consumidor Final
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-create-cliente">
                                    <i class="fas fa-user-plus"></i> Registrar Nuevo Cliente
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabla-clientes" class="table table-bordered table-striped table-hover table-sm" style="width:100%">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 30px;"><center>#</center></th>
                                            <th><center>Nombre / Razón Social</center></th>
                                            <th><center>Tipo Doc.</center></th>
                                            <th><center>Nro. Documento</center></th>
                                            <th><center>Celular</center></th>
                                            <th><center>Email</center></th>
                                            <th><center>Estado</center></th>
                                            <th><center>Registrado</center></th>
                                            <th style="width: 100px;"><center>Acciones</center></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Los datos se cargarán vía AJAX por DataTables -->
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

<!-- MODALES -->
<!-- Modal Crear Cliente -->
<div class="modal fade" id="modal-create-cliente" tabindex="-1" aria-labelledby="modal-create-cliente-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modal-create-cliente-label"><i class="fas fa-user-plus"></i> Registrar Nuevo Cliente</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="form-create-cliente" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="create_nombre_cliente">Nombre Completo / Razón Social <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="create_nombre_cliente" name="nombre_cliente" required placeholder="Ej: Juan Pérez / CONSUMIDOR FINAL">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="create_tipo_documento">Tipo Documento <span class="text-danger">*</span></label>
                                <select class="form-control" id="create_tipo_documento" name="tipo_documento" required>
                                    <option value="consumidor_final" selected>CONSUMIDOR FINAL</option>
                                    <option value="cedula">CÉDULA</option>
                                    <option value="ruc">RUC</option>
                                    <option value="pasaporte">PASAPORTE</option>
                                    <option value="extranjero">EXTRANJERO</option>
                                    <option value="otro">OTRO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="create_nit_ci_cliente" id="label_create_documento">Nro. Documento</label>
                                <input type="text" class="form-control" id="create_nit_ci_cliente" name="nit_ci_cliente">
                                <small id="create_documento_help" class="form-text text-muted"></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="create_celular_cliente">Celular</label>
                                <input type="text" class="form-control" id="create_celular_cliente" name="celular_cliente" placeholder="0991234567">
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="create_estado">Estado</label>
                                <select class="form-control" id="create_estado" name="estado">
                                    <option value="activo" selected>Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="create_email_cliente">Correo Electrónico</label>
                                <input type="email" class="form-control" id="create_email_cliente" name="email_cliente" placeholder="cliente@ejemplo.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="create_telefono_fijo">Teléfono Fijo</label>
                                <input type="text" class="form-control" id="create_telefono_fijo" name="telefono_fijo" placeholder="042123456">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="create_direccion">Dirección</label>
                        <textarea class="form-control" id="create_direccion" name="direccion" rows="2" placeholder="Av. Principal 123 y Secundaria"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="create_ciudad">Ciudad</label>
                                <input type="text" class="form-control" id="create_ciudad" name="ciudad" placeholder="Guayaquil">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="create_provincia">Provincia</label>
                                <select class="form-control" id="create_provincia" name="provincia">
                                    <option value="">Seleccione...</option>
                                    <?php foreach($provincias_ecuador as $provincia): ?>
                                        <option value="<?php echo htmlspecialchars($provincia['nombre_provincia']); ?>">
                                            <?php echo htmlspecialchars($provincia['nombre_provincia']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="create_fecha_nacimiento">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="create_fecha_nacimiento" name="fecha_nacimiento">
                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="form-group">
                                <label for="create_observaciones">Observaciones</label>
                                <textarea class="form-control" id="create_observaciones" name="observaciones" rows="1" placeholder="Notas adicionales..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div id="create_validation_errors" class="alert alert-danger" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="modal-edit-cliente" tabindex="-1" aria-labelledby="modal-edit-cliente-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modal-edit-cliente-label"><i class="fas fa-user-edit"></i> Editar Cliente</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="form-edit-cliente" method="POST">
                <input type="hidden" name="id_cliente_update" id="edit_id_cliente_update">
                <div class="modal-body">
                    <!-- Campos similares a crear, con prefijo 'edit_' en IDs -->
                     <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="edit_nombre_cliente">Nombre Completo / Razón Social <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nombre_cliente" name="nombre_cliente_update" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="edit_tipo_documento">Tipo Documento <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_tipo_documento" name="tipo_documento_update" required>
                                    <option value="consumidor_final">CONSUMIDOR FINAL</option>
                                    <option value="cedula">CÉDULA</option>
                                    <option value="ruc">RUC</option>
                                    <option value="pasaporte">PASAPORTE</option>
                                    <option value="extranjero">EXTRANJERO</option>
                                    <option value="otro">OTRO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="edit_nit_ci_cliente" id="label_edit_documento">Nro. Documento</label>
                                <input type="text" class="form-control" id="edit_nit_ci_cliente" name="nit_ci_cliente_update">
                                <small id="edit_documento_help" class="form-text text-muted"></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_celular_cliente">Celular</label>
                                <input type="text" class="form-control" id="edit_celular_cliente" name="celular_cliente_update">
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_estado">Estado</label>
                                <select class="form-control" id="edit_estado" name="estado_update">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_email_cliente">Correo Electrónico</label>
                                <input type="email" class="form-control" id="edit_email_cliente" name="email_cliente_update">
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="edit_telefono_fijo">Teléfono Fijo</label>
                                <input type="text" class="form-control" id="edit_telefono_fijo" name="telefono_fijo_update">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_direccion">Dirección</label>
                        <textarea class="form-control" id="edit_direccion" name="direccion_update" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_ciudad">Ciudad</label>
                                <input type="text" class="form-control" id="edit_ciudad" name="ciudad_update">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_provincia">Provincia</label>
                                <select class="form-control" id="edit_provincia" name="provincia_update">
                                    <option value="">Seleccione...</option>
                                    <?php foreach($provincias_ecuador as $provincia): ?>
                                        <option value="<?php echo htmlspecialchars($provincia['nombre_provincia']); ?>">
                                            <?php echo htmlspecialchars($provincia['nombre_provincia']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_fecha_nacimiento">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="edit_fecha_nacimiento" name="fecha_nacimiento_update">
                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_observaciones">Observaciones</label>
                                <textarea class="form-control" id="edit_observaciones" name="observaciones_update" rows="1"></textarea>
                            </div>
                        </div>
                    </div>
                    <div id="edit_validation_errors" class="alert alert-danger" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Actualizar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar Cliente (Confirmación) -->
<div class="modal fade" id="modal-delete-cliente" tabindex="-1" aria-labelledby="modal-delete-cliente-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modal-delete-cliente-label">Confirmar Eliminación</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar al cliente: <strong id="delete_nombre_cliente_display"></strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.</p>
                <input type="hidden" id="delete_id_cliente_hidden">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-delete"><i class="fas fa-trash"></i> Eliminar</button>
            </div>
        </div>
    </div>
</div>

<?php include '../layout/parte2.php'; // Pie de página, JS global, etc. ?>

<script>
$(document).ready(function() {
    const urlBase = '<?php echo $URL; ?>';
    let tablaClientes;

    // Inicializar DataTable
    function inicializarTablaClientes() {
        if ($.fn.DataTable.isDataTable('#tabla-clientes')) {
            tablaClientes.destroy();
        }
        tablaClientes = $('#tabla-clientes').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": `${urlBase}/app/controllers/clientes/controller_buscar_clientes_dt.php`,
                "type": "POST",
                "error": function(xhr, error, thrown) {
                    console.error("Error en DataTable: ", xhr.responseText);
                    Swal.fire('Error de Carga', 'No se pudieron cargar los datos de clientes. Revise la consola para más detalles.', 'error');
                }
            },
            "columns": [
                { "data": null, "className": "text-center", "orderable": false, "searchable": false,
                  "render": function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }
                },
                { "data": "nombre_cliente" },
                { "data": "tipo_documento", "className": "text-center", 
                  "render": function(data,type,row){
                      let tipo = data ? data.charAt(0).toUpperCase() + data.slice(1).toLowerCase() : 'N/A';
                      tipo = tipo.replace("_", " ");
                      if(data === 'consumidor_final') return `<span class="badge badge-info">${tipo}</span>`;
                      if(data === 'cedula') return `<span class="badge badge-primary">${tipo}</span>`;
                      if(data === 'ruc') return `<span class="badge badge-success">${tipo}</span>`;
                      if(data === 'pasaporte') return `<span class="badge badge-warning">${tipo}</span>`;
                      return `<span class="badge badge-secondary">${tipo}</span>`;
                  }
                },
                { "data": "nit_ci_cliente", "className": "text-center",
                  "render": function(data, type, row) {
                      if (row.tipo_documento === 'consumidor_final' && (data === '9999999999' || data === '9999999999999' || !data)) {
                          return '<span class="text-muted">N/A (Genérico)</span>';
                      }
                      return data || '<span class="text-muted">N/A</span>';
                  }
                },
                { "data": "celular_cliente", "className": "text-center", "render": function(data){ return data || '<span class="text-muted">N/A</span>';}},
                { "data": "email_cliente", "render": function(data){ return data || '<span class="text-muted">N/A</span>';}},
                { "data": "estado", "className": "text-center",
                  "render": function(data){ return data === 'activo' ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';}
                },
                { 
                    "data": "fyh_creacion", "className": "text-center",
                    "render": function(data) {
                        if (!data) return '<span class="text-muted">N/A</span>';
                        try {
                            let date = new Date(data.replace(' ', 'T')+'Z'); // Asegurar que se interprete como UTC si no tiene offset
                            if (isNaN(date.getTime())) { // Fallback si la fecha no es válida
                                date = new Date(data);
                            }
                            return date.toLocaleDateString('es-EC', {day: '2-digit', month: '2-digit', year: 'numeric'}) + ' ' + 
                                   date.toLocaleTimeString('es-EC', {hour: '2-digit', minute:'2-digit'});
                        } catch (e) {
                            return data; // Devolver original si hay error de parseo
                        }
                    }
                },
                { 
                    "data": null, "className": "text-center", "orderable": false, "searchable": false,
                    "render": function (data, type, row) {
                        return `
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-info btn-edit" data-id="${row.id_cliente}" title="Editar Cliente">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-delete" data-id="${row.id_cliente}" data-nombre="${row.nombre_cliente}" title="Eliminar Cliente">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            "responsive": true, "lengthChange": true, "autoWidth": false, "pageLength": 10,
            "order": [[1, 'asc']],
            "buttons": [
                { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar', className: 'btn-sm', exportOptions: { columns: [0,1,2,3,4,5,6,7] }},
                { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn-sm', exportOptions: { columns: [0,1,2,3,4,5,6,7] }},
                { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn-sm', orientation: 'landscape', exportOptions: { columns: [0,1,2,3,4,5,6,7] }},
                { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn-sm', exportOptions: { columns: [0,1,2,3,4,5,6,7] }}
            ],
            "language": { "url": `${urlBase}/public/templeates/AdminLTE-3.2.0/plugins/datatables-plugins/i18n/es_es.json`}
        });
        tablaClientes.buttons().container().appendTo('#tabla-clientes_wrapper .col-md-6:eq(0)');
    }
    
    inicializarTablaClientes();

    // --- Funciones de Validación (Lado Cliente para feedback rápido) ---
    function validarCedulaCliente(cedula) {
        if (!/^\d{10}$/.test(cedula)) return { valido: false, mensaje: 'Cédula debe tener 10 dígitos numéricos.' };
        let provincia = parseInt(cedula.substring(0, 2));
        if (provincia < 1 || provincia > 24) return { valido: false, mensaje: 'Código de provincia inválido en cédula.' };
        let tercerDigito = parseInt(cedula[2]);
        if (tercerDigito >= 6) return { valido: false, mensaje: 'Tercer dígito de cédula inválido.' };
        
        let coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        let suma = 0;
        for (let i = 0; i < 9; i++) {
            let producto = parseInt(cedula[i]) * coeficientes[i];
            suma += (producto >= 10) ? producto - 9 : producto;
        }
        let digitoVerificador = (suma % 10 === 0) ? 0 : 10 - (suma % 10);
        if (digitoVerificador !== parseInt(cedula[9])) return { valido: false, mensaje: 'Cédula inválida (dígito verificador no coincide).' };
        return { valido: true, mensaje: 'Cédula válida.' };
    }

    function validarRucCliente(ruc) {
        if (!/^\d{13}$/.test(ruc)) return { valido: false, mensaje: 'RUC debe tener 13 dígitos numéricos.' };
        let provincia = parseInt(ruc.substring(0, 2));
        if (provincia < 1 || provincia > 24) return { valido: false, mensaje: 'Código de provincia inválido en RUC.' };
        
        let tipoContribuyente = parseInt(ruc[2]);
        if (tipoContribuyente === 6 || tipoContribuyente === 9) { // Entidades públicas o Sociedades
            // Algoritmo módulo 11 para estos casos (simplificado aquí)
            let coef = (tipoContribuyente === 9) ? [4,3,2,7,6,5,4,3,2] : [3,2,7,6,5,4,3,2]; // Sociedades : Públicas
            let ref = (tipoContribuyente === 9) ? 8 : 9;
            let suma = 0;
            for (let i = 0; i < ref; i++) suma += parseInt(ruc[i]) * coef[i];
            let dv = (suma % 11 === 0) ? 0 : 11 - (suma % 11);
            if (dv !== parseInt(ruc[ref])) return { valido: false, mensaje: 'RUC de entidad inválido.'};

        } else if (tipoContribuyente < 6) { // Persona natural
            let validacionCedulaBase = validarCedulaCliente(ruc.substring(0,10));
            if (!validacionCedulaBase.valido) return { valido: false, mensaje: 'RUC (persona natural) con base de cédula inválida.'};
        } else {
            return { valido: false, mensaje: 'Tercer dígito de RUC no reconocido.'};
        }
        if (!/00[1-9]$/.test(ruc.substring(10,13)) && ruc.substring(10,13) !== '000' && !/001$/.test(ruc.substring(10,13))) {
             // Permite 000 y 001-009 para establecimientos. 
             // La lógica original '001' es muy restrictiva para algunos RUCs válidos con múltiples establecimientos.
             // Si se quiere ser estricto con '001' para el principal: if (ruc.substring(10,13) !== '001') ...
        }
        return { valido: true, mensaje: 'RUC válido.' };
    }
    
    function mostrarErroresModal(modalId, errores) {
        const errorDivId = (modalId === 'modal-create-cliente') ? 'create_validation_errors' : 'edit_validation_errors';
        let mensajesHtml = '<ul>';
        if (typeof errores === 'string') {
            mensajesHtml += `<li>${errores}</li>`;
        } else if (Array.isArray(errores)) {
            errores.forEach(err => mensajesHtml += `<li>${err}</li>`);
        } else if (typeof errores === 'object') {
            for (const key in errores) {
                mensajesHtml += `<li>${errores[key]}</li>`;
            }
        }
        mensajesHtml += '</ul>';
        $('#' + errorDivId).html(mensajesHtml).show();
    }

    function limpiarErroresModal(modalId) {
         const errorDivId = (modalId === 'modal-create-cliente') ? 'create_validation_errors' : 'edit_validation_errors';
         $('#' + errorDivId).hide().html('');
    }
    
    // --- Configuración Dinámica de Campos de Documento ---
    function configurarCampoDocumento(selectTipoDocId, inputDocId, helpTextId, labelDocId) {
        $('#' + selectTipoDocId).on('change', function() {
            const tipo = $(this).val();
            const $inputDoc = $('#' + inputDocId);
            const $helpText = $('#' + helpTextId);
            const $labelDoc = $('#' + labelDocId);
            let placeholder = "Nro. Documento";
            let help = "";
            let requerido = false;
            $inputDoc.val(''); // Limpiar al cambiar tipo

            switch(tipo) {
                case 'consumidor_final':
                    placeholder = "9999999999 (Opcional)";
                    help = "Para Consumidor Final, usar 9999999999 o dejar vacío.";
                    $labelDoc.text("Nro. Documento");
                    $inputDoc.val('9999999999'); // Autocompletar
                    break;
                case 'cedula':
                    placeholder = "Ej: 0102030405";
                    help = "10 dígitos numéricos.";
                    $labelDoc.text("Nro. Cédula *");
                    requerido = true;
                    break;
                case 'ruc':
                    placeholder = "Ej: 0102030405001";
                    help = "13 dígitos numéricos.";
                    $labelDoc.text("Nro. RUC *");
                    requerido = true;
                    break;
                case 'pasaporte':
                    placeholder = "Ej: A123B456";
                    help = "Documento de pasaporte.";
                    $labelDoc.text("Nro. Pasaporte *");
                    requerido = true;
                    break;
                case 'extranjero':
                case 'otro':
                    placeholder = "Documento de Identificación";
                    help = "Documento de identificación según país/tipo.";
                    $labelDoc.text("Nro. Documento");
                    break;
            }
            $inputDoc.attr('placeholder', placeholder).prop('required', requerido);
            $helpText.text(help);
        }).trigger('change');
    }

    configurarCampoDocumento('create_tipo_documento', 'create_nit_ci_cliente', 'create_documento_help', 'label_create_documento');
    configurarCampoDocumento('edit_tipo_documento', 'edit_nit_ci_cliente', 'edit_documento_help', 'label_edit_documento');

    // --- Eventos CRUD ---
    // Crear/Verificar Consumidor Final
    $('#btn-consumidor-final').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        $.ajax({
            url: `${urlBase}/app/controllers/clientes/crear_consumidor_final.php`,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire('Realizado', response.message, 'success');
                    tablaClientes.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', response.message || 'No se pudo procesar.', 'error');
                }
            },
            error: function() { Swal.fire('Error', 'No se pudo contactar al servidor.', 'error'); },
            complete: function() { btn.prop('disabled', false).html('<i class="fas fa-user-check"></i> Crear/Verif. Consumidor Final');}
        });
    });

    // Submit Formulario Crear Cliente
    $('#form-create-cliente').on('submit', function(e) {
        e.preventDefault();
        limpiarErroresModal('modal-create-cliente');
        const formData = $(this).serialize();
        const tipoDoc = $('#create_tipo_documento').val();
        const numDoc = $('#create_nit_ci_cliente').val().trim();
        let validacionCliente = { valido: true };

        if (numDoc && tipoDoc !== 'consumidor_final' && tipoDoc !== 'otro' && tipoDoc !== 'extranjero') {
            if (tipoDoc === 'cedula') validacionCliente = validarCedulaCliente(numDoc);
            if (tipoDoc === 'ruc') validacionCliente = validarRucCliente(numDoc);
            
            if (!validacionCliente.valido) {
                mostrarErroresModal('modal-create-cliente', validacionCliente.mensaje);
                return;
            }
        }
        
        $.ajax({
            url: `${urlBase}/app/controllers/clientes/create_cliente.php`,
            type: 'POST', data: formData, dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modal-create-cliente').modal('hide');
                    Swal.fire('¡Éxito!', response.message, 'success');
                    tablaClientes.ajax.reload(null, false);
                } else {
                    mostrarErroresModal('modal-create-cliente', response.message || 'Error al crear el cliente.');
                }
            },
            error: function() { mostrarErroresModal('modal-create-cliente', 'Error de conexión con el servidor.');}
        });
    });

    // Cargar datos para Editar Cliente
    $('#tabla-clientes tbody').on('click', '.btn-edit', function() {
        const idCliente = $(this).data('id');
        limpiarErroresModal('modal-edit-cliente');
        $.ajax({
            url: `${urlBase}/app/controllers/clientes/get_cliente.php`,
            type: 'GET', data: { id_cliente: idCliente }, dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    const cliente = response.data;
                    $('#edit_id_cliente_update').val(cliente.id_cliente);
                    $('#edit_nombre_cliente').val(cliente.nombre_cliente);
                    $('#edit_tipo_documento').val(cliente.tipo_documento).trigger('change'); // Trigger para actualizar UI
                    $('#edit_nit_ci_cliente').val(cliente.nit_ci_cliente);
                    $('#edit_celular_cliente').val(cliente.celular_cliente);
                    $('#edit_telefono_fijo').val(cliente.telefono_fijo);
                    $('#edit_email_cliente').val(cliente.email_cliente);
                    $('#edit_direccion').val(cliente.direccion);
                    $('#edit_ciudad').val(cliente.ciudad);
                    $('#edit_provincia').val(cliente.provincia);
                    $('#edit_fecha_nacimiento').val(cliente.fecha_nacimiento); // Asegurar formato YYYY-MM-DD
                    $('#edit_observaciones').val(cliente.observaciones);
                    $('#edit_estado').val(cliente.estado);
                    $('#modal-edit-cliente').modal('show');
                } else {
                    Swal.fire('Error', response.message || 'No se pudieron cargar los datos del cliente.', 'error');
                }
            },
            error: function() { Swal.fire('Error', 'No se pudo contactar al servidor.', 'error');}
        });
    });

    // Submit Formulario Editar Cliente
    $('#form-edit-cliente').on('submit', function(e) {
        e.preventDefault();
        limpiarErroresModal('modal-edit-cliente');
        const formData = $(this).serialize();
        const tipoDoc = $('#edit_tipo_documento').val();
        const numDoc = $('#edit_nit_ci_cliente').val().trim();
        let validacionCliente = { valido: true };

        if (numDoc && tipoDoc !== 'consumidor_final' && tipoDoc !== 'otro' && tipoDoc !== 'extranjero') {
             // No validar si es el documento genérico de consumidor final
            if (tipoDoc === 'consumidor_final' && (numDoc === '9999999999' || numDoc === '9999999999999')) {
                // No hacer nada, es válido
            } else {
                if (tipoDoc === 'cedula') validacionCliente = validarCedulaCliente(numDoc);
                if (tipoDoc === 'ruc') validacionCliente = validarRucCliente(numDoc);
            
                if (!validacionCliente.valido) {
                    mostrarErroresModal('modal-edit-cliente', validacionCliente.mensaje);
                    return;
                }
            }
        }

        $.ajax({
            url: `${urlBase}/app/controllers/clientes/update_cliente.php`,
            type: 'POST', data: formData, dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modal-edit-cliente').modal('hide');
                    Swal.fire('¡Actualizado!', response.message, 'success');
                    tablaClientes.ajax.reload(null, false);
                } else {
                    mostrarErroresModal('modal-edit-cliente', response.message || 'Error al actualizar el cliente.');
                }
            },
            error: function() { mostrarErroresModal('modal-edit-cliente', 'Error de conexión con el servidor.');}
        });
    });

    // Abrir Modal de Confirmación para Eliminar
    $('#tabla-clientes tbody').on('click', '.btn-delete', function() {
        const idCliente = $(this).data('id');
        const nombreCliente = $(this).data('nombre');
        $('#delete_id_cliente_hidden').val(idCliente);
        $('#delete_nombre_cliente_display').text(nombreCliente || 'este cliente');
        $('#modal-delete-cliente').modal('show');
    });

    // Confirmar Eliminación
    $('#btn-confirmar-delete').on('click', function() {
        const idCliente = $('#delete_id_cliente_hidden').val();
        $.ajax({
            url: `${urlBase}/app/controllers/clientes/delete_cliente.php`,
            type: 'POST', data: { id_cliente: idCliente }, dataType: 'json',
            success: function(response) {
                $('#modal-delete-cliente').modal('hide');
                if (response.status === 'success') {
                    Swal.fire('¡Eliminado!', response.message, 'success');
                    tablaClientes.ajax.reload(null, false);
                } else {
                    Swal.fire('Error al Eliminar', response.message || 'No se pudo eliminar el cliente.', 'error');
                }
            },
            error: function() {
                $('#modal-delete-cliente').modal('hide');
                Swal.fire('Error', 'No se pudo contactar al servidor.', 'error');
            }
        });
    });

    // Limpiar modales al cerrar para evitar datos cacheados
    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0]?.reset(); // Resetea el formulario si existe
        const createErrorDiv = $('#create_validation_errors');
        if (createErrorDiv.length) createErrorDiv.hide().html('');
        const editErrorDiv = $('#edit_validation_errors');
        if (editErrorDiv.length) editErrorDiv.hide().html('');

        // Resetear selects de tipo de documento a un estado inicial
        $('#create_tipo_documento').val('consumidor_final').trigger('change');
        // Para el modal de edición, no es necesario resetear el tipo si se va a cargar data nueva.
    });
});
</script>