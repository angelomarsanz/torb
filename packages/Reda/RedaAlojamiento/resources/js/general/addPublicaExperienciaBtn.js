// resources/js/reda/vistas/ui/experiencias/addPublicaExperienciaBtn.js
// Script no invasivo para añadir el botón "Publica tu experiencia" al menú principal.
// Comentarios y nombres en español conforme a las directrices del proyecto.

$(function () {
    const selectorEnlaceReferencia = 'a[aria-label="property-create"]';
    const botonId = 'nav-publica-experiencia';
    // Usar `href` relativo y la misma clase de estilo que "List your Space".
    const botonHtml = `
        <div class="nav-item ml-2" id="${botonId}" data-role="added-by-reda">
            <a class="nav-link p-2" href="https://pruebas.redetronic.com/reda/crear-experiencia" aria-label="experiencia-create">
                <button class="btn vbtn-outline-success text-14 font-weight-700 p-0 mt-2 pl-4 pr-4">
                    <p class="p-3 mb-0">Publica tu experiencia</p>
                </button>
            </a>
        </div>
        `;

    // Inserta el botón evitando duplicados. Devuelve true si lo insertó.
    const insertarBoton = () => {
        if ($('#' + botonId).length) return true; // Ya existe

        const $enlace = $(selectorEnlaceReferencia);
        if (!$enlace.length) return false;

        // Preferimos insertar después del contenedor .nav-item que contiene el enlace
        const $navItem = $enlace.closest('.nav-item');
        if ($navItem.length) {
            $navItem.after(botonHtml);
            return true;
        }

        // Fallback: insertar después del enlace mismo
        $enlace.parent().after(botonHtml);
        return true;
    };

    // Intento inicial cuando el DOM está listo
    $(document).ready(function () {
        if (!insertarBoton()) {
            // Si no se insertó (menú renderizado después), observamos cambios en el body
            const observer = new MutationObserver((mutations, obs) => {
                if (insertarBoton()) obs.disconnect();
            });
            observer.observe(document.body, { childList: true, subtree: true });

            // Timeout por seguridad para desconectar observer si no se logra insertar
            setTimeout(() => {
                insertarBoton();
                observer.disconnect();
            }, 5000);
        }
    });
});
