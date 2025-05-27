<?php
session_start();
?>
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

<!-- ✅ APLICAR ESTILOS DIRECTAMENTE EN EL BODY -->
<body class="hold-transition login-page" style="
    background-image: url('../public/images/fondo.jpg') !important;
    background-size: cover !important;
    background-position: center center !important;
    background-repeat: no-repeat !important;
    background-attachment: fixed !important;
    min-height: 100vh !important;
    position: relative !important;
    background-color: #667eea !important;
">

<!-- Overlay con posición absoluta -->
<div style="
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0, 0, 0, 0.5) !important;
    z-index: 1 !important;
"></div>

<div class="login-box" style="position: relative !important; z-index: 10 !important;">
    <?php
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
    <img src="../public/images/logo1.jpg"
             alt="Sistema de Ventas" width="300px" style="
             filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.3)) !important;
             border-radius: 15px !important;
             border: 3px solid rgba(255, 255, 255, 0.9) !important;
             background: rgba(255, 255, 255, 0.9) !important;
             padding: 10px !important;">
    </center>
    <br>
    
    <div class="card card-outline card-primary" style="
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px) !important;
        border: 2px solid rgba(255, 255, 255, 0.3) !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3) !important;
        border-radius: 15px !important;">
        
        <div class="card-header text-center" style="color: #2c3e50 !important;">
            <a href="#" class="h1" style="color: #2c3e50 !important; font-weight: bold !important;"><b>Sistema de </b>VENTAS</a>
        </div>
        
        <div class="card-body" style="color: #2c3e50 !important;">
            <p class="login-box-msg" style="color: #2c3e50 !important;">Ingrese sus datos</p>

            <form action="../app/controllers/login/ingreso.php" method="post">
                <div class="input-group mb-3">
                    <input type="text" name="usuario" class="form-control" placeholder="Usuario" required style="
                        background: #ffffff !important;
                        border: 2px solid #e9ecef !important;
                        color: #495057 !important;">
                    <div class="input-group-append">
                        <div class="input-group-text" style="
                            background: #ffffff !important;
                            border: 2px solid #e9ecef !important;
                            color: #495057 !important;">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                
                <div class="input-group mb-3">
                    <input type="password" name="password_user" class="form-control" placeholder="Password" required style="
                        background: #ffffff !important;
                        border: 2px solid #e9ecef !important;
                        color: #495057 !important;">
                    <div class="input-group-append">
                        <div class="input-group-text" style="
                            background: #ffffff !important;
                            border: 2px solid #e9ecef !important;
                            color: #495057 !important;">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                
                <hr>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block" style="
                            background: linear-gradient(45deg, #007bff, #0056b3) !important;
                            border: none !important;
                            border-radius: 8px !important;
                            font-weight: 600 !important;">Ingresar</button>
                    </div>
                </div>
            </form>

            <div class="mt-3">
                <div class="row">
                    <div class="col-12 text-center">
                        <a href="#" id="olvidar-pass" style="color: #495057 !important; font-weight: 600 !important;">Olvidé mi contraseña</a>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-center">
                        <a href="registro.php" style="color: #007bff !important;">Crear una cuenta nueva</a>
                    </div>
                </div>
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
    $("#olvidar-pass").click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Soporte Técnico',
            html: 'Contacta al administrador para recuperar tu contraseña: <br><b>0963593766</b>',
            icon: 'info',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#007bff'
        });
    });
});
</script>
</body>
</html>