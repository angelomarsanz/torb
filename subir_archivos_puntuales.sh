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
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/menu_lateral.blade.php"
        #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/ActividadExperiencia.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/fotos.blade.php"
        "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/actividades.blade.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/ubicacion.blade.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/horario.blade.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/precio.blade.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/informacion_adicional.blade.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/anfitrion.blade.php"
        "packages/Reda/RedaAlojamiento/resources/js/general/media.js"
        #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/formularioDePasosExperiencias.js"
        "packages/Reda/RedaAlojamiento/routes/web.php"
        "packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/ExperienciaController.php"
        "packages/Reda/RedaAlojamiento/src/Http/Controllers/General/MediaController.php"
)