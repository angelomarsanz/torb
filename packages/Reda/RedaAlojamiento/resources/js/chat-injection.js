import { iniciarChat } from './general/iniciarChat.js';

(function( $ ) {
    'use strict';

    console.log('REDA Chat Injection: VERSION 2.0 - Iniciando script en ' + window.location.href);

    function addChatButton(card) {
        if (card.querySelector('.reda-chat-btn')) return;

        // Try to find the property ID
        let propertyId = null;
        
        // 1. Check data-id on bookmark buttons (standard vRent)
        const bookmarkBtn = card.querySelector('.book_mark_change');
        if (bookmarkBtn) {
            propertyId = bookmarkBtn.getAttribute('data-id');
        }

        // 2. Fallback: Search in links or images
        if (!propertyId) {
            const links = card.querySelectorAll('a[href*="/properties/"]');
            for (let link of links) {
                const idFromAttr = link.getAttribute('data-id');
                if (idFromAttr) { 
                    propertyId = idFromAttr; 
                    break; 
                }
            }
        }

        if (!propertyId) return;

        // Find the container to append to
        const container = card.querySelector('.review-0') || 
                          card.querySelector('.card-body') || 
                          card;

        if (!container) return;

        const buttonWrapper = document.createElement('div');
        buttonWrapper.className = 'mt-2 reda-chat-btn';
        
        buttonWrapper.innerHTML = `
            <button type="button" class="btn-reda-chat-soft-v2 btn-reda-iniciar-chat" data-id="${propertyId}">
                <i class="far fa-paper-plane"></i> ${window.RedaAlojamientoJson["Enviar mensaje"] || "Enviar mensaje"}
            </button>
        `;

        container.appendChild(buttonWrapper);
    }

    function highlightActiveChat() {
        if (!window.location.pathname.includes('/inbox')) return;

        const urlParams = new URLSearchParams(window.location.search);
        const activeId = urlParams.get('id');
        if (!activeId) return;

        // Find all conversation items in sidebar
        const conversations = document.querySelectorAll('.conversassion');
        conversations.forEach(conv => {
            if (conv.getAttribute('data-id') === activeId) {
                conv.classList.add('active');
                // Ensure it's visible in scroll
                conv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                conv.classList.remove('active');
            }
        });
    }

    function scan() {
        const cardSelectors = '.card, .card-shadow, .card-1, .row.border.p-2.rounded-3, .col-md-6.col-lg-4.col-xl-3';
        const cards = document.querySelectorAll(cardSelectors);
        cards.forEach(addChatButton);

        highlightActiveChat();
    }

    function init() {
        scan();

        const observer = new MutationObserver(() => scan());
        observer.observe(document.body, { childList: true, subtree: true });

        setInterval(scan, 2000);

        // Event handler for initiating chat
        $(document).on('click', '.btn-reda-iniciar-chat', async function(e) {
            e.preventDefault();
            const id = $(this).data('id');

            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                window.RedaNotificaciones.esperar();
            }

            const respuesta = await iniciarChat(id);

            if (respuesta.success) {
                window.location.href = respuesta.respuesta;
            } else {
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.error === 'function') {
                    window.RedaNotificaciones.error(respuesta.mensaje_usuario);
                } else {
                    alert(respuesta.mensaje_usuario);
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(jQuery);
