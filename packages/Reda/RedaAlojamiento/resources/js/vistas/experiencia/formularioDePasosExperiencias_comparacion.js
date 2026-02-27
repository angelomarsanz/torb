case 'actividades':
    // 1. Inicializar el validador con configuración básica
    const validadorActividades = $('#list_des').validate({
        ignore: [], // Muy importante para que no ignore campos ocultos si fuera necesario
        errorPlacement: function(error, element) {
            error.addClass('text-danger small font-weight-bold');
            // Si es un textarea o input dentro de un div con clases de bootstrap, lo ponemos al final
            error.insertAfter(element);
        },
        highlight: function(element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function(form) {
            // VERIFICACIÓN MANUAL DE FOTOS
            let tieneErroresDeFoto = false;
            $('.error-foto-js').remove();
            $('.actividad-foto-card-container').css('border-color', '');
                                    
            $('.fila-actividad-container').each(function() {
                const fila = $(this);
                const contenedor = fila.find('.actividad-foto-card-container');
                const tieneImagen = contenedor.find('img').length > 0;
            
                if (!tieneImagen) {
                    tieneErroresDeFoto = true;
                    contenedor.css('border', '2px solid #dc3545');
                    contenedor.after('<div class="text-danger error-foto-js mt-1" style="font-size: 13px; font-weight: 700;"><i class="fa fa-exclamation-circle"></i> La foto es obligatoria</div>');
                }
            });
            
            if (tieneErroresDeFoto) {
                $('html, body').animate({ 
                    scrollTop: ($('.error-foto-js').first().offset().top - 150) 
                }, 500);
                return false; 
            }

            // Si llegamos aquí y el formulario es válido según las reglas dinámicas
            $("#btn_next").attr("disabled", true);
            $(".spinner").removeClass('d-none');
            $("#btn_next-text").text("Guardando...");
            
            // Usar la llamada nativa para evitar bucles de validación
            form.submit();
        }
    });

    // 2. Función mejorada para aplicar reglas a los campos array
    function aplicarReglasDinamicas() {
        $('textarea[name*="[descripcion_actividad]"]').each(function() {
            $(this).rules('add', {
                required: true,
                minlength: 5,
                messages: {
                    required: "La descripción es obligatoria (JS)",
                    minlength: "Escribe al menos 5 caracteres"
                }
            });
        });

        // Validación para ORDEN
        $('input[name*="[orden_actividad]"]').each(function() {
            $(this).rules('add', {
                required: true,
                number: true,
                min: 1,
                messages: {
                    required: "Obligatorio",
                    number: "Solo números",
                    min: "Mínimo 1"
                }
            });
        });

        // Validación para NOMBRE
        $('input[name*="[nombre_experiencia]"]').each(function() {
            $(this).rules('add', {
                required: true,
                messages: {
                    required: "El nombre es obligatorio"
                }
            });
        });
    }

    // Ejecutar al cargar la página
    aplicarReglasDinamicas();

    // 3. ACTUALIZAR REGLAS CUANDO SE AGREGA UNA FILA NUEVA (AJAX)
    // Dentro del éxito de tu llamada AJAX de #btn-add-actividad:
    // Ya lo tienes al final del success, pero asegúrate de que se llame:
    // $('#actividades-wrapper').append(response.html);
    // aplicarReglasDinamicas();