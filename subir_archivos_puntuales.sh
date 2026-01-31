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

        "packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/ExperienciaController.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/descripcion.blade.php"
        "packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/formularios_de_pasos/formularioDePasos.js"
        "packages/Reda/RedaAlojamiento/resources/js/main.js"
        #"packages/Reda/RedaAlojamiento/routes/web.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/create.blade.php"
        #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/experiencias/createExperiencias.js"
        #packages/Reda/RedaAlojamiento/database/migrations/2026_01_27_000002_add_user_id_to_experiencias_table.php"
        #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/Experiencia.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/menu_lateral.blade.php"
        #"packages/Reda/RedaAlojamiento/src/RedaAlojamientoServiceProvider.php"
        #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/FotoExperiencia.php"
        #"packages/Reda/RedaAlojamiento/database/migrations/2026_01_27_223125_create_fotos_experiencias_table.php"
        "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/fotos.blade.php"
        #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/FotoExperiencia.php"
        "resources/views/common/foot.blade.php"
        "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/actividades.blade.php"
)