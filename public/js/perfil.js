/**
 * JavaScript para validaciones del lado del cliente y mejoras de UX en el módulo de perfil.
 * Sistema de Ventas Casa - @samzinalker
 * Fecha de actualización: 2025-05-26
 */
$(document).ready(function() {
    
    // Inicializar el componente bs-custom-file-input para mostrar el nombre del archivo seleccionado.
    if (typeof bsCustomFileInput !== 'undefined') {
        bsCustomFileInput.init();
    } else {
        console.warn('bsCustomFileInput no está definido. Asegúrate de que el plugin esté cargado.');
    }
    
    // --- Validación para el formulario de cambio de contraseña (ID: #form-password) ---
    $("#form-password").submit(function(event) {
        const passwordActual = $("#password_actual").val(); // No es necesario trim() para contraseñas generalmente
        const passwordNueva = $("#password_nueva").val();
        const passwordConfirmar = $("#password_confirmar").val();
        let isValid = true;
        
        // Limpiar errores previos de SweetAlert si los hubiera (opcional, depende del flujo deseado)
        // Swal.close(); 

        if (!passwordActual) {
            event.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campo Requerido',
                text: 'Por favor, ingrese su contraseña actual.',
                confirmButtonColor: '#3085d6'
            });
            $("#password_actual").focus();
            isValid = false;
            return false;
        }
        
        if (!passwordNueva) {
            event.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campo Requerido',
                text: 'Por favor, ingrese su nueva contraseña.',
                confirmButtonColor: '#3085d6'
            });
            $("#password_nueva").focus();
            isValid = false;
            return false;
        }

        if (passwordNueva.length < 6) {
            event.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Contraseña Insegura',
                text: 'La nueva contraseña debe tener al menos 6 caracteres.',
                confirmButtonColor: '#fab005' // Amarillo para warning
            });
            $("#password_nueva").focus();
            isValid = false;
            return false;
        }
        
        if (!passwordConfirmar) {
            event.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campo Requerido',
                text: 'Por favor, confirme su nueva contraseña.',
                confirmButtonColor: '#3085d6'
            });
            $("#password_confirmar").focus();
            isValid = false;
            return false;
        }

        if (passwordNueva !== passwordConfirmar) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Las Contraseñas no Coinciden',
                text: 'La nueva contraseña y su confirmación deben ser idénticas.',
                confirmButtonColor: '#d33' // Rojo para error
            });
            $("#password_confirmar").focus();
            isValid = false;
            return false;
        }
        
        // Opcional: Evitar que la nueva contraseña sea igual a la actual (si se puede verificar aquí, aunque es mejor en servidor)
        // if (passwordActual === passwordNueva) {
        //     event.preventDefault();
        //     Swal.fire('Advertencia', 'La nueva contraseña no puede ser igual a la contraseña actual.', 'warning');
        //     isValid = false;
        //     return false;
        // }

        // Si todas las validaciones pasan, el formulario se envía.
        // Si se desea mostrar un spinner o deshabilitar el botón mientras se procesa:
        if (isValid) {
            // $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Actualizando...');
        }
        return isValid;
    });
    
    // --- Validación para el formulario de actualización de datos personales (ID: #form-actualizar-datos) ---
    $("#form-actualizar-datos").submit(function(event) {
        const nombres = $("#nombres_edit").val().trim();
        const email = $("#email_edit").val().trim();
        let isValid = true;

        if (!nombres) {
            event.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campo Requerido',
                text: 'El nombre completo es obligatorio.',
                confirmButtonColor: '#3085d6'
            });
            $("#nombres_edit").focus();
            isValid = false;
            return false;
        }
        
        if (!email) {
            event.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campo Requerido',
                text: 'El Usuario / Email de Contacto es obligatorio.',
                confirmButtonColor: '#3085d6'
            });
            $("#email_edit").focus();
            isValid = false;
            return false;
        }

        // Validación básica de formato de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Formato Incorrecto',
                text: 'Por favor, ingrese un email válido.',
                confirmButtonColor: '#d33'
            });
            $("#email_edit").focus();
            isValid = false;
            return false;
        }

        if (isValid) {
            // $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');
        }
        return isValid;
    });
    
    // --- Validación y vista previa para el formulario de subida de imagen (ID: #form-actualizar-imagen) ---
    // Input de archivo: #imagen_perfil_upload
    // Imagen de vista previa: .profile-user-img (clase usada en tu HTML)
    
    $("#imagen_perfil_upload").change(function(event) {
        const file = this.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB (debe coincidir con la validación del servidor)
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif']; // Tipos MIME permitidos

        if (file) {
            // Validar tipo de archivo
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tipo de Archivo no Permitido',
                    text: 'Solo se permiten imágenes en formato JPG, PNG y GIF.',
                    confirmButtonColor: '#d33'
                });
                $(this).val(''); // Limpiar el input de archivo
                // Resetear bsCustomFileInput si es necesario para que muestre "Seleccionar archivo..."
                $(this).next('.custom-file-label').html('Seleccionar archivo...');
                // Resetear la imagen de vista previa a la original (si se tiene una referencia) o a la default.
                // Esto requeriría almacenar la URL de la imagen original al cargar la página.
                // Por ahora, no se resetea la vista previa aquí, solo se limpia el input.
                return;
            }

            // Validar tamaño del archivo
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'Archivo Demasiado Grande',
                    text: 'La imagen no debe superar los 2MB.',
                    confirmButtonColor: '#d33'
                });
                $(this).val(''); // Limpiar el input de archivo
                $(this).next('.custom-file-label').html('Seleccionar archivo...');
                return;
            }

            // Si las validaciones pasan, mostrar la vista previa
            const reader = new FileReader();
            reader.onload = function(e) {
                // Asegúrate que la clase .profile-user-img exista y sea la correcta para tu imagen de perfil.
                $(".profile-user-img").attr('src', e.target.result);
            }
            reader.readAsDataURL(file);

            // Actualizar el label de bsCustomFileInput con el nombre del archivo
            $(this).next('.custom-file-label').html(file.name);

        } else {
             // Si no se selecciona archivo (ej. se cancela el diálogo), limpiar el label.
            $(this).next('.custom-file-label').html('Seleccionar archivo...');
        }
    });

    // Validación en el submit del formulario de imagen (opcional, ya que se valida en el 'change')
    $("#form-actualizar-imagen").submit(function(event) {
        const imagenInput = $("#imagen_perfil_upload")[0];
        let isValid = true;

        if (imagenInput.files.length === 0) {
            event.preventDefault(); // Prevenir envío si no hay archivo
            Swal.fire({
                icon: 'warning',
                title: 'No Hay Imagen Seleccionada',
                text: 'Debes seleccionar una imagen para actualizar tu perfil.',
                confirmButtonColor: '#fab005'
            });
            isValid = false;
            return false;
        }
        // Las validaciones de tipo y tamaño ya se hacen en el evento 'change'.
        // Si se quiere, se pueden repetir aquí como una doble verificación.

        if (isValid) {
             // $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Subiendo...');
        }
        return isValid;
    });

});