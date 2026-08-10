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
        if (!Auth::check()) {
            Log::info("REDA Payment: Invitado intentando reservar ID: " . $propertyId . ". Forzando intended URL.");
            
            // Forzamos la URL de retorno para que el login sepa exactamente a dónde volver (URL de reserva)
            // En lugar de que vuelva a la página de la propiedad por defecto.
            Session::put('url.intended', url("payments/book/{$propertyId}"));
            
            return redirect()->guest('login');
        }

        // 3. Restauración de datos al Request
        // Si venimos de un login exitoso (GET) y no tenemos fechas en el request, las inyectamos desde la sesión.
        // Esto engaña al controlador original (parent::index) para que procese el pago en lugar de redirigir.
        if (!$request->has('checkin') && Session::has('payment_checkin')) {
            Log::info("REDA Payment: Restaurando datos de sesión al Request para ID: " . Session::get('payment_property_id'));
            
            $request->merge([
                'id' => Session::get('payment_property_id'),
                'checkin' => Session::get('payment_checkin'),
                'checkout' => Session::get('payment_checkout'),
                'number_of_guests' => Session::get('payment_number_of_guests'),
                'booking_type' => Session::get('payment_booking_type'),
                'booking_status' => Session::get('payment_booking_status'),
                'booking_id' => Session::get('payment_booking_id'),
            ]);
        }

        // 4. Verificación de Usuario Activo (Replicando comportamiento de Guest middleware del proyecto)
        if (Auth::user()->status == 'Inactive') {
            Log::warning("REDA Payment: Usuario inactivo detectado: " . Auth::user()->email);
            Auth::logout();
            return redirect()->guest('login');
        }

        // 5. Continuar con la lógica original del proyecto invocando al padre
        return parent::index($request);
    }
}
