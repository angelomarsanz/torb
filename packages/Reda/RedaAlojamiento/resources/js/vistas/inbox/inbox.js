"use strict";

(function($) {
    var ls = localStorage.getItem("selected_reda");
    var selected = false;
    var list = document.querySelectorAll(".list");
    var open = document.querySelector(".open a");

    function process() {
        if (ls != null && list[ls]) {
            selected = true;
            click(list[ls], ls);
        }
        if (!selected && list[0]) {
            click(list[0], 0);
        }

        list.forEach((l, i) => {
            l.addEventListener("click", function() {
                click(l, i);
            });
        });

        try {
            document.querySelector(".list.active").scrollIntoView(false);
        } catch (e) {}
    }

    function click(l, index) {
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

    if (open) {
        open.addEventListener("click", (e) => {
            const sidebar = document.querySelector("sidebar");
            sidebar.classList.toggle("opened");
            if (sidebar.classList.contains('opened'))
                e.target.innerText = "DOWN";
            else
                e.target.innerText = "UP";
        });
    }

    // --- RUTAS EXCLUSIVAS REDA ---
    $(document).on('click', '.conversassion', function() {
        var id = $(this).data('id');
        var dataURL = APP_URL + '/reda/messaging/booking'; // RUTA ÚNICA
        $.ajax({
            url: dataURL,
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
                // Auto scroll al final después de cargar
                setTimeout(() => {
                    const wrap = document.querySelector(".message-wrap");
                    if (wrap) wrap.scrollTop = wrap.scrollHeight;
                }, 100);
            }
        });
    });

    $(document).on('click', '.chat', function() {
        var msg = $('.cht_msg').val();
        if (!msg.trim()) return;

        var booking_id = $(this).data('booking');
        var receiver_id = $(this).data('receiver');
        var property_id = $(this).data('property');

        var result = '<div class="message-list me">' +
            '<div class="msg pl-2 pr-2 pb-2 pt-2 mb-2">' +
            '<p class="m-0">' + sanitize(msg) + '</p>' +
            '</div>' +
            '<div class="time">just now</div>' +
            '</div>';

        var dataURL = APP_URL + '/reda/messaging/reply'; // RUTA ÚNICA
        $.ajax({
            url: dataURL,
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
                $('.message-wrap').append(result);
                $('.cht_msg').val("");
                const wrap = document.querySelector(".message-wrap");
                if (wrap) wrap.scrollTop = wrap.scrollHeight;
            }
        });
    });

    $(document).on('keyup', '.cht_msg', function(event) {
        if (event.which === 13) {
            $('.chat').trigger("click");
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
