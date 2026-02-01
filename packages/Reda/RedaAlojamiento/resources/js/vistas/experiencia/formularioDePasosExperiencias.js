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

                // EVENTO: SELECCIONAR ARCHIVOS NUEVOS (Input change)
                $(document).on('change', '#upload_photos', function(e) {
                    let files = e.target.files;
                    if (files && files.length > 0) {
                        let file = files[0];
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            // Para fotos nuevas, no necesitamos el prefijo /public/ 
                            // porque vienen directamente del navegador (base64)
                            $('#image-to-crop').attr('src', e.target.result);
                            $('#crop_photo_id').val(''); // ID vacío significa "foto nueva"
                            
                            if (cropper) {
                                cropper.destroy();
                            }
                            $('#cropModal').modal('show');
                        };
                        reader.readAsDataURL(file);
                    }
                });

                // INICIALIZACIÓN DE CROPPER AL ABRIR EL MODAL
                let cropper;
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
                        autoCropArea: 1,
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

                // BOTÓN GUARDAR DEL MODAL
                $('#crop-and-upload').on('click', function() {
                    let $btn = $(this);
                    $btn.prop('disabled', true).text('Procesando...'); // Feedback visual
                    
                    let canvas = cropper.getCroppedCanvas();
                    canvas.toBlob(function(blob) {
                        let formData = new FormData();
                        let photoId = $('#crop_photo_id').val();
                        
                        formData.append('cropped_image', blob, 'photo.jpg');
                        formData.append('_token', $('input[name="_token"]').val());
                        formData.append('photo_id', photoId);

                        // Si hay photoId, editamos; si no, subimos nueva.
                        let urlAction = photoId ? APP_URL + '/reda/crop-photo-experiencia' : APP_URL + '/reda/upload-photo-experiencia/' + $('#experiencia_id').val();
                        console.log('photoId:', photoId);
                        console.log('urlAction:', urlAction);
                        console.log('#experiencia_id:', $('#experiencia_id').val());

                        $.ajax({
                            url: urlAction,
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if(response.success) {
                                    let photoId = $('#crop_photo_id').val();
                                    if(photoId) {
                                        $(`#photo-${photoId} img`).attr('src', response.new_path);
                                    }
                                    alert(response.message); // Mensaje de éxito
                                    location.reload(); // Recargamos para ver los cambios reflejados
                                }
                            },
                            error: function(xhr) {
                                $btn.prop('disabled', false).text('Guardar Cambios');
                                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al guardar la imagen';
                                alert('Error: ' + msg); // Mensaje de error
                            }
                        });
                    }, 'image/jpeg');
                });

                break;    
                        
            // ... resto de los pasos
        }
    }
});