<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Admin\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SoporteTecnicoController extends Controller
{
    /**
     * Display the index page for technical support.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('reda-alojamiento::admin.general.soporte_tecnico.index');
    }
}
