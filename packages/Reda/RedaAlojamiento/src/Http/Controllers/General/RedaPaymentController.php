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
        // El ID puede venir como parámetro de ruta o en el request (id)
        $propertyId = $request->id ?? $request->route('id');

        // 1. Si es POST, guardamos la intención de reserva en la sesión
        // Esto permite que al regresar del login (vía GET) los datos sigan disponibles.
        if ($request->isMethod('post')) {
            Log::info("REDA Payment: Capturando datos de reserva (POST) para propiedad ID: " . $propertyId);
            
            Session::put('payment_property_id', $propertyId);
            Session::put('payment_checkin', $request->checkin);
            Session::put('payment_checkout', $request->checkout);
            Session::put('payment_number_of_guests', $request->number_of_guests);
            Session::put('payment_booking_type', $request->booking_type);
            Session::put('payment_booking_status', $request->booking_status);
            Session::put('payment_booking_id', $request->booking_id);
            Session::save();
        }

        // 2. Verificación de Autenticación manual
        // No usamos el middleware 'guest' original porque redirige ANTES de que podamos guardar la sesión.
        if (!Auth::check()) {
            Log::info("REDA Payment: Usuario no autenticado para reserva de ID: " . $propertyId . ". Redirigiendo a login.");
            return redirect()->guest('login');
        }

        // Log de depuración al regresar del login
        if (!$request->isMethod('post')) {
            Log::info("REDA Payment: Procesando reserva (GET) tras autenticación. Checkin en sesión: " . Session::get('payment_checkin'));
        }

        // 3. Verificación de Usuario Activo (Replicando comportamiento de Guest middleware del proyecto)
        if (Auth::user()->status == 'Inactive') {
            Log::warning("REDA Payment: Usuario inactivo detectado: " . Auth::user()->email);
            Auth::logout();
            return redirect()->guest('login');
        }

        // 4. Continuar con la lógica original del proyecto invocando al padre
        // El padre leerá los datos de la Session::get(...) que acabamos de asegurar.
        return parent::index($request);
    }
}
