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

        "webpack.mix.js"

        "resources/views/common/head.blade.php"
        "resources/views/common/foot.blade.php"

        "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/create.blade.php"
        "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/descripcion.blade.php"
        "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/fotos.blade.php"
        "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/actividades.blade.php"

        "packages/Reda/RedaAlojamiento/resources/js/general/main.js"
        "packages/Reda/RedaAlojamiento/resources/js/general/addPublicaExperienciaBtn.js"
        "packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/createExperiencias.js"
        "packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/formularioDePasosExperiencias.js"

)