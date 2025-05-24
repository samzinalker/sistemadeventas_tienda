/**
 * JavaScript para validaciones del módulo de perfil
 * Sistema de Ventas
 */
$(document).ready(function() {
    
    // Inicializar el componente de archivo personalizado
    bsCustomFileInput.init();
    
    // Validación para el formulario de cambio de contraseña
    $("#form-password").submit(function(event) {
        const passwordActual = $("#password_actual").val().trim();
        const passwordNueva = $("#password_nueva").val().trim();
        const passwordConfirmar = $("#password_confirmar").val().trim();
        
        // Verificar que todos los campos estén completos
        if (!passwordActual || !passwordNueva || !passwordConfirmar) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Campos incompletos',
                text: 'Todos los campos de contraseña son obligatorios'
            });
            return false;
        }
        
        // Verificar longitud mínima
        if (passwordNueva.length < 6) {
            event.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Contraseña insegura',
                text: 'La contraseña debe tener al menos 6 caracteres'
            });
            return false;
        }
        
        // Verificar que las contraseñas nuevas coincidan
        if (passwordNueva !== passwordConfirmar) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Las contraseñas no coinciden',
                text: 'La nueva contraseña y su confirmación deben ser idénticas'
            });
            return false;
        }
    });
    
    // Validación para el formulario de actualización de datos personales
    $("form[action='../app/controllers/perfil/actualizar_datos.php']").submit(function(event) {
        const nombres = $("#nombres").val().trim();
        const email = $("#email").val().trim();
        
        if (!nombres || !email) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Campos incompletos',
                text: 'El nombre y el email son obligatorios'
            });
            return false;
        }
    });
    
    // Validación para el formulario de subida de imagen
    $("form[action='../app/controllers/perfil/actualizar_imagen.php']").submit(function(event) {
        const imagen = $("#imagen")[0].files;
        
        if (imagen.length === 0) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'No hay imagen seleccionada',
                text: 'Debes seleccionar una imagen para actualizar tu perfil'
            });
            return false;
        }
        
        const file = imagen[0];
        const fileSize = file.size;
        const fileType = file.type;
        const maxSize = 2 * 1024 * 1024; // 2MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        // Verificar el tipo de archivo
        if (!allowedTypes.includes(fileType)) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Tipo de archivo no permitido',
                text: 'Solo se permiten imágenes en formato JPG, PNG y GIF'
            });
            return false;
        }
        
        // Verificar el tamaño del archivo
        if (fileSize > maxSize) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Archivo demasiado grande',
                text: 'La imagen no debe superar los 2MB'
            });
            return false;
        }
    });
    
    // Mostrar vista previa de la imagen seleccionada
    $("#imagen").change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $(".profile-user-img").attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });
});