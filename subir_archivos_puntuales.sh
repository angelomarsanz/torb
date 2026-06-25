#!/bin/bash

# --- CONFIGURACIÓN DE SUBIDA PUNTUAL ---
# Lista aquí las rutas de TODOS los archivos específicos que quieres subir.
# Pueden ser del proyecto original o de los módulos REDA.
# Para no subir archivos, escribir la palabra "Ninguno".
ARCHIVOS_PHP_PUNTUALES=(
    # --- ARCHIVOS DEL PROYECTO ORIGINAL MODIFICADOS ---
    #"composer.json"
    #"config/app.php"
    #"config/pdf.php"
    #"package.json"
    #"webpack.mix.js"
    #"resources/views/admin/common/head.blade.php"
    #"resources/views/admin/common/foot.blade.php"
    #"resources/views/common/head.blade.php"
    #"resources/views/common/foot.blade.php"
    #"app/Http/Middleware/RedirectIfAuthenticated.php"

    # --- PLUGIN REDA ALOJAMIENTO: GENERAL ---
    #"packages/Reda/RedaAlojamiento/composer.json"
    #"packages/Reda/RedaAlojamiento/config/reda-alojamiento.php"
    "packages/Reda/RedaAlojamiento/resources/lang/es.json"
    "packages/Reda/RedaAlojamiento/resources/lang/es/messages.php"
    "packages/Reda/RedaAlojamiento/routes/web.php"
    #"packages/Reda/RedaAlojamiento/src/RedaAlojamientoServiceProvider.php"

    #"packages/Reda/RedaAlojamiento/src/Helpers/helpers.php"
    #"packages/Reda/RedaAlojamiento/resources/sass/main.scss"
    "packages/Reda/RedaAlojamiento/resources/sass/admin/main.scss"

    # --- PLUGIN REDA ALOJAMIENTO: MODELOS ---
    #"packages/Reda/RedaAlojamiento/src/Models/Administrativo/Administrativo.php"
    #"packages/Reda/RedaAlojamiento/src/Models/BilleteraHuesped/BilleteraHuesped.php"
    #"packages/Reda/RedaAlojamiento/src/Models/Disputa/Disputa.php"
    #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/ActividadExperiencia.php"
    #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/AnfitrionExperiencia.php"
    #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/CalificacionExperiencia.php"
    #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/Experiencia.php"
    #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/FotoExperiencia.php"
    #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/HorarioExperiencia.php"
    #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/InformacionExperiencia.php"
    #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/ReservacionExperiencia.php"
    "packages/Reda/RedaAlojamiento/src/Models/Admin/SoporteTecnico.php"

    # --- PLUGIN REDA ALOJAMIENTO: MIGRACIONES ---
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_10_26_225134_create_actividades_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_10_26_225134_create_horarios_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_11_02_100000_remove_fields_from_horarios_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_11_04_000000_create_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_11_04_000001_update_actividades_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_11_04_000002_update_horarios_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_11_08_120000_remove_cupos_from_horarios_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_11_15_000001_create_informacion_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_11_16_173000_create_reservacion_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_11_17_000000_create_anfitrion_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_11_17_000000_update_reservacion_experiencias_add_fk_user.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_11_17_000001_fix_anfitrion_experiencias_add_fk_user.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_11_17_000001_update_reservacion_experiencias_add_fk_horario.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_12_12_000000_rename_anfitrion_experiencias_to_anfitriones_experiencias.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_12_12_000001_rename_informacion_experiencias_to_informaciones_experiencias.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_12_12_000002_rename_reservacion_experiencias_to_reservaciones_experiencias.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_12_12_000003_fix_anfitriones_experiencias_add_fk_user.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_12_12_000004_fix_informaciones_experiencias_add_fk_experiencia.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2025_12_12_000005_fix_reservaciones_experiencias_add_fks.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_01_27_000000_add_experiencia_id_to_anfitriones_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_01_27_000002_add_user_id_to_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_01_27_223125_create_fotos_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_02_05_233110_add_orden_actividad_to_actividades_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_02_24_223956_add_columns_to_actividades_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_03_08_202105_rename_nombre_experiencia_to_nombre_actividad_in_actividades_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_04_01_125540_add_categoria_negocio_to_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_04_01_135608_add_tipo_producto_servicio_to_actividades_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_04_02_110149_make_columns_nullable_in_actividades_experiencias.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_04_02_125219_change_experiencia_id_nullable_in_actividades_experiencias.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_04_02_131434_make_all_columns_nullable_in_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_05_26_080651_agregar_campos_a_actividades_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_05_26_091600_agregar_horario_a_tabla_experiencias.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_05_26_120903_cambios_columnas_precio_moneda.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_05_26_130425_mover_precios_monedas_complementarios.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_05_28_000000_agregar_ubicacion_a_experiencias_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_06_01_100000_agregar_foto_anfitrion_a_anfitriones_experiencias.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_06_08_100000_crear_tabla_calificaciones_experiencias.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_06_22_000000_create_soportes_tecnicos_table.php"
    #"packages/Reda/RedaAlojamiento/database/migrations/2026_06_24_100000_cambiar_link_error_a_text_en_soportes_tecnicos.php"

    # --- PLUGIN REDA ALOJAMIENTO: VISTAS (ADMIN) ---
    #"packages/Reda/RedaAlojamiento/resources/views/admin/experiencia/tipos_de_negocios/opciones_tipos_de_negocios.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/admin/general/main_footer.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/admin/general/main_head.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/admin/general/modal_confirmacion.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/admin/general/modal_notificaciones.blade.php"
    "packages/Reda/RedaAlojamiento/resources/views/admin/general/soporte_tecnico/index.blade.php"
    "packages/Reda/RedaAlojamiento/resources/views/admin/general/soporte_tecnico/show.blade.php"

    # --- PLUGIN REDA ALOJAMIENTO: VISTAS (FRONTEND - CARPETAS FRONTEND) ---
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/calificacion_experiencia_frontend.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/listado_experiencias.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/listado_productos_servicios.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/partials/card_negocio.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/partials/card_ver_todos_negocios.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/frontend/partials/card_reseña.blade.php"

    # --- PLUGIN REDA ALOJAMIENTO: VISTAS (FUERA DE CARPETAS FRONTEND) ---
    #"packages/Reda/RedaAlojamiento/resources/views/general/main_footer.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/general/main_head.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/general/modal_confirmacion.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/general/modal_crop.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/general/modal_notificaciones.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/general/modal_listado_infinito.blade.php"

    #"packages/Reda/RedaAlojamiento/resources/views/administrativo/administrativos/index.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/billetera_huesped/billeteras_huespedes/index.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/disputa/disputas/index.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/calificacion_experiencia.blade.php"
    #packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/cartel_calificacion_pdf.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/create.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/index.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/listado_calificaciones.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/detalle_calificaciones.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/actividades.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/anfitrion.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/descripcion.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/fotos.blade.php"
    #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/formularios_de_pasos/horario.blade.php"

    # --- Archivos Javascript admin ---
    #"packages/Reda/RedaAlojamiento/resources/js/admin/general/menus/menuLateralAdmin.js"
    "packages/Reda/RedaAlojamiento/resources/js/admin/general/soporte_tecnico/indexSoporteTecnico.js"
    "packages/Reda/RedaAlojamiento/resources/js/admin/general/soporte_tecnico/showSoporteTecnico.js"

    # --- Archivos Javascript en la carpeta frontend ---
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/listadoProductosServicios.js"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/listadoExperiencias.js"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/calificacionExperienciaFrontend.js"

    # --- Archivos Javascript fuera de la carpeta frontend ---
    #"packages/Reda/RedaAlojamiento/resources/js/general/main.js"
    #"packages/Reda/RedaAlojamiento/resources/js/general/menus/menuPrincipal.js"
    #"packages/Reda/RedaAlojamiento/resources/js/general/menus/menuLateralUsuario.js"
    #"packages/Reda/RedaAlojamiento/resources/js/general/utilidades/listadoInfinito.js"
    #"packages/Reda/RedaAlojamiento/resources/js/general/notificaciones.js"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/formularioDePasosExperiencias.js"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/indexExperiencias.js"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/calificacionExperiencia.js"
    #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/detalleCalificaciones.js"

    # --- PLUGIN REDA ALOJAMIENTO: CONTROLADORES admin ---
    #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Admin/Experiencia/ExperienciaController.php"
    "packages/Reda/RedaAlojamiento/src/Http/Controllers/Admin/General/SoporteTecnicoController.php"

    # --- PLUGIN REDA ALOJAMIENTO: CONTROLADORES ---
    #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Administrativo/AdministrativoController.php"
    #"packages/Reda/RedaAlojamiento/src/Http/Controllers/BilleteraHuesped/BilleteraHuespedController.php"
    #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Disputa/DisputaController.php"
    #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/CalificacionController.php"
    #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/ExperienciaController.php"
    #"packages/Reda/RedaAlojamiento/src/Http/Controllers/General/MediaController.php"
    #"packages/Reda/RedaAlojamiento/src/Http/Middleware/CheckPluginAuth.php"
    "packages/Reda/RedaAlojamiento/src/Http/Controllers/General/SoporteTecnicoController.php"
)
