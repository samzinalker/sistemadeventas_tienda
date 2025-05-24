<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de ventas</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../public/templeates/AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="../public/templeates/AdminLTE-3.2.0/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../public/templeates/AdminLTE-3.2.0/dist/css/adminlte.min.css">

    <!-- Libreria Sweetallert2-->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <!-- /.login-logo -->


    <?php
    session_start();
    if(isset($_SESSION['mensaje'])){
        $respuesta = $_SESSION['mensaje']; 
        $icono = isset($_SESSION['icono']) ? $_SESSION['icono'] : 'error';
        ?>
        <script>
            Swal.fire({
                position: 'top-end',
                icon: '<?php echo $icono; ?>',
                title: '<?php echo $respuesta;?>',
                showConfirmButton: false,
                timer: 1500
            })
        </script>
    <?php
        unset($_SESSION['mensaje']);
        unset($_SESSION['icono']);
    }
    ?>

    <center>
    <img src="https://img.freepik.com/vector-premium/ilustracion-costo-vida-degradado_52683-139098.jpg?w=996"
             alt="" width="300px">
    </center>
    <br>
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="#" class="h1"><b>Sistema de </b>VENTAS</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Ingrese sus datos</p>

            <form action="../app/controllers/login/ingreso.php" method="post">
                <div class="input-group mb-3">
                    <!-- Cambiado de type="email" a type="text" para permitir cualquier formato -->
                    <input type="text" name="email" class="form-control" placeholder="Usuario">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <!-- Cambiado de fa-envelope a fa-user para reflejar que es un usuario -->
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password_user" class="form-control" placeholder="Password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <!-- /.col -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>

            <!-- Añadidos enlaces para recuperar contraseña y crear cuenta nueva -->
            <div class="mt-3">
                <div class="row">
                    <div class="col-12 text-center">
                        <a href="#" id="olvidar-pass">Olvidé mi contraseña</a>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-center">
                        <a href="registro.php" class="text-primary">Crear una cuenta nueva</a>
                    </div>
                </div>
            </div>

        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="../public/templeates/AdminLTE-3.2.0/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../public/templeates/AdminLTE-3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../public/templeates/AdminLTE-3.2.0/dist/js/adminlte.min.js"></script>

<!-- Script para manejar el olvido de contraseña -->
<script>
$(document).ready(function() {
    $("#olvidar-pass").click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Soporte Técnico',
            html: 'Contacta al administrador para recuperar tu contraseña: <br><b>0963593766</b>',
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
    });
});
</script>
</body>
</html>