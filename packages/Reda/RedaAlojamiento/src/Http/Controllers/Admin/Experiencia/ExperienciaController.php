<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Admin\Experiencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Currency;
use Reda\RedaAlojamiento\Models\Experiencia\{
    Experiencia,
    ActividadExperiencia,
    HorarioExperiencia,
    InformacionExperiencia,
    AnfitrionExperiencia,
    FotoExperiencia,
    PlanNegocio
};
use Auth;
use Illuminate\Support\Facades\File;
use Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExperienciaController extends Controller
{
    public function index(Request $request)
    {
        //
    }

    public function configuracionPlanes()
    {
        // Recuperar antigüedad
        $settingAntiguedad = DB::table('settings')->where('name', 'antiguedad_planes_destacados')->first();
        $configuracion = [
            'cantidad'      => 0,
            'unidad_tiempo' => 'Mes(es)'
        ];
        if ($settingAntiguedad && !empty($settingAntiguedad->value)) {
            $configuracion = json_decode($settingAntiguedad->value, true);
        }

        // Recuperar promedio calificaciones
        $settingPromedio = DB::table('settings')->where('name', 'promedio_calificaciones_planes_destacados')->first();
        $promedio_calificaciones = $settingPromedio->value ?? 0;

        return view('reda-alojamiento::admin.experiencia.configuracion_planes', compact('configuracion', 'promedio_calificaciones'));
    }

    public function storeConfiguracionPlanes(Request $request)
    {
        // Validación de campos obligatorios
        $request->validate([
            'cantidad'                => 'required|numeric|min:0',
            'unidad_tiempo'           => 'required|string',
            'promedio_calificaciones' => 'required|numeric|min:0|max:5',
        ]);

        // Guardar Antigüedad
        $dataAntiguedad = [
            'cantidad'      => $request->cantidad,
            'unidad_tiempo' => $request->unidad_tiempo
        ];
        DB::table('settings')->updateOrInsert(
            ['name' => 'antiguedad_planes_destacados'],
            ['value' => json_encode($dataAntiguedad)]
        );

        // Guardar Promedio Calificaciones
        DB::table('settings')->updateOrInsert(
            ['name' => 'promedio_calificaciones_planes_destacados'],
            ['value' => $request->promedio_calificaciones]
        );

        $respuesta = [
            'success'         => true,
            'message'         => __('Configuración de planes guardada correctamente'),
            'mensaje_usuario' => __('Configuración de planes guardada con éxito'),
            'respuesta'       => '',
            'code'            => 200
        ];

        Log::info("storeConfiguracionPlanes: " . print_r($respuesta, true));

        return response()->json($respuesta, $respuesta['code']);
    }

    // --- CRUD DE PLANES ---

    public function indexPlanes(Request $request)
    {
        $planes = PlanNegocio::orderBy('orden', 'asc')->paginate(10);
        
        $tablaHtml = view('reda-alojamiento::admin.experiencia.partials.tabla_planes', compact('planes'))->render();

        $respuesta = [
            'success'         => true,
            'message'         => __('Listado de planes recuperado'),
            'mensaje_usuario' => __('Listado de planes recuperado'),
            'respuesta'       => $tablaHtml,
            'code'            => 200
        ];

        Log::info("indexPlanes: " . print_r($respuesta, true));

        return response()->json($respuesta, $respuesta['code']);
    }

    public function getPlan($id)
    {
        $plan = PlanNegocio::find($id);

        if (!$plan) {
            $respuesta = [
                'success' => false,
                'message' => __('Plan no encontrado'),
                'mensaje_usuario' => __('Plan no encontrado'),
                'respuesta' => '',
                'code' => 404
            ];
            Log::error("getPlan (error): " . print_r($respuesta, true));
            return response()->json($respuesta, $respuesta['code']);
        }

        $respuesta = [
            'success'         => true,
            'message'         => __('Plan recuperado'),
            'mensaje_usuario' => __('Plan recuperado'),
            'respuesta'       => $plan,
            'code'            => 200
        ];

        Log::info("getPlan: " . print_r($respuesta, true));

        return response()->json($respuesta, $respuesta['code']);
    }

    public function storePlan(Request $request)
    {
        $request->validate([
            'nombre'               => 'required|string|max:255',
            'orden'                => 'required|integer|min:0',
            'planes_pago'          => 'required|array|min:1',
            'planes_pago.*.precio' => 'required|numeric|min:0',
            'planes_pago.*.moneda' => 'required|string',
            'planes_pago.*.lapso'  => 'required|string',
            'beneficios'           => 'required|array|min:1',
            'beneficios.*'         => 'required|string',
        ]);

        $plan = PlanNegocio::create([
            'nombre'      => $request->nombre,
            'planes_pago' => $request->planes_pago,
            'beneficios'  => $request->beneficios,
            'destacado'   => $request->has('destacado'),
            'estatus'     => $request->has('estatus'),
            'orden'       => $request->orden,
        ]);

        $respuesta = [
            'success'         => true,
            'message'         => __('Plan creado'),
            'mensaje_usuario' => __('Plan guardado con éxito'),
            'respuesta'       => $plan,
            'code'            => 200
        ];

        Log::info("storePlan: " . print_r($respuesta, true));

        return response()->json($respuesta, $respuesta['code']);
    }

    public function updatePlan(Request $request)
    {
        $request->validate([
            'id'                   => 'required|exists:planes_negocios,id',
            'nombre'               => 'required|string|max:255',
            'orden'                => 'required|integer|min:0',
            'planes_pago'          => 'required|array|min:1',
            'planes_pago.*.precio' => 'required|numeric|min:0',
            'planes_pago.*.moneda' => 'required|string',
            'planes_pago.*.lapso'  => 'required|string',
            'beneficios'           => 'required|array|min:1',
            'beneficios.*'         => 'required|string',
        ]);

        $plan = PlanNegocio::find($request->id);
        $plan->update([
            'nombre'      => $request->nombre,
            'planes_pago' => $request->planes_pago,
            'beneficios'  => $request->beneficios,
            'destacado'   => $request->has('destacado'),
            'estatus'     => $request->has('estatus'),
            'orden'       => $request->orden,
        ]);

        $respuesta = [
            'success'         => true,
            'message'         => __('Plan actualizado'),
            'mensaje_usuario' => __('Plan actualizado con éxito'),
            'respuesta'       => $plan,
            'code'            => 200
        ];

        Log::info("updatePlan: " . print_r($respuesta, true));

        return response()->json($respuesta, $respuesta['code']);
    }

    public function destroyPlan(Request $request, $id)
    {
        $plan = PlanNegocio::find($id);

        if (!$plan) {
            $respuesta = [
                'success' => false,
                'message' => __('Plan no encontrado'),
                'mensaje_usuario' => __('Plan no encontrado'),
                'respuesta' => '',
                'code' => 404
            ];
            Log::error("destroyPlan (error): " . print_r($respuesta, true));
            return response()->json($respuesta, $respuesta['code']);
        }

        // Se elimina el plan completo con todas sus opciones de pago y beneficios
        $plan->delete();

        $respuesta = [
            'success'         => true,
            'message'         => __('Plan eliminado'),
            'mensaje_usuario' => __('Plan eliminado con éxito'),
            'respuesta'       => '',
            'code'            => 200
        ];

        Log::info("destroyPlan: " . print_r($respuesta, true));

        return response()->json($respuesta, $respuesta['code']);
    }

    // --- FIN CRUD DE PLANES ---

    public function opcionesTiposDeNegocios()
    {
        // Buscamos en la tabla settings por el nombre del parámetro
        $setting = DB::table('settings')->where('name', 'opciones_tipos_de_negocios')->first();

        // Inicializamos el array por si no existe o está vacío
        $categorias = [];

        if ($setting && !empty($setting->value)) {
            // Decodificamos el JSON que viene de la base de datos
            $dataJson = json_decode($setting->value, true);
            $categorias = $dataJson['categorias'] ?? [];
            // Ordenamos alfabéticamente por la clave (key)
            ksort($categorias);
        }

        // Retornamos la vista pasando el listado de categorías
        return view('reda-alojamiento::admin.experiencia.tipos_de_negocios.opciones_tipos_de_negocios', compact('categorias'));
    }

    public function storeOpcionTipoNegocio(Request $request)
    {
        // Validación de campos obligatorios
        $request->validate([
            'clave'  => 'required|string',
            'nombre' => 'required|string',
        ]);

        // Buscamos el registro actual
        $setting = DB::table('settings')->where('name', 'opciones_tipos_de_negocios')->first();

        $dataJson = ['categorias' => []];

        if ($setting && !empty($setting->value)) {
            $dataJson = json_decode($setting->value, true);
        }

        // Agregamos la nueva categoría al array
        // Usamos la clave proporcionada para mantener la estructura: 'clave' => 'Descripción'
        $dataJson['categorias'][$request->clave] = $request->nombre;

        // Actualizamos o insertamos en la tabla settings
        DB::table('settings')->updateOrInsert(
            ['name' => 'opciones_tipos_de_negocios'],
            ['value' => json_encode($dataJson)]
        );

        $respuesta = [
            'success' => true,
            'message' => __('Categoría guardada correctamente'),
            'mensaje_usuario' => __('Categoría guardada correctamente'),
            'respuesta' => '',
            'code' => 200
        ];

        Log::info("storeOpcionTipoNegocio: " . print_r($respuesta, true));

        return response()->json($respuesta, $respuesta['code']);
    }

    public function updateOpcionTipoNegocio(Request $request)
    {
        // Validación de campos obligatorios
        $request->validate([
            'old_clave' => 'required|string',
            'clave'     => 'required|string',
            'nombre'    => 'required|string',
        ]);

        // Buscamos el registro actual
        $setting = DB::table('settings')->where('name', 'opciones_tipos_de_negocios')->first();

        if (!$setting || empty($setting->value)) {
            $respuesta = [
                'success' => false,
                'message' => __('Configuración no encontrada'),
                'mensaje_usuario' => __('Configuración no encontrada'),
                'respuesta' => '',
                'code' => 404
            ];
            Log::error("updateOpcionTipoNegocio (error): " . print_r($respuesta, true));
            return response()->json($respuesta, 404);
        }

        $dataJson = json_decode($setting->value, true);

        // Si la clave cambió, eliminamos la anterior
        if ($request->old_clave !== $request->clave) {
            if (isset($dataJson['categorias'][$request->old_clave])) {
                unset($dataJson['categorias'][$request->old_clave]);
            }
        }

        // Agregamos/Actualizamos la categoría
        $dataJson['categorias'][$request->clave] = $request->nombre;

        // Guardamos los cambios
        DB::table('settings')->where('name', 'opciones_tipos_de_negocios')->update([
            'value' => json_encode($dataJson)
        ]);

        $respuesta = [
            'success' => true,
            'message' => __('Categoría actualizada correctamente'),
            'mensaje_usuario' => __('Categoría actualizada correctamente'),
            'respuesta' => '',
            'code' => 200
        ];

        Log::info("updateOpcionTipoNegocio: " . print_r($respuesta, true));

        return response()->json($respuesta, $respuesta['code']);
    }

    public function destroyOpcionTipoNegocio($clave)
    {
        // Buscamos el registro actual
        $setting = DB::table('settings')->where('name', 'opciones_tipos_de_negocios')->first();

        if (!$setting || empty($setting->value)) {
            $respuesta = [
                'success' => false,
                'message' => __('Configuración no encontrada'),
                'mensaje_usuario' => __('Configuración no encontrada'),
                'respuesta' => '',
                'code' => 404
            ];
            Log::error("destroyOpcionTipoNegocio (error): " . print_r($respuesta, true));
            return response()->json($respuesta, 404);
        }

        $dataJson = json_decode($setting->value, true);

        // Si existe la clave en las categorías, la eliminamos
        if (isset($dataJson['categorias'][$clave])) {
            unset($dataJson['categorias'][$clave]);
        } else {
            $respuesta = [
                'success' => false,
                'message' => __('La categoría no existe'),
                'mensaje_usuario' => __('La categoría no existe'),
                'respuesta' => '',
                'code' => 404
            ];
            Log::error("destroyOpcionTipoNegocio (error - no existe): " . print_r($respuesta, true));
            return response()->json($respuesta, 404);
        }

        // Guardamos el JSON actualizado
        DB::table('settings')->where('name', 'opciones_tipos_de_negocios')->update([
            'value' => json_encode($dataJson)
        ]);

        $respuesta = [
            'success' => true,
            'message' => __('Categoría eliminada correctamente'),
            'mensaje_usuario' => __('Categoría eliminada correctamente'),
            'respuesta' => '',
            'code' => 200
        ];

        Log::info("destroyOpcionTipoNegocio: " . print_r($respuesta, true));

        return response()->json($respuesta, $respuesta['code']);
    }
}
