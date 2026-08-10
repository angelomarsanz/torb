// resources/js/reda/vistas/ui/experiencias/addPublicaExperienciaBtn.js
// Script no invasivo para añadir el botón "Publica tu experiencia" al menú principal.
// Comentarios y nombres en español conforme a las directrices del proyecto.

export const addPublicaExperienciaBtn = () => {
    (function( $ ) {
        "use strict";

        const botonIdEscritorio = 'nav-publica-experiencia';
        const botonIdMovil = 'nav-publica-experiencia-movil';
        const textoBoton = window.RedaAlojamiento?.general?.publica_tu_negocio || "Publica tu negocio";
        const urlCrearExperiencia = APP_URL + '/reda/negocios/crear-experiencia';

        // HTML para la versión de escritorio
        const botonHtmlEscritorio = `
            <div class="nav-item ml-2" id="${botonIdEscritorio}" data-role="added-by-reda">
                <a class="nav-link p-2" href="${urlCrearExperiencia}" aria-label="experiencia-create">
                    <button class="btn vbtn-outline-success text-14 font-weight-700 btn-menu-reda-compact">
                        <p>${textoBoton}</p>
                    </button>
                </a>
            </div>
            `;

        // HTML para la versión móvil (dentro del modal)
        const botonHtmlMovil = `
            <li id="${botonIdMovil}" data-role="added-by-reda" class="mt-3">
                <a href="${urlCrearExperiencia}">
                    <button class="btn vbtn-outline-success text-14 font-weight-700 btn-menu-reda-compact">
                        ${textoBoton}
                    </button>
                </a>
            </li>
        `;

        // Inserta el botón de escritorio
        const insertarBotonEscritorio = () => {
            if ($('#' + botonIdEscritorio).length) return true;

            const $enlace = $('a[aria-label="property-create"]');
            if (!$enlace.length) return false;

            // Compactar el botón original "Publica tu alojamiento"
            const $botonOriginal = $enlace.find('button');
            if ($botonOriginal.length && !$botonOriginal.hasClass('btn-menu-reda-compact')) {
                $botonOriginal.removeClass('p-0 mt-2 pl-4 pr-4').addClass('btn-menu-reda-compact');
                $botonOriginal.find('p').removeClass('p-3 mb-0');
            }

            const $navItem = $enlace.closest('.nav-item');
            if ($navItem.length) {
                $navItem.after(botonHtmlEscritorio);
                return true;
            }

            $enlace.parent().after(botonHtmlEscritorio);
            return true;
        };

        // Inserta el botón móvil (en el modal)
        const insertarBotonMovil = () => {
            if ($('#' + botonIdMovil).length) return true;

            // En el modal el botón de "List your space" está al final de ul.mobile-side
            const $listaMovil = $('#left_modal .mobile-side');
            if (!$listaMovil.length) return false;

            // Buscamos el enlace de "List your space" en el móvil para poner el nuestro después
            const $enlaceReferenciaMovil = $listaMovil.find('a[href*="property/create"]');
            if ($enlaceReferenciaMovil.length) {
                // Compactar el botón original móvil "Publica tu alojamiento"
                const $botonOriginalMovil = $enlaceReferenciaMovil.find('button');
                if ($botonOriginalMovil.length && !$botonOriginalMovil.hasClass('btn-menu-reda-compact')) {
                    $botonOriginalMovil.removeClass('pl-5 pr-5 pt-3 pb-3').addClass('btn-menu-reda-compact');
                }

                $enlaceReferenciaMovil.parent().after(botonHtmlMovil);
                return true;
            }

            // Fallback: al final de la lista
            $listaMovil.append(botonHtmlMovil);
            return true;
        };

        const ejecutarInserciones = () => {
            const escritorioStatus = insertarBotonEscritorio();
            const movilStatus = insertarBotonMovil();
            return { escritorioStatus, movilStatus };
        };

        // Intento inicial cuando el DOM está listo
        $(function () {
            const { escritorioStatus, movilStatus } = ejecutarInserciones();

            // Si alguno de los dos no se pudo insertar, usamos un observer
            if (!escritorioStatus || !movilStatus) {
                const observer = new MutationObserver((mutations, obs) => {
                    const status = ejecutarInserciones();
                    if (status.escritorioStatus && status.movilStatus) {
                        obs.disconnect();
                    }
                });

                observer.observe(document.body, { childList: true, subtree: true });

                // Timeout de seguridad
                setTimeout(() => {
                    ejecutarInserciones();
                    observer.disconnect();
                }, 5000);
            }
        });
    })(jQuery);
}

addPublicaExperienciaBtn();
