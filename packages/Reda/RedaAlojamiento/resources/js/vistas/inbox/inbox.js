"use strict";

(function($) {
    // Flag global para evitar duplicidad de carga
    if (window.RedaInboxBound) return;
    window.RedaInboxBound = true;

    // NEUTRALIZACIÓN INMEDIATA (Document Level)
    $(document).off('click', '.conversassion');
    $(document).off('click', '.chat');
    $(document).off('keyup', '.cht_msg');

    var ls = localStorage.getItem("selected_reda");
    var selected = false;
    var list = [];
    var open = null;
    var isSending = false;

    /**
     * AJAX para cargar el detalle de un booking (Inbox).
     */
    const apiCargarBooking = (id) => {
        return new Promise((resolve) => {
            $.ajax({
                url: APP_URL + '/reda/messaging/booking',
                data: {
                    "_token": $('meta[name="csrf-token"]').attr('content'),
                    'id': id,
                },
                type: 'POST',
                dataType: 'json',
                success: (data) => resolve(data),
                error: (x) => {
                    let res = {}; try { res = JSON.parse(x.responseText); } catch (e) {}
                    resolve({
                        success: false,
                        mensaje_usuario: res.mensaje_usuario || (window.RedaAlojamientoJson["Error al cargar"] || "Error al cargar"),
                        code: x.status || 500
                    });
                }
            });
        });
    };

    /**
     * AJAX para responder un mensaje (Inbox).
     */
    const apiResponderMensaje = (params) => {
        return new Promise((resolve) => {
            $.ajax({
                url: APP_URL + '/reda/messaging/reply',
                data: {
                    "_token": $('meta[name="csrf-token"]').attr('content'),
                    ...params
                },
                type: 'POST',
                dataType: 'json',
                success: (data) => resolve(data),
                error: (x) => {
                    let res = {}; try { res = JSON.parse(x.responseText); } catch (e) {}
                    resolve({
                        success: false,
                        mensaje_usuario: res.mensaje_usuario || (window.RedaAlojamientoJson["Error al enviar mensaje"] || "Error al enviar mensaje"),
                        code: x.status || 500
                    });
                }
            });
        });
    };

    /**
     * Desplaza el chat al final y asegura visibilidad del input en móvil.
     */
    function scrollChatToBottom() {
        const wrap = document.querySelector(".message-wrap-reda");
        if (wrap) {
            wrap.scrollTop = wrap.scrollHeight;
        }
        
        // En móvil (WhatsApp Style), forzamos que el footer (input) sea visible
        if (window.innerWidth < 768) {
            const footer = document.querySelector(".message-footer");
            if (footer) {
                // Pequeño retardo para asegurar que el DOM se haya actualizado y el teclado (si está abierto) se considere
                setTimeout(() => {
                    footer.scrollIntoView({ behavior: 'smooth', block: 'end' });
                }, 150);
            }
        }
    }

    function process() {
        console.log('REDA Inbox: Vinculando eventos...');
        list = document.querySelectorAll(".list");
        open = document.querySelector(".open a");

        // Neutralización Directa
        $('.conversassion').off('click');
        $('.chat').off('click');
        $('.cht_msg').off('keyup');

        if (ls != null && list[ls]) {
            selected = true;
            // No activar vista de chat automáticamente en móvil al cargar (isManual = false)
            click(list[ls], ls, false);
        }
        if (!selected && list[0]) {
            click(list[0], 0, false);
        }

        list.forEach((l, i) => {
            $(l).on("click", function() {
                // Al hacer clic manualmente, sí activamos la vista de chat (isManual = true)
                click(l, i, true);
            });
        });
    }

    function click(l, index, isManual = true) {
        list = document.querySelectorAll(".list");
        list.forEach(x => { x.classList.remove("active"); });
        if (l) {
            l.classList.add("active");
            // WhatsApp Style: Mostrar el chat en móvil solo si es clic manual
            if (isManual && window.innerWidth < 768) {
                $('.reda-inbox-wrapper').addClass('chat-active');
            }
            
            const wrap = document.querySelector(".message-wrap-reda");
            if (wrap) scrollChatToBottom();
            localStorage.setItem("selected_reda", index);
        }
    }

    // Botón de regresar en móvil (WhatsApp Style)
    $(document).on('click', '.btn-back-to-list', function() {
        $('.reda-inbox-wrapper').removeClass('chat-active');
    });

    $(document).on('click', '.open a', function(e) {
        // Desactivado logic antigua UP/DOWN
        return false;
    });

    // --- MANEJO DE EVENTOS CON CONTROL DE PROPAGACIÓN ---
    
    $(document).on('click', '.conversassion', async function(e) {
        e.stopImmediatePropagation(); // Evita ejecución de scripts originales (vRent)
        
        var id = $(this).data('id');
        console.log('REDA Inbox: Cargando conversation ID:', id);

        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
            window.RedaNotificaciones.esperar();
        }
        
        const data = await apiCargarBooking(id);

        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
            window.RedaNotificaciones.ocultar();
        }

        if (data.success) {
            $('#msg-' + id).removeClass('text-success font-weight-bold');
            $('#messages').empty().html(data.respuesta.inbox);
            $('#booking').empty().html(data.respuesta.booking);
            
            setTimeout(() => {
                scrollChatToBottom();
            }, 100);
        } else {
            alert(data.mensaje_usuario);
        }
    });

    $(document).on('click', '.chat', async function(e) {
        e.stopImmediatePropagation();
        
        if (isSending) return; // Evita doble envío accidental
        
        var msg = $('.cht_msg').val();
        if (!msg || !msg.trim()) return;

        var booking_id = $(this).data('booking');
        var receiver_id = $(this).data('receiver');
        var property_id = $(this).data('property');

        isSending = true;
        console.log('REDA Inbox: Enviando mensaje...');

        // Usar la plantilla de Blade para el mensaje enviado
        const $temp = $('#reda-temp-msg-container').clone().removeClass('d-none').removeAttr('id');
        $temp.find('.msg_txt').text(msg);
        $temp.find('.msg_time').text(window.RedaAlojamientoJson["justo ahora"] || "justo ahora");
        
        // Poblar nombre y rol desde la variable global inyectada
        if (window.RedaCurrentUser) {
            $temp.find('.sender_name').text(window.RedaCurrentUser.name);
            $temp.find('.sender_role').text(window.RedaCurrentUser.role);
        }

        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
            window.RedaNotificaciones.esperar();
        }

        const data = await apiResponderMensaje({
            'msg': msg,
            'booking_id': booking_id,
            'receiver_id': receiver_id,
            'property_id': property_id,
        });

        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
            window.RedaNotificaciones.ocultar();
        }

        if (data.success && data.respuesta == 1) {
            $('.message-wrap-reda').append($temp);
            $('.cht_msg').val("");
            scrollChatToBottom();

            // Actualizar el último mensaje en el sidebar dinámicamente
            const $sidebarItem = $(`.conversassion[data-id="${booking_id}"]`);
            if ($sidebarItem.length) {
                const truncatedMsg = msg.length > 20 ? msg.substring(0, 20) + '...' : msg;
                const checkIcon = '<i class="fas fa-check mr-1"></i>';
                $sidebarItem.find('p.m-0.text-14').html(checkIcon + sanitize(truncatedMsg));
            }
        } else {
            alert(data.mensaje_usuario || (window.RedaAlojamientoJson["Error al enviar mensaje"] || "Error al enviar mensaje"));
        }
        
        isSending = false;
    });

    // Manejo de Enter muy específico para evitar burbujeo hacia otros scripts
    $(document).on('keyup', '.cht_msg', function(event) {
        if (event.which === 13) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            console.log('REDA Inbox: Enter detectado');
            $('.chat').first().trigger("click");
            return false;
        }
    });

    /**
     * AMPLIA LA FOTO DEL AVATAR (WHATSAPP STYLE)
     */
    $(document).on('click', '.reda-chat-avatar-container', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const $container = $(this);
        const propImg = $container.find('.reda-property-bg').attr('src');
        const guestImg = $container.find('.reda-user-avatar.guest').attr('src');
        const hostImg = $container.find('.reda-user-avatar.host').attr('src');

        // Poblar el overlay
        $('#zoom-prop-img').attr('src', propImg);
        $('#zoom-guest-img').attr('src', guestImg);
        $('#zoom-host-img').attr('src', hostImg);

        // Mostrar con animación
        $('#reda-chat-zoom-overlay').css('display', 'flex').addClass('active');
        $('body').css('overflow', 'hidden'); // Bloquear scroll
    });

    /**
     * EXPANDE/CONTRAE TEXTOS TRUNCADOS (NOMBRE PROPIEDAD)
     */
    $(document).on('click', '.reda-expandable-text', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).toggleClass('expanded');
    });

    // Cerrar el zoom
    $(document).on('click', '#reda-chat-zoom-overlay, #btn-close-reda-zoom', function(e) {
        if (e.target.id === 'reda-chat-zoom-overlay' || e.target.id === 'btn-close-reda-zoom' || $(e.target).hasClass('btn-close-zoom')) {
            $('#reda-chat-zoom-overlay').removeClass('active');
            setTimeout(() => {
                $('#reda-chat-zoom-overlay').hide();
                $('body').css('overflow', ''); // Restaurar scroll
            }, 300);
        }
    });

    function sanitize(string) {
        const symbols = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#x27;',
            "/": '&#x2F;',
        };
        const regex = /[&<>"'/]/ig;
        return string.replace(regex, (match) => (symbols[match]));
    }

    $(document).ready(function() {
        process();
    });

})(jQuery);
