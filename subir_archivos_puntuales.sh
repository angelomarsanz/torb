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

        #Archivos modificados en el proyecto original

        #"composer.json"
        #"config/app.php"
        #"package.json"
        #"webpack.mix.js"
        #"subir.sh"
        #"compilar.sh"

        #"resources/views/common/head.blade.php"
        #"resources/views/common/foot.blade.php"
        #"app/Http/Middleware/RedirectIfAuthenticated.php"

        #Archivos del plugin Reda/RedaAlojamiento

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

        #"packages/Reda/RedaAlojamiento/config/reda-alojamiento.php"
        #"packages/Reda/RedaAlojamiento/composer.json"
        #"packages/Reda/RedaAlojamiento/src/Http/Middleware/CheckPluginAuth.php" 
        #"packages/Reda/RedaAlojamiento/src/RedaAlojamientoServiceProvider.php"

        #"packages/Reda/RedaAlojamiento/src/Models/Administrativo/Administrativo.php"
        #"packages/Reda/RedaAlojamiento/src/Models/BilleteraHuesped/BilleteraHuesped.php"
        #"packages/Reda/RedaAlojamiento/src/Models/Disputa/Disputa.php"

        #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/ActividadExperiencia.php"
        #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/AnfitrionExperiencia.php"        
        #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/Experiencia.php"
        #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/HorarioExperiencia.php"
        #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/InformacionExperiencia.php"
        #"packages/Reda/RedaAlojamiento/src/Models/Experiencia/ReservacionExperiencia.php"

        #"packages/Reda/RedaAlojamiento/resources/views/administrativo/administrativos/index.blade.php"
        #"packages/Reda/RedaAlojamiento/resources/views/billetera_huesped/billeteras_huespedes/index.blade.php"
        #"packages/Reda/RedaAlojamiento/resources/views/disputa/disputas/index.blade.php"
        
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/actividades_experiencias/index.blade.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/anfitriones_experiencias/index.blade.php"
        "packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/create.blade.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/experiencias/index.blade.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/horarios_experiencias/index.blade.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/informaciones_experiencias/index.blade.php"
        #"packages/Reda/RedaAlojamiento/resources/views/experiencia/reservaciones_experiencias/index.blade.php"

        #"packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/ui/addPublicaExperienciaBtn.js"

        #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Administrativo/AdministrativoController.php"
        #"packages/Reda/RedaAlojamiento/src/Http/Controllers/BilleteraHuesped/BilleteraHuespedController.php"
        #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Disputa/DisputaController.php"

        #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/ActividadExperienciaController.php"
        #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/AnfitrionExperienciaController.php"
        #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/ExperienciaController.php"
        #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/HorarioExperienciaController.php"
        #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/InformacionExperienciaController.php"
        #"packages/Reda/RedaAlojamiento/src/Http/Controllers/Experiencia/ReservacionExperienciaController.php"

        #"packages/Reda/RedaAlojamiento/routes/web.php"
)