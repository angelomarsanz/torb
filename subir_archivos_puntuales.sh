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
    #".github/copilot-instructions.md"

    #"resources/views/admin/common/head.blade.php"
    #"resources/views/admin/common/foot.blade.php"

    #"resources/views/common/head.blade.php"
    #"resources/views/common/header.blade.php"
    #"resources/views/common/foot.blade.php"
    #"resources/views/home/home.blade.php"

    "packages/Reda/RedaAlojamiento/resources/lang/es.json"
    "packages/Reda/RedaAlojamiento/resources/sass/main.scss"
    "packages/Reda/RedaAlojamiento/routes/web.php"
    "webpack.mix.js"

    "packages/Reda/RedaAlojamiento/src/Models/Experiencia/Experiencia.php"
    "packages/Reda/RedaAlojamiento/src/Models/Experiencia/CalificacionExperiencia.php"
    "packages/Reda/RedaAlojamiento/database/migrations/2026_06_08_100000_crear_tabla_calificaciones_experiencias.php"

    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/listado_experiencias.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/partials/lista_cards.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/listado_productos_servicios.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/listadoProductosServicios.js"
    "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/calificacion_experiencia.blade.php"
    "packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/calificacionExperiencia.js"

    #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/ExperienciaController.php"
    #"packages/Reda/RedaAlojamiento/src/Http/Controllers/General/MediaController.php"
    "packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/CalificacionController.php"
)
