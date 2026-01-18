<?php
namespace Reda\RedaAlojamiento\Http\Controllers\Administrativo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdministrativoController extends Controller
{
    public function index()
    {
        return view('reda-alojamiento::administrativo.administrativos.index');
    }
}