#!/bin/bash

# --- CONFIGURACIÓN DE SUBIDA PUNTUAL ---
# Lista aquí las rutas de TODOS los archivos específicos que quieres subir.
# Pueden ser del proyecto original o de los módulos REDA.
# Para no subir archivos, escribir la palabra "Ninguno".
# Ejemplo:
#   ARCHIVOS_PHP_PUNTUALES=(
#       "app/Http/Controllers/PaymentController.php"
#       "Ninguno"
#   )
ARCHIVOS_PHP_PUNTUALES=(
        #"Ninguno"
        "packages/Reda/RedaAlojamiento/resources/lang/es/messages.php"
        "packages/Reda/RedaAlojamiento/src/RedaAlojamientoServiceProvider.php"
        "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/actividades.blade.php"
        "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/partials/fila_actividad.blade.php"
)