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
}
