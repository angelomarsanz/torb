#!/bin/bash

# --- CONFIGURACIÓN DE SUBIDA PUNTUAL ---
# Lista aquí las rutas de TODOS los archivos específicos que quieres subir.
# Pueden ser del proyecto original o de los módulos REDA.
# Para no subir archivos, escribir la palabra "Ninguno".
ARCHIVOS_PHP_PUNTUALES=(
    #"config/pdf.php"
    #"webpack.mix.js"
    #"packages/Reda/RedaAlojamiento/resources/lang/es.json"
    #"packages/Reda/RedaAlojamiento/resources/lang/es/messages.php"
    "packages/Reda/RedaAlojamiento/routes/web.php"

    #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/CalificacionExperiencia.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_06_08_100000_crear_tabla_calificaciones_experiencias.php"

    #"packages/Reda/RedaAlojamiento/resources/views/admin/experiencia/tipos_de_negocios/opciones_tipos_de_negocios.blade.php"

    "packages/Reda/RedaAlojamiento/resources/sass/main.scss"

    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/index.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/create.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/calificacion_experiencia.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/cartel_calificacion_pdf.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/listado_calificaciones.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/menu_lateral.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/descripcion.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/fotos.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/actividades.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/ubicacion.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/horario.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/anfitrion.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/informacion_adicional.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/precio.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/partials/lista_cards.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/calificacion_experiencia_frontend.blade.php"
    "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/listado_productos_servicios.blade.php"
    "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/partials/card_producto_servicio.blade.php"

    #"packages/Reda/RedaAlojamiento/resources/js/admin/general/menus/menuLateralAdmin.js"
    #"packages/Reda/RedaAlojamiento/resources/js/admin/vistas/experiencia/opcionesTipoDeNegocios.js"
    #"packages/Reda/RedaAlojamiento/resources/js/general/menus/addPublicaExperienciaBtn.js"
    #"packages/Reda/RedaAlojamiento/resources/js/general/menus/menuLateralUsuario.js"
    #"packages/Reda/RedaAlojamiento/resources/js/general/menus/menuPrincipal.js"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/indexExperiencias.js"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/createExperiencias.js"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/eliminarExperiencia.js"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/formularioDePasosExperiencias.js"
    "packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/listadoProductosServicios.js"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/calificacionExperienciaFrontend.js"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/calificacionExperiencia.js"

    "packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/ExperienciaController.php"
    #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/CalificacionController.php"
)
