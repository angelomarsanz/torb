<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Experiencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnfitrionExperienciaController extends Controller
{
    /**
     * Muestra la lista de recursos.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('reda-alojamiento::experiencia.anfitriones_experiencias.index');
    }
}
