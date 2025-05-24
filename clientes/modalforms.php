<!-- Modal para Crear Cliente -->
<div class="modal fade" id="modalCrearCliente" tabindex="-1" aria-labelledby="modalCrearClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCrearClienteLabel"><i class="fas fa-user-plus"></i> Registrar Nuevo Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-white">&times;</span>
                </button>
            </div>
            <form id="formCrearCliente" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="crear_nombre_cliente">Nombre Completo / Razón Social <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="crear_nombre_cliente" name="nombre_cliente" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="crear_tipo_documento">Tipo Documento <span class="text-danger">*</span></label>
                                <select class="form-control" id="crear_tipo_documento" name="tipo_documento" required>
                                    <option value="consumidor_final" selected>CONSUMIDOR FINAL</option>
                                    <option value="cedula">CÉDULA</option>
                                    <option value="ruc">RUC</option>
                                    <option value="pasaporte">PASAPORTE</option>
                                    <option value="extranjero">EXTRANJERO (Sin validación)</option>
                                    <option value="otro">OTRO (Sin validación)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="crear_nit_ci_cliente">Nro. Documento</label>
                                <input type="text" class="form-control" id="crear_nit_ci_cliente" name="nit_ci_cliente" placeholder="Opcional para Consumidor Final">
                                <small id="crear_doc_helper_text" class="form-text text-muted">Para Consumidor Final, usar 9999999999 o dejar vacío.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="crear_celular_cliente">Celular</label>
                                <input type="text" class="form-control" id="crear_celular_cliente" name="celular_cliente" placeholder="0991234567">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="crear_telefono_fijo">Teléfono Fijo</label>
                                <input type="text" class="form-control" id="crear_telefono_fijo" name="telefono_fijo">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="crear_email_cliente">Correo Electrónico</label>
                                <input type="email" class="form-control" id="crear_email_cliente" name="email_cliente" placeholder="cliente@ejemplo.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="crear_fecha_nacimiento">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="crear_fecha_nacimiento" name="fecha_nacimiento">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="crear_direccion">Dirección</label>
                        <textarea class="form-control" id="crear_direccion" name="direccion" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="crear_ciudad">Ciudad</label>
                                <input type="text" class="form-control" id="crear_ciudad" name="ciudad">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="crear_provincia">Provincia</label>
                                <input type="text" class="form-control" id="crear_provincia" name="provincia">
                            </div>
                        </div>
                    </div>
                     <div class="form-group">
                        <label for="crear_observaciones">Observaciones</label>
                        <textarea class="form-control" id="crear_observaciones" name="observaciones" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="crear_estado">Estado</label>
                        <select class="form-control" id="crear_estado" name="estado">
                            <option value="activo" selected>Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div id="crear_cliente_error_messages" class="alert alert-danger" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalEditarClienteLabel"><i class="fas fa-user-edit"></i> Editar Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-white">&times;</span>
                </button>
            </div>
            <form id="formEditarCliente" method="POST">
                <input type="hidden" id="id_cliente_update" name="id_cliente_update">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nombre_cliente_update">Nombre Completo / Razón Social <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre_cliente_update" name="nombre_cliente_update" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tipo_documento_update">Tipo Documento <span class="text-danger">*</span></label>
                                <select class="form-control" id="tipo_documento_update" name="tipo_documento_update" required>
                                     <option value="consumidor_final">CONSUMIDOR FINAL</option>
                                    <option value="cedula">CÉDULA</option>
                                    <option value="ruc">RUC</option>
                                    <option value="pasaporte">PASAPORTE</option>
                                    <option value="extranjero">EXTRANJERO (Sin validación)</option>
                                    <option value="otro">OTRO (Sin validación)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nit_ci_cliente_update">Nro. Documento</label>
                                <input type="text" class="form-control" id="nit_ci_cliente_update" name="nit_ci_cliente_update">
                                <small id="update_doc_helper_text" class="form-text text-muted">Para Consumidor Final, usar 9999999999 o dejar vacío.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="celular_cliente_update">Celular</label>
                                <input type="text" class="form-control" id="celular_cliente_update" name="celular_cliente_update">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="telefono_fijo_update">Teléfono Fijo</label>
                                <input type="text" class="form-control" id="telefono_fijo_update" name="telefono_fijo_update">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email_cliente_update">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email_cliente_update" name="email_cliente_update">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_nacimiento_update">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento_update" name="fecha_nacimiento_update">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="direccion_update">Dirección</label>
                        <textarea class="form-control" id="direccion_update" name="direccion_update" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ciudad_update">Ciudad</label>
                                <input type="text" class="form-control" id="ciudad_update" name="ciudad_update">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="provincia_update">Provincia</label>
                                <input type="text" class="form-control" id="provincia_update" name="provincia_update">
                            </div>
                        </div>
                    </div>
                     <div class="form-group">
                        <label for="observaciones_update">Observaciones</label>
                        <textarea class="form-control" id="observaciones_update" name="observaciones_update" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="estado_update">Estado</label>
                        <select class="form-control" id="estado_update" name="estado_update">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                     <div id="editar_cliente_error_messages" class="alert alert-danger" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Eliminar Cliente (Confirmación) -->
<div class="modal fade" id="modalConfirmarEliminarCliente" tabindex="-1" aria-labelledby="modalConfirmarEliminarClienteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalConfirmarEliminarClienteLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-white">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar al cliente: <strong id="nombre_cliente_a_eliminar"></strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.</p>
                <input type="hidden" id="id_cliente_a_eliminar">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminacionCliente"><i class="fas fa-trash"></i> Eliminar</button>
            </div>
        </div>
    </div>
</div>