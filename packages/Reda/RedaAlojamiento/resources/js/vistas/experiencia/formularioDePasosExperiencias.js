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

                    $('#image-to-crop').attr('src', correctedSrc);
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
                $('#cropModal').on('shown.bs.modal', function () {
                    let image = document.getElementById('image-to-crop');
                    cropper = new Cropper(image, {
                        aspectRatio: 4 / 3,
                        viewMode: 1,
                    });
                });

                // BOTÓN GUARDAR DEL MODAL
                $('#crop-and-upload').on('click', function() {
                    let canvas = cropper.getCroppedCanvas();
                    canvas.toBlob(function(blob) {
                        let formData = new FormData();
                        let photoId = $('#crop_photo_id').val();
                        
                        formData.append('cropped_image', blob, 'photo.jpg');
                        formData.append('_token', $('input[name="_token"]').val());
                        formData.append('photo_id', photoId);

                        // Si hay photoId, editamos; si no, subimos nueva.
                        let urlAction = photoId ? APP_URL + '/reda/crop-photo' : APP_URL + '/reda/upload-photos/' + $('#experiencia_id').val();
                        console.log('urlAction:', urlAction);

                        $.ajax({
                            url: urlAction,
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if(response.success) {
                                    location.reload(); // Recargamos para ver los cambios reflejados
                                }
                            }
                        });
                    });
                });

                break;    
                        
            // ... resto de los pasos
        }
    }
});