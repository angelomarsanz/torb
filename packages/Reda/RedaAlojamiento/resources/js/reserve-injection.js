(function( $ ) {
    'use strict';

    console.log('REDA Reserve Injection: Iniciando script en ' + window.location.href);

    function addReserveButton(card) {
        if (card.querySelector('.reda-reserve-btn')) return;

        // Skip the main booking card on the single property page
        if (card.querySelector('#booking_form')) return;

        // Try to find the property ID and slug
        let propertyId = null;
        let propertySlug = null;
        
        // 1. Check data-id on bookmark buttons (standard vRent)
        const bookmarkBtn = card.querySelector('.book_mark_change');
        if (bookmarkBtn) {
            propertyId = bookmarkBtn.getAttribute('data-id');
        }

        // 2. Search for the slug in links
        const propertyLink = card.querySelector('a[href*="properties/"]');
        if (propertyLink) {
            const href = propertyLink.getAttribute('href');
            // Extract slug from href
            const parts = href.split('properties/');
            if (parts.length > 1) {
                propertySlug = parts[1].split('?')[0].split('#')[0];
            }
        }

        if (!propertySlug && !propertyId) return;

        // Find the container to append to
        const container = card.querySelector('.review-0') || 
                          card.querySelector('.card-body') || 
                          card;

        if (!container) return;

        const buttonWrapper = document.createElement('div');
        buttonWrapper.className = 'reda-reserve-btn';
        
        const targetUrl = propertySlug ? `${window.APP_URL}/properties/${propertySlug}#booking-price` : `${window.APP_URL}/payments/book/${propertyId}`;
        const buttonText = (window.RedaAlojamientoJson && window.RedaAlojamientoJson["Reservar"]) || "Reservar";

        buttonWrapper.innerHTML = `
            <a href="${targetUrl}" class="btn-reda-chat-soft-v2 reda-btn-reservar" style="text-decoration: none !important; background-color: #1dbf73 !important; color: #fff !important; border-color: #1dbf73 !important; box-shadow: 0 4px 6px rgba(29, 191, 115, 0.2) !important; padding: 8px 20px !important; font-weight: 700 !important; letter-spacing: 0.5px !important;">
                <i class="far fa-calendar-check" style="color: #fff !important; margin-right: 5px !important;"></i> ${buttonText}
            </a>
        `;

        container.appendChild(buttonWrapper);
    }

    function scan() {
        const cardSelectors = '.card, .card-shadow, .card-1, .row.border.p-2.rounded-3, .col-md-6.col-lg-4.col-xl-3';
        const cards = document.querySelectorAll(cardSelectors);
        cards.forEach(addReserveButton);
    }

    function init() {
        scan();

        const observer = new MutationObserver(() => scan());
        observer.observe(document.body, { childList: true, subtree: true });

        setInterval(scan, 2000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(jQuery);
