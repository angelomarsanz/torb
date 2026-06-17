/**
 * packages/Reda/RedaAlojamiento/resources/js/general/utilidades/listadoInfinito.js
 * Utilidad genérica para manejar listados con scroll infinito en modales.
 */

export const ListadoInfinito = {
    config: {
        modalId: '#modalListadoInfinito',
        contenedorId: '#contenedor_items_infinito',
        loaderId: '#loader_infinito',
        noMoreId: '#no_more_infinito',
        offset: 0,
        idNegocio: null,
        tipo: null,
        loading: false,
        noMore: false,
        urlBase: null
    },

    /**
     * Inicializa y abre el modal con el primer set de datos.
     */
    async iniciar(options) {
        this.config = { ...this.config, ...options, offset: 0, noMore: false, loading: false };
        
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
        $(this.config.loaderId).fadeIn(200);

        try {
            const response = await this.peticionAjax();
            if (response.success && response.cantidad > 0) {
                // Agregar los items al contenedor (vista de tarjetas)
                $(this.config.contenedorId).append(response.html_modal || response.html);
                this.config.offset = response.proximo_offset;
                
                if (response.cantidad < 10) {
                    this.config.noMore = true;
                    $(this.config.noMoreId).fadeIn(300);
                }
            } else {
                this.config.noMore = true;
                $(this.config.noMoreId).fadeIn(300);
            }
        } catch (error) {
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
        return new Promise((resolve, reject) => {
            $.ajax({
                url: this.config.urlBase,
                type: 'GET',
                data: {
                    offset: this.config.offset,
                    tipo: this.config.tipo,
                    es_modal: true
                },
                dataType: 'json',
                success: (data) => resolve(data),
                error: (err) => reject(error)
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
