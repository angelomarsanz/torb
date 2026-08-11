import { iniciarChat } from './general/iniciarChat.js';

(function( $ ) {
    'use strict';

    console.log('REDA Chat Injection: Relocating to Property Single View');

    /**
     * Relocates "About the Host" sections below the map and injects the message button.
     * This fulfills the requirement of moving the "Enviar mensaje" button to the property single view
     * and placing both the host section and the button below the map.
     */
    function relocateHostSectionAndAddButton() {
        if (!window.location.pathname.includes('/properties/')) return;

        // Property ID extraction from hidden inputs standard in vRent
        const propertyId = document.getElementById('property_id')?.value || 
                          document.getElementById('hosting_id')?.value;
        
        if (!propertyId) return;

        // Map container parent (container-fluid-90)
        const mapSection = document.querySelector('#room-detail-map')?.closest('.container-fluid');
        if (!mapSection) return;

        // Search for "About the Host" sections (there are usually desktop and mobile versions)
        const allH2 = document.querySelectorAll('h2');
        
        allH2.forEach(h2 => {
            const text = h2.textContent.trim().toLowerCase();
            // Match "About the Host" or "Sobre el anfitrión"
            if (text.includes('about the host') || text.includes('sobre el anfitrión')) {
                const hostSection = h2.closest('.mt-5');
                if (hostSection && !hostSection.dataset.relocated) {
                    
                    // Identify if it's the desktop or mobile version to maintain visibility behavior
                    const isDesktop = hostSection.closest('.desktop') !== null;
                    const isMobile = hostSection.closest('.mobile') !== null;

                    // Create a wrapper that spans the full width below the map
                    const wrapper = document.createElement('div');
                    wrapper.className = 'container-fluid container-fluid-90 mt-5 reda-host-relocated-wrapper';
                    
                    // Maintain responsive visibility
                    if (isDesktop) {
                        wrapper.classList.add('desktop');
                        // Fallback in case the 'desktop' class isn't enough outside its original context
                        if (!wrapper.classList.contains('d-none')) wrapper.classList.add('d-none', 'd-md-block');
                    }
                    if (isMobile) {
                        wrapper.classList.add('mobile');
                        if (!wrapper.classList.contains('d-block')) wrapper.classList.add('d-block', 'd-md-none');
                    }

                    // Move host section into the new wrapper
                    wrapper.appendChild(hostSection);

                    // Inject "Send message" button if not already there
                    // We target the internal col-md-12 to maintain harmony with the host info padding
                    const targetContainer = hostSection.querySelector('.col-md-12') || hostSection;

                    if (!hostSection.querySelector('.reda-chat-btn')) {
                        const buttonWrapper = document.createElement('div');
                        buttonWrapper.className = 'mt-4 reda-chat-btn reda-chat-btn-relocated';
                        
                        const btnText = (window.RedaAlojamientoJson && window.RedaAlojamientoJson["Enviar mensaje"]) || "Enviar mensaje";
                        
                        buttonWrapper.innerHTML = `
                            <button type="button" class="btn-reda-chat-soft-v2 btn-reda-iniciar-chat" data-id="${propertyId}">
                                <i class="far fa-paper-plane"></i> ${btnText}
                            </button>
                        `;
                        targetContainer.appendChild(buttonWrapper);
                    }

                    // Place after map section
                    mapSection.after(wrapper);
                    hostSection.dataset.relocated = "true";
                }
            }
        });
    }

    /**
     * Highlights the active conversation in the inbox sidebar.
     */
    function highlightActiveChat() {
        if (!window.location.pathname.includes('/inbox')) return;

        const urlParams = new URLSearchParams(window.location.search);
        const activeId = urlParams.get('id');
        if (!activeId) return;

        const conversations = document.querySelectorAll('.conversassion');
        conversations.forEach(conv => {
            if (conv.getAttribute('data-id') === activeId) {
                conv.classList.add('active');
                conv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                conv.classList.remove('active');
            }
        });
    }

    function init() {
        // Execute relocation logic
        relocateHostSectionAndAddButton();
        highlightActiveChat();

        // Observe changes to handle dynamic loading or re-renders
        const observer = new MutationObserver(() => {
            relocateHostSectionAndAddButton();
            highlightActiveChat();
        });
        observer.observe(document.body, { childList: true, subtree: true });

        // Interval as safety fallback
        setInterval(relocateHostSectionAndAddButton, 3000);

        // Event handler for initiating chat (delegated to document for relocated buttons)
        $(document).on('click', '.btn-reda-iniciar-chat', async function(e) {
            e.preventDefault();
            const id = $(this).data('id');

            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                window.RedaNotificaciones.esperar();
            }

            const respuesta = await iniciarChat(id);

            if (respuesta.success) {
                window.location.href = respuesta.respuesta;
            } else if (respuesta.code === 401) {
                // Redirigimos a la ruta de iniciar chat para que el middleware guest() 
                // capture la intención y después del login nos traiga de vuelta aquí
                window.location.href = APP_URL + '/reda/pago/iniciar-chat/' + id;
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
