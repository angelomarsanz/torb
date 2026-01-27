<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Experiencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Reda\RedaAlojamiento\Models\Experiencia\Experiencia;
use Auth;

class ExperienciaController extends Controller
{
    public function index()
    {
        return view('reda-alojamiento::experiencia.experiencias.index');
    }

    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            // Validación
            $request->validate([
                'titulo' => 'required|max:255',
                'precio_persona' => 'required|numeric',
                'latitud_encuentro' => 'required',
                'longitud_encuentro' => 'required',
            ]);

            // Creación inicial del modelo
            $experiencia = new Experiencia;
            $experiencia->titulo = $request->titulo;
            $experiencia->tipo_moneda = $request->tipo_moneda;
            $experiencia->precio_persona = $request->precio_persona;
            $experiencia->latitud_encuentro = $request->latitud_encuentro;
            $experiencia->longitud_encuentro = $request->longitud_encuentro;
            // Asignar el host (asumiendo que hay una columna host_id o similar)
            $experiencia->user_id = Auth::id(); 
            
            $experiencia->save();

            return redirect()->route('reda.experiencias.pasos', [
                'id' => $experiencia->id, 
                'paso' => 'fotos'
            ]);
        }

        return view('reda-alojamiento::experiencia.experiencias.create');   
    }
    public function formularioDePasos(Request $request)
    {

    }
}
