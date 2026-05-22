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
            'code' => 400
        ];

        Log::info("storeOpcionTipoNegocio: " . print_r($respuesta, true));

        return response()->json($respuesta, $respuesta['code']);
    }
}
