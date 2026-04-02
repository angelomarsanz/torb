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
        #"packages/Reda/RedaAlojamiento/database/migrations/2026_04_01_125540_add_categoria_negocio_to_experiencias_table.php"
        #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/Experiencia.php"
        #"packages/Reda/RedaAlojamiento/database/migrations/2026_04_01_135608_add_tipo_producto_servicio_to_actividades_experiencias_table.php"
        #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/ExperienciaController.php"
        #"packages/Reda/RedaAlojamiento/database/migrations/2026_04_02_110149_make_columns_nullable_in_actividades_experiencias.php"
        #"packages/Reda/RedaAlojamiento/database/migrations/2026_04_02_125219_change_experiencia_id_nullable_in_actividades_experiencias.php"
        #"packages/Reda/RedaAlojamiento/database/migrations/2026_04_02_131434_make_all_columns_nullable_in_experiencias_table.php"
        "packages/Reda/RedaAlojamiento/src/Models/Experiencia/ActividadExperiencia.php"
        "packages/Reda/RedaAlojamiento/resources/lang/es/messages.php"
        "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/partials/fila_actividad.blade.php"
        "packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/formularioDePasosExperiencias.js"
        "packages/Reda/RedaAlojamiento/resources/js/general/media.js"
        "packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/ExperienciaController.php"
)
