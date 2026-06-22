(function( $ ) {
    "use strict";

    const containerId = '#calificacion_experiencia';

    if ($(containerId).length) {
        console.log('Script para Gestión de Material de Calificaciones cargado correctamente');

        $(function() {
            /**
             * Generar vistas previas de los códigos QR automáticamente al cargar.
             */
            $('.qrcode-preview').each(function() {
                const container = $(this);
                const url = container.data('url');
                
                // Limpiar por si acaso
                container.empty();

                // Generar QR para vista previa (tamaño pequeño)
                if (typeof QRCode !== 'undefined') {
                    new QRCode(this, {
                        text: url,
                        width: 150,
                        height: 150,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.M
                    });
                }
            });

            /**
             * Manejo del botón para generar y descargar el código QR individual.
             * Esta opción permite al usuario usar el QR en sus propios diseños.
             */
            $(document).on('click', '.btn-generar-qr', function(e) {
                e.preventDefault();
                
                const urlCalificar = $(this).data('url');
                const nombreNegocio = $(this).data('nombre');

                $('#modalGenerandoQR').modal('show');

                // Simulamos un pequeño delay para dar sensación de procesamiento
                setTimeout(() => {
                    generarYDescargarQR(urlCalificar, nombreNegocio);
                    $('#modalGenerandoQR').modal('hide');
                }, 1000);
            });

            /**
             * Manejo del botón para descargar el cartel (PDF) con código QR.
             * Como el listener global de notificaciones.js ahora ignora enlaces con 'no-esperar',
             * manejamos manualmente la visibilidad aquí para evitar que el modal quede bloqueado
             * permanentemente debido a que las descargas no disparan eventos de navegación.
             */
            $(document).on('click', '.btn-descargar-cartel', function() {
                // Mostramos el modal de espera global
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                    window.RedaNotificaciones.esperar();
                }

                /**
                 * Lo ocultamos después de un tiempo prudencial (ej. 6 segundos).
                 * Esto asegura que el usuario recupere el control incluso si la descarga 
                 * se inicia y la página nunca se recarga. El listener de 'focus' en 
                 * notificaciones.js también ayudará si el usuario regresa de ver el PDF.
                 */
                setTimeout(() => {
                    if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                        window.RedaNotificaciones.ocultar();
                    }
                }, 6000);
            });

            /**
             * Función para generar el QR en el cliente y disparar la descarga.
             * @param {string} url - URL que contendrá el QR.
             * @param {string} nombre - Nombre del negocio para el archivo.
             */
            function generarYDescargarQR(url, nombre) {
                // Creamos un elemento temporal oculto para generar el QR
                const tempDiv = document.createElement('div');
                
                // Inicializamos QRCode.js
                // Usamos una resolución alta (1000x1000) por si el usuario quiere imprimirlo grande
                if (typeof QRCode !== 'undefined') {
                    const qrcode = new QRCode(tempDiv, {
                        text: url,
                        width: 1000, 
                        height: 1000,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.H
                    });

                    // QRCode.js genera un canvas o img dentro del div
                    setTimeout(() => {
                        const canvas = tempDiv.querySelector('canvas');
                        if (canvas) {
                            const imgData = canvas.toDataURL("image/png");
                            const link = document.createElement('a');
                            link.href = imgData;
                            link.download = `QR_${nombre.replace(/\s+/g, '_')}.png`;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        }
                    }, 200);
                } else {
                    console.error('QRCode.js no está cargado');
                    const errorMsg = window.RedaAlojamientoJson["Error al generar QR"] || 'Error al generar QR. Intente recargar la página.';
                    if (typeof mostrarNotificacion === 'function') {
                        mostrarNotificacion(window.RedaAlojamientoJson["Error"] || "Error", errorMsg, 'error');
                    } else {
                        alert(errorMsg);
                    }
                }
            }
        });
    }
})(jQuery);
