<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de ventas</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="<?php echo $URL;?>/public/templeates/AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo $URL;?>/public/templeates/AdminLTE-3.2.0/dist/css/adminlte.min.css">

    <!-- Libreria Sweetallert2-->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="<?php echo $URL;?>/public/templeates/AdminLTE-3.2.0/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?php echo $URL;?>/public/templeates/AdminLTE-3.2.0/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?php echo $URL;?>/public/templeates/AdminLTE-3.2.0/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

    <!-- jQuery -->
    <script src="<?php echo $URL;?>/public/templeates/AdminLTE-3.2.0/plugins/jquery/jquery.min.js"></script>

</head>
<body class="hold-transition sidebar-mini">

<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link">SISTEMA DE VENTAS </a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="<?php echo $URL;?>" class="brand-link">
            <img src="<?php echo $URL;?>/public/images/logo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">SIS VENTAS</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
<div class="user-panel mt-3 pb-3 mb-3 d-flex">
    <div class="image">
        <?php 
        // Obtener imagen de perfil
        $sql_img = "SELECT imagen_perfil FROM tb_usuarios WHERE id_usuario = :id_usuario";
        $query_img = $pdo->prepare($sql_img);
        $query_img->bindParam(':id_usuario', $_SESSION['id_usuario']);
        $query_img->execute();
        $result_img = $query_img->fetch(PDO::FETCH_ASSOC);
        
        $img_perfil = isset($result_img['imagen_perfil']) ? $result_img['imagen_perfil'] : 'user_default.png';
        $ruta_img = $URL.'/public/images/perfiles/'.$img_perfil;
        ?>
        <img src="<?php echo $ruta_img; ?>" class="img-circle elevation-2" alt="User Image">
    </div>
    <div class="info">
        <a href="<?php echo $URL;?>/perfil" class="d-block"><?php echo $nombres_sesion;?></a>
    </div>
</div>


            <nav class="mt-2">
<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

    <?php $isAdmin = ($_SESSION['rol'] === 'administrador'); ?>

    <?php if ($isAdmin): ?>
    <li class="nav-item has-treeview <?php if($modulo_abierto=='usuarios') echo 'menu-open'; ?>">
        <a href="#" class="nav-link active <?php if($modulo_abierto=='usuarios') echo 'active'; ?>">
            <i class="nav-icon fas fa-users"></i>
            <p>
                Usuarios
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="<?php echo $URL;?>/usuarios" class="nav-link <?php if($pagina_activa=='usuarios') echo 'active'; ?>">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Listado de usuarios</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo $URL;?>/usuarios/create.php" class="nav-link <?php if($pagina_activa=='usuarios_create') echo 'active'; ?>">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Creación de usuario</p>
                </a>
            </li>
        </ul>
    </li>

    




    

    <li class="nav-item has-treeview <?php if($modulo_abierto=='roles') echo 'menu-open'; ?>">
        <a href="#" class="nav-link active <?php if($modulo_abierto=='roles') echo 'active'; ?>">
            <i class="nav-icon fas fa-address-card"></i>
            <p>
                Roles
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="<?php echo $URL;?>/roles" class="nav-link <?php if($pagina_activa=='roles') echo 'active'; ?>">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Listado de roles</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo $URL;?>/roles/create.php" class="nav-link <?php if($pagina_activa=='roles_create') echo 'active'; ?>">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Creación de rol</p>
                </a>
            </li>
        </ul>
    </li>
    <?php endif; ?>



                    <li class="nav-item ">
                        <a href="#" class="nav-link active">
                            <i class="nav-icon fas fa-tags"></i>
                            <p>
                                Categorías
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?php echo $URL;?>/categorias" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listado de categorías</p>
                                </a>
                            </li>
                        </ul>
                    </li>






                    <li class="nav-item ">
                        <a href="#" class="nav-link active">
                            <i class="nav-icon fas fa-list"></i>
                            <p>
                                Almacen
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?php echo $URL;?>/almacen" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listado de productos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo $URL;?>/almacen/create.php" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Creación de productos</p>
                                </a>
                            </li>
                        </ul>
                    </li>




                    
                      <li class="nav-item "> <!-- Puedes añadir 'menu-open' si quieres que esté abierto por defecto -->
                         <a href="#" class="nav-link active"> <!-- Puedes quitar 'active' si no es la página actual -->
                             <i class="nav-icon fas fa-cart-plus"></i>
                             <p>
                                 Compras
                                 <i class="right fas fa-angle-left"></i>
                             </p>
                         </a>
                         <ul class="nav nav-treeview">
                             <li class="nav-item">
                                 <a href="<?php echo $URL;?>/compras/" class="nav-link"> <!-- Asegúrate que el index.php sea el default o añade /index.php -->
                                     <i class="far fa-circle nav-icon"></i>
                                     <p>Listado de compras</p>
                                 </a>
                             </li>
                              <li class="nav-item">
                                 <a href="<?php echo $URL;?>/compras/create.php" class="nav-link">
                                     <i class="far fa-circle nav-icon"></i>
                                     <p>Registrar Nueva Compra</p>
                                 </a>
                             </li>
                         </ul>
                      </li>




                    <li class="nav-item ">
                        <a href="#" class="nav-link active">
                            <i class="nav-icon fas fa-car"></i>
                            <p>
                                Proveedores
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?php echo $URL;?>/proveedores" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listado de proveedores</p>
                                </a>
                            </li>
                        </ul>
                    </li>






                    <li class="nav-item ">
                        <a href="#" class="nav-link active">
                            <i class="nav-icon fas fa-shopping-basket"></i>
                            <p>
                                Ventas
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?php echo $URL;?>/ventas" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listado de ventas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo $URL;?>/ventas/create.php" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Realizar ventas</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                   

<!-- Agregar antes del item de Cerrar Sesión -->
<li class="nav-item">
    <a href="<?php echo $URL;?>/perfil" class="nav-link">
        <i class="nav-icon fas fa-user-circle"></i>
        <p>
            Mi Perfil
        </p>
    </a>
</li>


                    <li class="nav-item">
                        <a href="<?php echo $URL;?>/app/controllers/login/cerrar_sesion.php" class="nav-link" style="background-color: #ca0a0b">
                            <i class="nav-icon fas fa-door-closed"></i>
                            <p>
                                Cerrar Sesión
                            </p>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>
