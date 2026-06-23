<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Admin\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Reda\RedaAlojamiento\Models\Admin\SoporteTecnico;

class SoporteTecnicoController extends Controller
{
    /**
     * Display the index page for technical support.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $tickets = SoporteTecnico::with('user')->orderBy('created_at', 'desc')->get();
        return view('reda-alojamiento::admin.general.soporte_tecnico.index', compact('tickets'));
    }

    /**
     * Display the details of a technical support ticket.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $ticket = SoporteTecnico::with('user')->findOrFail($id);
        return view('reda-alojamiento::admin.general.soporte_tecnico.show', compact('ticket'));
    }
}
