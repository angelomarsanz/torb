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
    FotoExperiencia
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
            'message' => 'Categoría guardada correctamente',
            'mensaje_usuario' => __('reda-alojamiento::messages.general.categoria_guardada_correctamente'),
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
            return response()->json([
                'success' => false,
                'message' => 'Configuración no encontrada',
                'code' => 404
            ], 404);
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
            'message' => 'Categoría actualizada correctamente',
            'mensaje_usuario' => __('reda-alojamiento::messages.general.categoria_actualizada_correctamente'),
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
            return response()->json([
                'success' => false,
                'message' => 'Configuración no encontrada',
                'code' => 404
            ], 404);
        }

        $dataJson = json_decode($setting->value, true);

        // Si existe la clave en las categorías, la eliminamos
        if (isset($dataJson['categorias'][$clave])) {
            unset($dataJson['categorias'][$clave]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'La categoría no existe',
                'mensaje_usuario' => __('reda-alojamiento::messages.general.la_categoria_no_existe'),
                'code' => 404
            ], 404);
        }

        // Guardamos el JSON actualizado
        DB::table('settings')->where('name', 'opciones_tipos_de_negocios')->update([
            'value' => json_encode($dataJson)
        ]);

        $respuesta = [
            'success' => true,
            'message' => 'Categoría eliminada correctamente',
            'mensaje_usuario' => __('reda-alojamiento::messages.general.categoria_eliminada_con_exito'),
            'respuesta' => '',
            'code' => 200
        ];

        Log::info("destroyOpcionTipoNegocio: " . print_r($respuesta, true));

        return response()->json($respuesta, $respuesta['code']);
    }
}
