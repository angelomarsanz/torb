/**
 * Lógica para el sistema de reservación en modal y botones flotantes
 * Vista: Detalle de Propiedad (property.single)
 */

(function( $ ) {
    "use strict";

    const PropertyDetailReservation = {
        config: {
            formId: '#booking_form',
            sidebarContainerId: '#booking-price',
            modalId: '#modalReservar',
            modalBodyId: '#modalReservarBody',
            saveBtnId: '#save_btn',
            floatingBtnContainerClass: 'reda-btn-flotante-reserva',
            hashTrigger: '#reservar'
        },

        init: function() {
            if (!$(this.config.formId).length) return;

            console.log('REDA Property Detail: Inicializando sistema de reserva en modal');
            
            this.setupUI();
            this.handleHash();
            this.bindEvents();
        },

        setupUI: function() {
            const self = this;
            const $form = $(this.config.formId);
            const $sidebar = $(this.config.sidebarContainerId);
            
            // 1. Ocultar sidebar original (agregando clase de SASS)
            $sidebar.closest('.card').parent().addClass('hide-booking-sidebar');
            $sidebar.addClass('d-none');

            // 2. Determinar texto del botón según tipo de reserva
            const isInstant = $('#booking_type').val() === 'instant';
            const btnText = isInstant 
                ? (window.RedaAlojamientoJson["Reservar"] || "Reservar")
                : (window.RedaAlojamientoJson["Solicitud para reservar"] || "Solicitud para reservar");

            // 3. Inyectar botón flotante
            const floatingBtnHtml = `
                <div class="${this.config.floatingBtnContainerClass}">
                    <a href="javascript:void(0)" class="btn-flotante-inner">
                        <i class="far fa-calendar-alt"></i>
                        <span>${btnText}</span>
                    </a>
                </div>
            `;
            $('body').append(floatingBtnHtml);
            // Evitamos fadeIn() por ser inyección de estilos inline. Usamos clase SASS.
            setTimeout(() => {
                $(`.${this.config.floatingBtnContainerClass}`).addClass('is-visible');
            }, 100);

            // 4. Personalizar el botón de envío (que estará dentro del modal)
            const $saveBtn = $(this.config.saveBtnId);
            const enviarText = window.RedaAlojamientoJson["Enviar"] || "Enviar";
            
            // Guardamos el contenido original por si acaso, pero forzamos "Enviar"
            $saveBtn.find('span:not(.display-off)').text(enviarText);
            $saveBtn.addClass('btn-enviar-latido');
        },

        openModal: function() {
            const $form = $(this.config.formId);
            const $modalBody = $(this.config.modalBodyId);
            const $modal = $(this.config.modalId);

            // Mover el formulario al modal si no está allí
            if (!$modalBody.find(this.config.formId).length) {
                $modalBody.append($form);
                // Solo removemos d-none. No usamos .show() para evitar estilos inline.
                $form.removeClass('d-none');
            }

            $modal.modal('show');
        },

        handleHash: function() {
            if (window.location.hash === this.config.hashTrigger) {
                // Pequeño delay para asegurar que todo esté cargado (daterangepicker, etc)
                setTimeout(() => {
                    this.openModal();
                }, 500);
            }
        },

        bindEvents: function() {
            const self = this;

            // Clic en botón flotante
            $(document).on('click', `.${this.config.floatingBtnContainerClass} a`, function(e) {
                e.preventDefault();
                self.openModal();
            });

            // Escuchar cambios de hash (por si el usuario hace clic en un link interno)
            $(window).on('hashchange', function() {
                self.handleHash();
            });
        }
    };

    $(function() {
        PropertyDetailReservation.init();
    });

})(jQuery);
