<?php

namespace Reda\RedaAlojamiento\Http\Controllers\General;

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Auth, Session, Log;

class RedaPaymentController extends PaymentController
{
    /**
     * Sobrescribe el index original para capturar datos de reserva antes de la autenticación.
     * Esto soluciona el problema de pérdida de datos POST cuando un invitado intenta reservar.
     */
    public function index(Request $request)
    {
        // 1. Si es POST, guardamos la intención de reserva en la sesión
        // Esto permite que al regresar del login (vía GET) los datos sigan disponibles.
        if ($request->isMethod('post')) {
            Log::info("REDA Payment: Capturando datos de reserva (POST) antes de validación de Auth.");
            
            Session::put('payment_property_id', $request->id);
            Session::put('payment_checkin', $request->checkin);
            Session::put('payment_checkout', $request->checkout);
            Session::put('payment_number_of_guests', $request->number_of_guests);
            Session::put('payment_booking_type', $request->booking_type);
            Session::put('payment_booking_status', $request->booking_status);
            Session::put('payment_booking_id', $request->booking_id);
            Session::save();
        }

        // 2. Verificación de Autenticación manual
        // No usamos middleware en la ruta para poder ejecutar el paso 1.
        if (!Auth::check()) {
            Log::info("REDA Payment: Usuario no autenticado, redirigiendo a login.");
            return redirect()->guest('login');
        }

        // 3. Verificación de Usuario Activo (Replicando comportamiento de Guest middleware)
        if (Auth::user()->status == 'Inactive') {
            Log::warning("REDA Payment: Usuario inactivo detectado.");
            Auth::logout();
            return redirect()->guest('login');
        }

        // 4. Continuar con la lógica original del proyecto invocando al padre
        return parent::index($request);
    }
}
