<?php
// DEBUG START
echo "DEBUG: Inicio de mensajes.php. ";
if( (isset($_SESSION['mensaje'])) && (isset($_SESSION['icono'])) ){
    echo "Mensaje e icono ESTÁN SETEADOS en SESSION.<br>"; // DEBUG
    $respuesta = $_SESSION['mensaje'];
    $icono = $_SESSION['icono'];
    ?>
    <script>
        // DEBUGGING SWEETALERT ITSELF
        console.log("SweetAlert script a punto de ejecutarse con icono: <?php echo $icono; ?> y mensaje: <?php echo addslashes($respuesta); ?>"); 
        Swal.fire({
            position: 'top-end',
            icon: '<?php echo $icono; ?>',
            title: '<?php echo addslashes($respuesta);?>', // Usar addslashes por si el mensaje tiene comillas
            showConfirmButton: false,
            timer: 2500
        });
    </script>
    <?php
    unset($_SESSION['mensaje']);
    unset($_SESSION['icono']);
} else {
    echo "Mensaje o icono NO ESTÁN SETEADOS en SESSION.<br>"; // DEBUG
    //echo "Contenido actual de SESSION en mensajes.php:<pre>"; // DEBUG
   // print_r($_SESSION); // DEBUG
    //echo "</pre>"; // DEBUG
}
// DEBUG END
?>