(function( $ ) {
    "use strict";

    const containerId = '#calificacion_experiencia';

    if ($(containerId).length) {
        console.log('Script para Gestión de Material de Calificaciones cargado correctamente');

        $(function() {
            /**
             * Manejo del botón para generar y descargar SOLO el código QR.
             * Esta opción permite al usuario usar el QR en sus propios diseños.
             */
            $(document).on('click', '.btn-generar-qr', function(e) {
                e.preventDefault();
                
                const id = $(this).data('id');
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
             * Función para generar el QR en el cliente y disparar la descarga.
             * @param {string} url - URL que contendrá el QR.
             * @param {string} nombre - Nombre del negocio para el archivo.
             */
            function generarYDescargarQR(url, nombre) {
                // Creamos un elemento temporal oculto para generar el QR
                const tempDiv = document.createElement('div');
                
                // Inicializamos QRCode.js
                // Usamos una resolución alta (1000x1000) por si el usuario quiere imprimirlo grande
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
                        link.download = `QR_Individual_${nombre.replace(/\s+/g, '_')}.png`;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
                }, 200);
            }
        });
    }
})(jQuery);
