<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Admin\Disputa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Reda\RedaAlojamiento\Models\Disputa\Disputa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DisputaController extends Controller
{
    /**
     * Muestra la vista principal de mediaciones para el administrador.
     */
    public function index()
    {
        return view('reda-alojamiento::admin.disputa.index');
    }

}
