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
    #"packages/Reda/RedaAlojamiento/resources/sass/main.scss"
    #"packages/Reda/RedaAlojamiento/routes/web.php"
    #"packages/Reda/RedaAlojamiento/resources/lang/es/messages.php"
    #"webpack.mix.js"
    ".gitignore"

    "packages/Reda/RedaAlojamiento/src/Models/Experiencia/Experiencia.php"
    "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/index.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/indexExperiencias.js"
    "packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/ExperienciaController.php"

    #"packages/Reda/RedaAlojamiento/resources/js/general/main.js"
    #"packages/Reda/RedaAlojamiento/resources/js/general/menus/index.js"
    #packages/Reda/RedaAlojamiento/resources/js/general/menus/menuLateralUsuario.js"
)
