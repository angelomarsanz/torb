<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Experiencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExperienciaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('reda-alojamiento::experiencia.experiencias.index');
    }
    // Crear acción para la vista de creación
    public function create()
    {
        return view('reda-alojamiento::experiencia.experiencias.create');   
    }
}
