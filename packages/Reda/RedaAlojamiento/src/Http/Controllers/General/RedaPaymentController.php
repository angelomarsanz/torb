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
        // 1. Log de entrada total para rastrear el flujo
        Log::info("REDA Payment: Petición entrante [" . $request->method() . "] - URI: " . $request->getRequestUri());

        // El ID puede venir de la ruta, del input, o de lo que guardamos previamente en sesión
        $propertyId = $request->id ?? $request->route('id') ?? Session::get('payment_property_id');

        // 2. Si hay datos en el request (POST inicial), los aseguramos en la sesión
        // Usamos una clave personalizada para evitar limpiezas accidentales del core de vRent.
        if ($request->has('checkin')) {
            Log::info("REDA Payment: Asegurando datos de reserva en sesión para ID: " . $propertyId);
            $bookingData = $request->all();
            $bookingData['id'] = $propertyId; // Aseguramos que el ID vaya en el pack
            
            Session::put('reda_payment_data', $bookingData);
            Session::put('payment_property_id', $propertyId);
            
            // También guardamos en las claves estándar por compatibilidad con el padre
            Session::put([
                'payment_checkin'        => $request->checkin,
                'payment_checkout'       => $request->checkout,
                'payment_number_of_guests' => $request->number_of_guests,
                'payment_booking_type'   => $request->booking_type,
                'payment_booking_status' => $request->booking_status,
                'payment_booking_id'     => $request->booking_id,
            ]);
            Session::save();
        }

        // 3. Verificación de Autenticación manual
        if (!Auth::check()) {
            Log::info("REDA Payment: Usuario no autenticado para ID: " . $propertyId . ". Forzando redirección controlada.");
            
            // Forzamos el intended para que el LoginController sepa a dónde volver.
            $targetUrl = url("payments/book/{$propertyId}");
            Session::put('url.intended', $targetUrl);
            Session::put('reda.intended', $targetUrl); // Backup nuestro
            
            return redirect()->guest('login');
        }

        // 4. Restauración Crítica post-login
        // Si regresamos del login (GET) y el request está "vacío" de datos de reserva, los re-inyectamos.
        // Sin el parámetro 'id' o 'checkin', el parent::index original redirige a la propiedad.
        if (!$request->has('checkin') && Session::has('reda_payment_data')) {
            Log::info("REDA Payment: Detectado regreso de login. Restaurando datos desde 'reda_payment_data'.");
            $backup = Session::get('reda_payment_data');
            $request->merge($backup);
            
            // Log de verificación de lo restaurado
            Log::info("REDA Payment: Request restaurado con ID: " . $request->id . " y Checkin: " . $request->checkin);
        }

        // 5. Verificación de Usuario Activo (Replicando comportamiento de Guest middleware del proyecto)
        if (Auth::user()->status == 'Inactive') {
            Log::warning("REDA Payment: Usuario inactivo detectado: " . Auth::user()->email);
            Auth::logout();
            return redirect()->guest('login');
        }

        Log::info("REDA Payment: Entregando control al PaymentController original. ID final: " . $request->id);
        return parent::index($request);
    }
}
