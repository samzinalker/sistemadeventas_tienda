<?php
// 1. Incluir configuración, sesión y permisos
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../layout/sesion.php';
require_once __DIR__ . '/../layout/permisos.php'; // Asegura que solo administradores accedan

// 2. Definir título y variables para el layout
$titulo_pagina = 'Creación de Nuevo Rol';
$modulo_abierto = 'roles';
$pagina_activa = 'roles_create'; // Para resaltar en el menú

// RolModel no es necesario aquí, ya que este es solo el formulario.
// El controlador app/controllers/roles/create_controller.php se encargará de la lógica.

// Repoblar formulario si hay datos en sesión por un error previo en el controlador
$form_data = $_SESSION['form_data_rol_create'] ?? [];
unset($_SESSION['form_data_rol_create']); // Limpiar después de usar

// 3. Incluir la parte superior del layout
require_once __DIR__ . '/../layout/parte1.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo htmlspecialchars($titulo_pagina); ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/roles/">Roles</a></li>
                        <li class="breadcrumb-item active">Crear Rol</li>
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
                <div class="col-md-6"> {/* Ajusta el ancho si es necesario */}
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Llene los datos del nuevo rol</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            {/* El action apunta al controlador de creación */}
                            <form action="<?php echo htmlspecialchars($URL . '/app/controllers/roles/create_controller.php'); ?>" method="post">
                                <div class="form-group">
                                    <label for="nombre_rol">Nombre del Rol</label>
                                    <input type="text" name="nombre_rol" id="nombre_rol" class="form-control" 
                                           value="<?php echo htmlspecialchars($form_data['nombre_rol'] ?? ''); ?>" required>
                                    <small class="form-text text-muted">Ej: Vendedor, Editor, Contador, etc.</small>
                                </div>
                                
                                <hr>
                                <div class="form-group text-right">
                                    <a href="<?php echo $URL; ?>/roles/" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Guardar Rol</button>
                                </div>
                            </form>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php
// 4. Incluir mensajes y la parte final del layout
require_once __DIR__ . '/../layout/mensajes.php';
require_once __DIR__ . '/../layout/parte2.php';
?>