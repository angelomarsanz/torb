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
    #"packages/Reda/RedaAlojamiento/resources/sass/main.scss"
    #"packages/Reda/RedaAlojamiento/routes/web.php"
    "packages/Reda/RedaAlojamiento/resources/lang/es/messages.php"
    #"webpack.mix.js"
    #"packages/Reda/RedaAlojamiento/src/RedaAlojamientoServiceProvider.php"

    #"resources/views/admin/common/head.blade.php"
    #"resources/views/admin/common/foot.blade.php"

    #"packages/Reda/RedaAlojamiento/resources/sass/admin/main.scss"
    #"packages/Reda/RedaAlojamiento/resources/views/admin/general/main_head.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/admin/general/main_footer.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/admin/general/modal_notificaciones.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/js/admin/general/main.js"
    #"packages/Reda/RedaAlojamiento/resources/js/admin/general/menus/index.js"
    #"packages/Reda/RedaAlojamiento/resources/js/admin/general/menus/menuLateralAdmin.js"
    #"packages/Reda/RedaAlojamiento/resources/js/admin/general/notificaciones.js"

    "packages/Reda/RedaAlojamiento/resources/views/admin/experiencia/tipos_de_negocios/opciones_tipos_de_negocios.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/js/admin/vistas/experiencia/opcionesTipoDeNegocios.js"
    #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Admin/Experiencia/ExperienciaController.php"
)
