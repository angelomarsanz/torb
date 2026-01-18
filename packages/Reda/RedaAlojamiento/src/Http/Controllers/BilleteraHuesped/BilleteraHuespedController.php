<?php
namespace Reda\RedaAlojamiento\Http\Controllers\BilleteraHuesped;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BilleteraHuespedController extends Controller
{
    public function index()
    {
        return view('reda-alojamiento::billetera_huesped.billeteras_huespedes.index');
    }
}