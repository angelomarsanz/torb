"use strict";

console.log('Se cargó correctamente media.js v8');
// Crear el contenedor del loader
if ($('#custom-loader').length === 0) {
    const procesando = window.RedaAlojamiento.general.procesando;
    const loaderHtml = `
        <div id="custom-loader" class="loader-overlay">
            <div id="loader-content"></div>
            <h4 id="loader-text" class="font-weight-700">${procesando}</h4>
        </div>`;
    $('body').append(loaderHtml);
}

// 2. Función global para disparar el loader desde cualquier vista
window.showLoader = function(type, text) {
    let content = '';
    if (type === 'crop') {
        content = '<i class="fa fa-crop loader-icon anim-crop"></i>';
    } else if (type === 'delete') {
        content = `
            <div class="anim-trash-container">
                <i class="fa fa-minus trash-lid"></i>
                <i class="fa fa-trash loader-icon anim-trash"></i>
            </div>`;
    } else if (type === 'star') {
        // Animación de estrella con efecto de destello
        content = '<i class="fa fa-star loader-icon anim-star"></i>';
    }
    
    $('#loader-content').html(content);
    $('#loader-text').text(text);
    $('#custom-loader').css('display', 'flex').hide().fadeIn();
}
                
$(document).on('change', '.upload_photos', function() {
    let file = this.files[0];
    let inputSelect = $(this);

    let actividadId = inputSelect.attr('data-id') || inputSelect.data('id');
    console.log('ID capturado al cambiar archivo:', actividadId);

    if (file) {
        const extensionesPermitidas = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
        if (!extensionesPermitidas.exec(file.name)) {
            alert(window.RedaAlojamiento.general.solo_se_permiten_imagenes_jpg_jpeg_png_gif);
            $(this).val('');
            return false;
        }

        if (file.size > 26214400) {
            alert(RedaAlojamiento.general.el_archivo_es_muy_pesado_máximo_25_mb);
            $(this).val('');
            return false;
        }

        const origen = $('#crop-and-upload').data('origen');

        let reader = new FileReader();
        reader.onload = function(e) {
            // Ponemos la imagen en el modal
            $('#image-to-crop').attr('src', e.target.result);
            if (origen === 'actividades-experiencias') {
                if (actividadId == null)
                {
                    actividadId = $(this).attr('data-id');
                }
                if (actividadId) {
                    $('#crop_photo_id').val(actividadId);
                }
                console.log('ID de actividad detectado y asignado:', actividadId);
            }
            else
            {
                // Limpiamos el photo_id porque es una foto NUEVA
                $('#crop_photo_id').val('');
            } 
            if (cropper) {
                cropper.destroy();
            }
            // Abrimos el modal
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
        autoCropArea: 1,
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
        window.showLoader('crop', window.RedaAlojamiento.general.recortando_y_subiendo_imagen);
        let formData = new FormData();
        const origen = $(this).data('origen');

        let idPrincipal = null;
        let photoId = null;

        if (origen == "fotos-experiencias") {
            idPrincipal = $('#experiencia_id').val();
            photoId = $('#crop_photo_id').val();
            if(photoId) formData.append('photo_id', photoId);
        } else
        {
            idPrincipal = $('#crop_photo_id').val();
        }

        formData.append('cropped_image', blob, 'photo.jpg');
        formData.append('_token', $('input[name="_token"]').val());
        
        // Si hay photoId es una edición, si no, es una subida nueva
        let urlAction = photoId 
            ? APP_URL + '/reda/crop-photo' 
            : APP_URL + '/reda/upload-photo/' + idPrincipal;

        formData.append('origen', origen);

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
                    $('#loader-text').text(window.RedaAlojamiento.general.imagen_actualizada);
                    setTimeout(() => {
                        $('#custom-loader').fadeOut();
                        // DISPARAR EVENTO PERSONALIZADO
                        const event = new CustomEvent('mediaUpdated', { 
                            detail: { 
                                origen: origen,
                                accion: "crop-and-upload", 
                                response: response,
                            } 
                        });
                        document.dispatchEvent(event);                        
                    }, 800);
                }
                else
                {
                    $('#custom-loader').fadeOut();
                    alert(window.RedaAlojamiento.general.error + response.message);
                }
            },
            error: function(xhr) {
                $('#custom-loader').fadeOut();
                let msg = xhr.responseJSON ? xhr.responseJSON.message : window.RedaAlojamiento.general.error_al_guardar_la_imagen;
                alert(window.RedaAlojamiento.general.error_del_servidor + msg); // Mensaje de error
            }
        });
    });
});

// --- BOTÓN IMAGEN DESTACADA ---
$(document).on('click', '.make-default', function(e) {
    e.preventDefault();
    let photoId = $(this).data('id');
    let starUrl = APP_URL + '/reda/make-default-photo';
    let expId = $('#experiencia_id').val();
    const origen = $(this).data('origen');
    showLoader('star', window.RedaAlojamiento.general.marcando_como_foto_de_portada);

    $.post(starUrl, {
        _token: $('input[name="_token"]').val(),
        photo_id: photoId,
        experiencia_id: expId,
        origen: origen
    }, function(response) {
        if (response.success) {
            $('#loader-text').text(window.RedaAlojamiento.general.portada_actualizada);
            setTimeout(() => {
                $('#custom-loader').fadeOut();
                // DISPARAR EVENTO PERSONALIZADO
                const event = new CustomEvent('mediaUpdated', { 
                    detail: { 
                        origen: origen,
                        accion: "make-default", 
                        response: response,
                    } 
                });
                document.dispatchEvent(event);                        
            }, 800);
        } else {
            $('#custom-loader').fadeOut();
            alert(window.RedaAlojamiento.general.error_al_marcar_como_predeterminada);
        }
    }).fail(function() {
        $('#custom-loader').fadeOut();
        alert(window.RedaAlojamiento.general.error_en_el_servidor);
    });
});

// --- BOTÓN ELIMINAR ---
$(document).on('click', '.delete-photo', function(e) {
    e.preventDefault();
    let photoId = $(this).data('id');
    let deleteUrl = APP_URL + '/reda/delete-photo';
    const origen = $(this).data('origen');
    if (confirm(window.RedaAlojamiento.general.estas_seguro_de_eliminar_esta_foto)) {
        showLoader('delete', window.RedaAlojamiento.general.eliminando_permanentemente);
        $.post(deleteUrl, {
            _token: $('input[name="_token"]').val(),
            photo_id: photoId,
            origen: origen
        },function(response) {
            if (response.success) {
                $('#loader-text').text(window.RedaAlojamiento.general.eliminado_con_exito);
                setTimeout(() => {
                    $('#custom-loader').fadeOut();
                    // DISPARAR EVENTO PERSONALIZADO
                    const event = new CustomEvent('mediaUpdated', { 
                        detail: { 
                            origen: origen,
                            accion: "delete-photo", 
                            response: response,
                        } 
                    });
                    document.dispatchEvent(event);                        
                }, 800);
            } else {
                $('#custom-loader').fadeOut();
                alert(window.RedaAlojamiento.general.error_al_eliminar);
            }
        }).fail(function() {
            $('#custom-loader').fadeOut();
            alert(window.RedaAlojamiento.general.error_en_el_servidor_no_se_pudo_eliminar_la_foto);
        });
    }
});