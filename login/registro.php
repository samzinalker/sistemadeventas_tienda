<?php
include('../app/config.php');
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de ventas | Registro</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../public/templeates/AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="../public/templeates/AdminLTE-3.2.0/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../public/templeates/AdminLTE-3.2.0/dist/css/adminlte.min.css">
    <!-- Libreria Sweetallert2 -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="hold-transition register-page">
<div class="register-box">
    <center>
        <img src="https://img.freepik.com/vector-premium/ilustracion-costo-vida-degradado_52683-139098.jpg?w=996"
             alt="" width="200px">
    </center>
    <br>
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="#" class="h1"><b>Sistema de </b>VENTAS</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Registrar una nueva cuenta</p>

            <form action="../app/controllers/login/registro.php" method="post" id="formulario-registro">
                <div class="input-group mb-3">
                    <input type="text" name="nombres" class="form-control" placeholder="Nombre completo" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="text" name="email" class="form-control" placeholder="Usuario" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password_user" class="form-control" placeholder="Contraseña" required id="password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="repassword" class="form-control" placeholder="Confirmar contraseña" required id="repassword">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="agreeTerms" name="terms" value="agree" required>
                            <label for="agreeTerms">
                                Acepto los <a href="#">términos</a>
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block" id="btnRegistrar">Registrar</button>
                    </div>
                </div>
            </form>

            <div class="mt-3 text-center">
                <a href="../login" class="text-center">Ya tengo una cuenta</a>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="../public/templeates/AdminLTE-3.2.0/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../public/templeates/AdminLTE-3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../public/templeates/AdminLTE-3.2.0/dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    // Validar que las contraseñas coincidan
    $("#formulario-registro").submit(function(e) {
        var password = $("#password").val();
        var repassword = $("#repassword").val();
        
        if (password !== repassword) {
            e.preventDefault();
            Swal.fire({
                title: 'Error',
                text: 'Las contraseñas no coinciden',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            Swal.fire({
                title: 'Contraseña insegura',
                text: 'La contraseña debe tener al menos 6 caracteres',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }
    });
});
</script>

<?php
if(isset($_SESSION['mensaje'])){
    $respuesta = $_SESSION['mensaje']; 
    $icono = isset($_SESSION['icono']) ? $_SESSION['icono'] : 'error';
    ?>
    <script>
        Swal.fire({
            position: 'top-end',
            icon: '<?php echo $icono; ?>',
            title: '<?php echo $respuesta; ?>',
            showConfirmButton: false,
            timer: 1500
        })
    </script>
<?php
    unset($_SESSION['mensaje']);
    unset($_SESSION['icono']);
}
?>
</body>
</html>