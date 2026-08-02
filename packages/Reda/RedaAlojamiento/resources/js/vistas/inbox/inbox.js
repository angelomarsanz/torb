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
    
    $(document).on('click', '.conversassion', function(e) {
        e.stopImmediatePropagation(); // Evita ejecución de scripts originales (vRent)
        
        var id = $(this).data('id');
        console.log('REDA Inbox: Cargando conversation ID:', id);
        
        $.ajax({
            url: APP_URL + '/reda/messaging/booking',
            data: {
                "_token": $('meta[name="csrf-token"]').attr('content'),
                'id': id,
            },
            type: 'post',
            dataType: 'json',
            success: function(data) {
                $('#msg-' + id).removeClass('text-success');
                $('#messages').empty().html(data['inbox']);
                $('#booking').empty().html(data['booking']);
                
                setTimeout(() => {
                    const wrap = document.querySelector(".message-wrap");
                    if (wrap) wrap.scrollTop = wrap.scrollHeight;
                }, 100);
            }
        });
    });

    $(document).on('click', '.chat', function(e) {
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

        $.ajax({
            url: APP_URL + '/reda/messaging/reply',
            data: {
                "_token": $('meta[name="csrf-token"]').attr('content'),
                'msg': msg,
                'booking_id': booking_id,
                'receiver_id': receiver_id,
                'property_id': property_id,
            },
            type: 'post',
            dataType: 'json',
            success: function(data) {
                if (data == 1) {
                    $('.message-wrap').append(result);
                    $('.cht_msg').val("");
                    const wrap = document.querySelector(".message-wrap");
                    if (wrap) wrap.scrollTop = wrap.scrollHeight;
                }
                isSending = false;
            },
            error: function() {
                isSending = false;
            }
        });
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
