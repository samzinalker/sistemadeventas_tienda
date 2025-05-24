<?php
// Incluir configuraciones y sesión primero
include('../app/config.php'); 
include('../layout/sesion.php'); 
include('../layout/permisos.php'); // Asegura que solo administradores accedan

// Incluir el nuevo RolModel para obtener los roles
require_once __DIR__ . '/../app/models/RolModel.php';
$rolModel = new RolModel($pdo);
$roles_disponibles = $rolModel->getAllRoles();

// Título y módulo
$titulo_pagina = 'Creación de nuevo usuario';
$modulo_abierto = 'usuarios';
$pagina_activa = 'usuarios_create';

// Incluir la parte superior del layout
include('../layout/parte1.php'); 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0"><?php echo htmlspecialchars($titulo_pagina); ?></h1>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8"> {/* Ajusta el ancho si es necesario */}
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Llene los datos con cuidado</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            {/* El action apunta al controlador refactorizado */}
                            <form action="<?php echo $URL; ?>/app/controllers/usuarios/create.php" method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nombres">Nombres completos</label>
                                            <input type="text" name="nombres" id="nombres" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" name="email" id="email" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="rol">Rol del usuario</label>
                                            <select name="rol" id="rol" class="form-control" required>
                                                <option value="">Seleccione un rol...</option>
                                                <?php foreach ($roles_disponibles as $rol_dato): ?>
                                                    <option value="<?php echo htmlspecialchars($rol_dato['id_rol']); ?>">
                                                        <?php echo htmlspecialchars($rol_dato['rol']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_user">Contraseña</label>
                                            <input type="password" name="password_user" id="password_user" class="form-control" required>
                                            <small class="form-text text-muted">La contraseña debe tener al menos 6 caracteres.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_repeat">Repetir Contraseña</label>
                                            <input type="password" name="password_repeat" id="password_repeat" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <a href="<?php echo $URL; ?>/usuarios/" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
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
// Incluir mensajes y parte final del layout
include('../layout/mensajes.php'); 
include('../layout/parte2.php'); 
?>