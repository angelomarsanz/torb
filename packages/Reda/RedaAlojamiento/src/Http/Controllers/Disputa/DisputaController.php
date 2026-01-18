<?php
namespace Reda\RedaAlojamiento\Http\Controllers\Disputa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DisputaController extends Controller
{
    public function index()
    {
        return view('reda-alojamiento::disputa.disputas.index');
    }
}