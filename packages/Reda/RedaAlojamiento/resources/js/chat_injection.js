(function() {
    'use strict';

    console.log('REDA Chat Injection: Iniciando script en ' + window.location.href);

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
        
        // Use relative URL to avoid origin issues
        const chatUrl = '/reda/pago/iniciar-chat/' + propertyId;
        
        buttonWrapper.innerHTML = `
            <a href="${chatUrl}" class="reda-chat-btn-link">
                <i class="far fa-paper-plane"></i> Enviar mensaje
            </a>
        `;

        container.appendChild(buttonWrapper);
        console.log('REDA Chat Injection: Botón inyectado en propiedad ID: ' + propertyId);
    }

    function highlightActiveChat() {
        if (!window.location.pathname.includes('/inbox')) return;

        const urlParams = new URLSearchParams(window.location.search);
        const activeId = urlParams.get('id');
        if (!activeId) return;

        console.log('REDA Chat Injection: Resaltando chat activo ID: ' + activeId);

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
        // Broad selectors to catch home cards, search results, and variants
        const cardSelectors = '.card, .card-shadow, .card-1, .row.border.p-2.rounded-3, .col-md-6.col-lg-4.col-xl-3';
        const cards = document.querySelectorAll(cardSelectors);
        cards.forEach(addChatButton);

        // Also check if we need to highlight chat
        highlightActiveChat();
    }

    function init() {
        console.log('REDA Chat Injection: DOM listo, iniciando escaneo...');
        scan();

        const observer = new MutationObserver(() => scan());
        observer.observe(document.body, { childList: true, subtree: true });

        // Absolute persistence interval
        setInterval(scan, 2000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
