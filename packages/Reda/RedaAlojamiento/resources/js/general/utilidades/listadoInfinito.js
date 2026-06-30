/**
 * packages/Reda/RedaAlojamiento/resources/js/general/utilidades/listadoInfinito.js
 * Utilidad genérica para manejar listados con scroll infinito en modales.
 */

export const ListadoInfinito = {
    config: {
        modalId: '#modalListadoInfinito',
        dialogId: '#modalDialogInfinito',
        contentId: '#modalContentInfinito',
        contenedorId: '#contenedor_items_infinito',
        loaderId: '#loader_infinito',
        noMoreId: '#no_more_infinito',
        offset: 0,
        idNegocio: null,
        tipo: null,
        loading: false,
        noMore: false,
        urlBase: null,
        dialogClass: 'modal-xl', // Clase por defecto para el tamaño
        contentClass: 'modal-content-infinito-default', // Clase por defecto para el contenido
        extraData: {} // Para pasar filtros adicionales
    },

    /**
     * Inicializa y abre el modal con el primer set de datos.
     */
    async iniciar(options) {
        this.config = { ...this.config, ...options, offset: 0, noMore: false, loading: false };
        
        // Ajustar clases del modal
        $(this.config.dialogId).removeClass('modal-sm modal-md modal-lg modal-xl').addClass(this.config.dialogClass);
        $(this.config.contentId).attr('class', 'modal-content border-0 shadow-lg ' + this.config.contentClass);

        $(this.config.modalId).modal('show');
        $('#tituloModalInfinito').text(this.config.tituloModal);
        $(this.config.contenedorId).empty();
        $(this.config.noMoreId).hide();
        
        await this.cargarSiguientes();
        this.initScrollListener();
    },

    /**
     * Carga el siguiente bloque de 10 elementos.
     */
    async cargarSiguientes() {
        if (this.config.loading || this.config.noMore) return;

        this.config.loading = true;
        
        // Para el scroll infinito usamos el loader local discreto si ya hay items,
        // pero si es la primera carga o según mandato usamos la animación global.
        const esPrimeraCarga = (this.config.offset === 0);
        if (esPrimeraCarga) {
            window.RedaNotificaciones.esperar();
        } else {
            $(this.config.loaderId).fadeIn(200);
        }

        try {
            const response = await this.peticionAjax();
            
            if (esPrimeraCarga) window.RedaNotificaciones.ocultar();

            if (response.success) {
                const data = response.respuesta;
                // Agregar los items al contenedor (vista de tarjetas)
                $(this.config.contenedorId).append(data.html_modal || data.html);
                this.config.offset = data.proximo_offset;
                
                if (data.cantidad < 10) {
                    this.config.noMore = true;
                    $(this.config.noMoreId).fadeIn(300);
                }
            } else {
                this.config.noMore = true;
                $(this.config.noMoreId).fadeIn(300);
                
                // Notificar error
                window.RedaNotificaciones.notificar(
                    window.RedaAlojamientoJson['Error'] || 'Error',
                    response.mensaje_usuario,
                    'error'
                );
            }
        } catch (error) {
            if (esPrimeraCarga) window.RedaNotificaciones.ocultar();
            console.error('Error cargando listado infinito:', error);
        } finally {
            this.config.loading = false;
            $(this.config.loaderId).hide();
        }
    },

    /**
     * Realiza la llamada Ajax.
     */
    peticionAjax() {
        const data = {
            offset: this.config.offset,
            tipo: this.config.tipo,
            es_modal: true,
            ...this.config.extraData
        };

        return new Promise((resolve) => {
            $.ajax({
                url: this.config.urlBase,
                type: 'GET',
                data: data,
                dataType: 'json',
                success: (data) => resolve(data),
                error: function (x, xs, xt) {
                    let respuestaServidor = {};
                    try {
                        respuestaServidor = JSON.parse(x.responseText);
                    } catch (e) {
                        respuestaServidor = {};
                    }

                    const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                    const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                    let respuesta = {
                        'success': false,
                        'message' : window.RedaAlojamientoJson["Error cargando items"] || 'Error cargando items',
                        'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                        'respuesta': respuestaServidor.respuesta || '',
                        'code': x.status !== 0 ? x.status : 504,
                    };
                    resolve(respuesta);
                }
            });
        });
    },

    /**
     * Listener para detectar cuando el usuario llega al final del scroll del modal.
     */
    initScrollListener() {
        const self = this;
        const $body = $('#bodyModalInfinito');

        $body.off('scroll').on('scroll', function() {
            const scrollHeight = $(this)[0].scrollHeight;
            const scrollTop = $(this).scrollTop();
            const clientHeight = $(this).innerHeight();

            // Si el usuario está a menos de 50px del final
            if (scrollTop + clientHeight >= scrollHeight - 50) {
                self.cargarSiguientes();
            }
        });
    }
};
