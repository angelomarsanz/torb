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
            click(list[ls], ls);
        }
        if (!selected && list[0]) {
            click(list[0], 0);
        }

        list.forEach((l, i) => {
            $(l).on("click", function() {
                click(l, i);
            });
        });
    }

    function click(l, index) {
        list = document.querySelectorAll(".list");
        list.forEach(x => { x.classList.remove("active"); });
        if (l) {
            l.classList.add("active");
            const sidebar = document.querySelector("sidebar");
            if (sidebar) sidebar.classList.remove("opened");
            if (open) open.innerText = "UP";
            
            const wrap = document.querySelector(".message-wrap");
            if (wrap) wrap.scrollTop = wrap.scrollHeight;
            localStorage.setItem("selected_reda", index);
        }
    }

    $(document).on('click', '.open a', function(e) {
        const sidebar = document.querySelector("sidebar");
        if (sidebar) {
            sidebar.classList.toggle("opened");
            if (sidebar.classList.contains('opened'))
                $(this).text("DOWN");
            else
                $(this).text("UP");
        }
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
                const wrap = document.querySelector(".message-wrap");
                if (wrap) wrap.scrollTop = wrap.scrollHeight;
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

        var result = '<div class="message-list me">' +
            '<div class="msg pl-2 pr-2 pb-2 pt-2 mb-2">' +
            '<p class="m-0">' + sanitize(msg) + '</p>' +
            '</div>' +
            '<div class="time">just now</div>' +
            '</div>';

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
            $('.message-wrap').append(result);
            $('.cht_msg').val("");
            const wrap = document.querySelector(".message-wrap");
            if (wrap) wrap.scrollTop = wrap.scrollHeight;
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
