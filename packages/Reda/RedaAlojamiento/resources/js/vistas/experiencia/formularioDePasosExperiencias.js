"use strict";

$(function() {
    const container = $('.formulario-de-pasos');
    if (container.length) {
        const currentStep = container.data('step');
        console.log('Cargando validaciones para el paso:', currentStep);

        switch (currentStep) {
            case 'descripcion':
                $('#list_des').validate({
                    rules: {
                        titulo: { required: true, maxlength: 255 },
                        descripcion: { required: true, minlength: 20 }
                    },
                    submitHandler: function(form) {
                        $("#btn_next").attr("disabled", true);
                        $(".spinner").removeClass('d-none');
                        return true;
                    }
                });
                break;

            case 'fotos':
                // CONFIGURACIÓN DE VALIDACIÓN
                $('#img_form').validate({
                    rules: {
                        'photos[]': {
                            required: function() {
                                return $('.photo-item').length === 0;
                            },
                            extension: "jpg|jpeg|png|gif"
                        }
                    },
                    messages: {
                        'photos[]': {
                            required: "Por favor, sube al menos una foto.",
                            extension: "Solo se permiten imágenes (jpg, jpeg, png, gif)."
                        }
                    },
                    submitHandler: function(form) {
                        $("#btn_next").attr("disabled", true);
                        $(".spinner").removeClass('d-none');
                        $("#btn_next-text").text("Subiendo...");
                        return true;
                    }
                });

                // Inyectar estilos para las animaciones modernas
                const style = document.createElement('style');
                style.innerHTML = `
                    .loader-overlay {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100vw;
                        height: 100vh;
                        background: rgba(255, 255, 255, 0.9);
                        display: none; /* Se activa con .fadeIn() de jQuery */
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        z-index: 10000;
                        backdrop-filter: blur(5px);
                    }
                    
                    #loader-content {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        min-height: 200px;
                    }

                    .loader-icon { font-size: 80px; color: #28a745; margin-bottom: 20px; }
                    
                    /* Animación Recorte */
                    .anim-crop { animation: pulse-crop 1.5s infinite; }
                    @keyframes pulse-crop {
                        0% { transform: scale(1) rotate(0deg); }
                        50% { transform: scale(1.2) rotate(15deg); }
                        100% { transform: scale(1) rotate(0deg); }
                    }

                    /* Animación Papelera Mejorada */
                    .anim-trash-container { text-align: center; position: relative; width: 100px; }
                    .anim-trash { animation: shake-trash 0.8s infinite; color: #dc3545; }
                    .trash-lid { 
                        font-size: 40px;
                        position: absolute;
                        left: 25px;
                        top: -30px;
                        animation: close-lid 1.2s infinite; 
                    }
                    
                    @keyframes close-lid {
                        0% { transform: translateY(-20px) rotate(-30deg); opacity: 0; }
                        50% { transform: translateY(0) rotate(0deg); opacity: 1; }
                        100% { transform: translateY(0) rotate(0deg); opacity: 1; }
                    }
                    @keyframes shake-trash {
                        0%, 100% { transform: rotate(0deg); }
                        25% { transform: rotate(-5deg); }
                        75% { transform: rotate(5deg); }
                    }
                `;
                document.head.appendChild(style);

                // Crear el contenedor del loader
                const loaderHtml = `
                    <div id="custom-loader" class="loader-overlay">
                        <div id="loader-content"></div>
                        <h4 id="loader-text" class="font-weight-700">Procesando...</h4>
                    </div>`;
                $('body').append(loaderHtml);

                function showLoader(type, text) {
                    let content = '';
                    if (type === 'crop') {
                        content = '<i class="fa fa-crop loader-icon anim-crop"></i>';
                    } else if (type === 'delete') {
                        content = `
                            <div class="anim-trash-container">
                                <i class="fa fa-minus trash-lid"></i>
                                <i class="fa fa-trash loader-icon anim-trash"></i>
                            </div>`;
                    }
                    
                    $('#loader-content').html(content);
                    $('#loader-text').text(text);
                    
                    // Forzamos el flex antes del fadeIn para que el centrado funcione
                    $('#custom-loader').css('display', 'flex').hide().fadeIn();
                }
                                
                $(document).on('change', '#upload_photos', function() {
                    let file = this.files[0];
                    if (file) {
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            // 1. Ponemos la imagen en el modal
                            $('#image-to-crop').attr('src', e.target.result);
                            // 2. Limpiamos el photo_id porque es una foto NUEVA
                            $('#crop_photo_id').val(''); 
                            // 3. Abrimos el modal
                            $('#cropModal').modal('show');
                        };
                        reader.readAsDataURL(file);
                    }
                    $(this).val('');
                });

                let cropper;

                // EVENTO: CLIC EN "EDITAR" (Para fotos que ya existen en la lista)
                $(document).on('click', '.btn-crop', function() {
                    let photoId = $(this).data('id');
                    let photoSrc = $(this).data('src'); 
                    
                    // CORRECCIÓN DE RUTA PARA EL MODAL
                    let correctedSrc = photoSrc;
                    if (photoSrc && photoSrc.indexOf('/public/') === -1) {
                        correctedSrc = photoSrc.replace('/images/', '/public/images/');
                    }

                    let correctedSrcWithCacheBuster = correctedSrc + (correctedSrc.includes('?') ? '&' : '?') + 'v=' + new Date().getTime();

                    // Cargamos la imagen con el cache buster en el visor del modal
                    $('#image-to-crop').attr('src', correctedSrcWithCacheBuster);

                    $('#crop_photo_id').val(photoId);
                    
                    // Destruir instancia previa de Cropper si existe
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    $('#cropModal').modal('show');
                });

                // INICIALIZACIÓN DE CROPPER AL ABRIR EL MODAL
                let isShiftPressed = false;
                
                // 1. Interruptor de teclado (Limpio, sin funciones de cropper)
                $(document).on('keydown', function(e) { if (e.key === "Shift") isShiftPressed = true; });
                $(document).on('keyup', function(e) { if (e.key === "Shift") isShiftPressed = false; });
                
                // 2. Configuración del Cropper
                $('#cropModal').on('shown.bs.modal', function () {
                    let image = document.getElementById('image-to-crop');
                    
                    cropper = new Cropper(image, {
                        aspectRatio: NaN, 
                        viewMode: 1,
                        autoCropArea: 0.8,
                        dragMode: 'move',
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                        // CLAVE 1: Antes de que el cuadro se mueva
                        cropstart: function (event) {
                            if (isShiftPressed) {
                                let data = cropper.getData();
                                let currentRatio = data.width / data.height;
                                
                                // Aplicamos el ratio pero SIN disparar el re-renderizado automático
                                cropper.options.aspectRatio = currentRatio;
                                cropper.setAspectRatio(currentRatio);
                            }
                        },
                        // CLAVE 2: Al soltar el ratón (Aquí es donde fallaba antes)
                        cropend: function () {
                            // Guardamos la posición EXACTA donde el usuario dejó el ratón
                            let lastData = cropper.getData();
                            
                            // Volvemos a modo libre para que el usuario pueda mover lados individualmente
                            cropper.options.aspectRatio = NaN; 
                            cropper.setAspectRatio(NaN); 
                            
                            // FORZAMOS a que el marco se quede donde lo soltamos
                            // Esto evita que Cropper lo expanda al tamaño original
                            cropper.setData(lastData);
                        }
                    });
                });

                $('#cropModal').on('hidden.bs.modal', function () {
                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }
                    isShiftPressed = false; // Resetear bandera
                });

                $('#crop-and-upload').on('click', function() {
                    let $btn = $(this);
                    cropper.getCroppedCanvas().toBlob((blob) => {
                        $('#cropModal').modal('hide'); // Cerramos el modal para que se vea la animación central
                        showLoader('crop', 'Recortando y subiendo imagen...');
                        let formData = new FormData();
                        let photoId = $('#crop_photo_id').val();
                        let expId = $('#experiencia_id').val();
                
                        formData.append('cropped_image', blob, 'photo.jpg');
                        formData.append('_token', $('input[name="_token"]').val());
                        
                        // Si hay photoId es una edición, si no, es una subida nueva
                        let urlAction = photoId 
                            ? APP_URL + '/reda/crop-photo-experiencia' 
                            : APP_URL + '/reda/upload-photo-experiencia/' + expId;
                
                        if(photoId) formData.append('photo_id', photoId);

                        console.log('urlAction', urlAction);
                        $.ajax({
                            url: urlAction,
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                console.log("Respuesta del servidor:", response);
                                if(response.success) {
                                    $('#loader-text').text('¡Todo listo! Actualizando...');
                                    $('#cropModal').modal('hide');
                                    location.reload();
                                }
                                else
                                {
                                    $('#custom-loader').fadeOut();
                                    alert('Error: ' + response.message);
                                }
                            },
                            error: function(xhr) {
                                $('#custom-loader').fadeOut();
                                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al guardar la imagen';
                                alert('Error del servidor: ' + msg); // Mensaje de error
                            }
                        });
                    });
                });

                // --- BOTÓN ELIMINAR ---
                $(document).on('click', '.delete-photo', function(e) {
                    e.preventDefault();
                    showLoader('delete', 'Eliminando permanentemente...');
                    let photoId = $(this).data('id');
                    if (confirm('¿Estás seguro de eliminar esta foto?')) {
                        $.post(APP_URL + '/reda/delete-photo-experiencia', {
                            _token: $('input[name="_token"]').val(),
                            photo_id: photoId
                        },function(response) {
                            if (response.success) {
                                $('#loader-text').text('Eliminado con éxito');
                                location.reload();
                            } else {
                                $('#custom-loader').fadeOut();
                                alert('Error al eliminar');
                            }
                        }).fail(function() {
                            $('#custom-loader').fadeOut();
                            alert('Error en el servidor, no se pudo eliminar la foto');
                        });
                    }
                });

                // --- BOTÓN IMAGEN DESTACADA ---
                $(document).on('click', '.make-default', function(e) {
                    e.preventDefault();
                    let photoId = $(this).data('id');
                    $.post(APP_URL + '/reda/make-default-photo-experiencia', {
                        _token: $('input[name="_token"]').val(),
                        photo_id: photoId,
                        experiencia_id: $('#experiencia_id').val()
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    });
                });

                break;    
                        
            // ... resto de los pasos
        }
    }
});