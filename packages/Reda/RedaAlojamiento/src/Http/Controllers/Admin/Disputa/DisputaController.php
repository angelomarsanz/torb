<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Admin\Disputa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Reda\RedaAlojamiento\Models\Disputa\Disputa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DisputaController extends Controller
{
    /**
     * Muestra la vista principal de mediaciones para el administrador.
     */
    public function index()
    {
        return view('reda-alojamiento::admin.disputa.index');
    }

    /**
     * Obtiene el listado de mediaciones paginado para el administrador.
     */
    public function obtenerDisputasPaginadas(Request $request)
    {
        $estatus = $request->get('status', 'todos');

        $consulta = Disputa::query();

        if ($estatus !== 'todos') {
            // Mapeo de estados del frontend a los valores en la base de datos (traducidos)
            $mapeo = [
                'abiertos' => __('Abierto'),
                'revision' => __('En revisión'),
                'espera'   => __('Esperando respuesta'),
                'resueltos' => __('Resuelto'),
                'cerrados' => __('Cerrado')
            ];
            
            if (isset($mapeo[$estatus])) {
                $consulta->where('estado', $mapeo[$estatus]);
            }
        }

        $disputas = $consulta->with(['booking.properties.property_address', 'agente', 'turista', 'anfitrion'])->orderBy('updated_at', 'desc')->paginate(10);

        // Formatear los datos para el consumo del frontend via Javascript
        $elementos = $disputas->getCollection()->map(function($d) {
            
            // Adjuntos del Turista
            $adjuntosTurista = [];
            if ($d->documentos_turista) {
                $rutas = json_decode($d->documentos_turista, true);
                if (is_array($rutas)) {
                    foreach ($rutas as $ruta) {
                        $rutaWeb = strpos($ruta, 'public/') === 0 ? $ruta : 'public/' . $ruta;
                        $adjuntosTurista[] = [
                            'nombre' => basename($ruta),
                            'url' => asset($rutaWeb),
                            'es_imagen' => in_array(strtolower(pathinfo($ruta, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])
                        ];
                    }
                }
            }

            // Adjuntos del Anfitrión
            $adjuntosAnfitrion = [];
            if ($d->documentos_anfitrion) {
                $rutas = json_decode($d->documentos_anfitrion, true);
                if (is_array($rutas)) {
                    foreach ($rutas as $ruta) {
                        $rutaWeb = strpos($ruta, 'public/') === 0 ? $ruta : 'public/' . $ruta;
                        $adjuntosAnfitrion[] = [
                            'nombre' => basename($ruta),
                            'url' => asset($rutaWeb),
                            'es_imagen' => in_array(strtolower(pathinfo($ruta, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])
                        ];
                    }
                }
            }

            // Combinar adjuntos para el resumen general del admin (o mostrar por separado)
            $adjuntos = array_merge($adjuntosTurista, $adjuntosAnfitrion);

            // Asegurar prefijo public/ para la foto de la propiedad si es ruta relativa
            $propiedadFoto = $d->booking && $d->booking->properties ? $d->booking->properties->cover_photo : asset('public/img/unnamed.png');
            if ($d->booking && $d->booking->properties && strpos($propiedadFoto, 'http') === false && strpos($propiedadFoto, 'public/') !== 0) {
                $propiedadFoto = asset('public/' . $propiedadFoto);
            } elseif ($d->booking && $d->booking->properties) {
                $propiedadFoto = asset($propiedadFoto);
            }

            // Datos de ubicación
            $ubicacion = '';
            if ($d->booking && $d->booking->properties && $d->booking->properties->property_address) {
                $direccion = $d->booking->properties->property_address;
                $ubicacion = trim(($direccion->city ?? '') . ', ' . ($direccion->state ?? ''), ', ');
            }

            return [
                'id' => $d->id,
                'estado' => $d->estado,
                'paso_actual' => $d->paso_actual,
                'motivo' => $d->motivo,
                'prioridad' => $d->prioridad,
                'descripcion' => $d->descripcion,
                'booking_id' => $d->booking_id,
                'booking_start_date' => $d->booking ? date('d/m/Y', strtotime($d->booking->start_date)) : '',
                'booking_end_date' => $d->booking ? date('d/m/Y', strtotime($d->booking->end_date)) : '',
                'booking_guest' => $d->booking ? $d->booking->guest : 0,
                'propiedad_nombre' => $d->booking && $d->booking->properties ? $d->booking->properties->name : '',
                'propiedad_ubicacion' => $ubicacion,
                'adjuntos' => $adjuntos,
                'adjuntos_turista' => $adjuntosTurista,
                'adjuntos_anfitrion' => $adjuntosAnfitrion,
                'fecha_apertura' => $d->fecha_apertura ? $d->fecha_apertura->format('d/m/Y H:i') : '',
                'actualizado_hace' => $d->updated_at->diffForHumans(),
                'agente' => $d->agente ? [
                    'nombre' => $d->agente->username,
                    'foto' => $d->agente->profile_src
                ] : null,
                'turista_nombre' => $d->turista ? $d->turista->first_name . ' ' . $d->turista->last_name : '',
                'turista_foto' => $d->turista ? $d->turista->profile_src : asset('public/img/unnamed.png'),
                'anfitrion_nombre' => $d->anfitrion ? $d->anfitrion->first_name . ' ' . $d->anfitrion->last_name : '',
                'anfitrion_foto' => $d->anfitrion ? $d->anfitrion->profile_src : asset('public/img/unnamed.png'),
                'propiedad_foto' => $propiedadFoto
            ];
        });

        $respuesta = [
            'success' => true,
            'message' => __('Listado de mediaciones (Admin)'),
            'mensaje_usuario' => __('Listado recuperado con éxito'),
            'respuesta' => [
                'data' => $elementos,
                'pagination' => (string) $disputas->appends(request()->except('page'))->links('reda-alojamiento::general.paginacion')
            ],
            'code' => 200
        ];

        return response()->json($respuesta, $respuesta['code']);
    }

    /**
     * Retorna el HTML del modal de detalle de mediación para el administrador.
     */
    public function getDetailModal($id)
    {
        $disputa = Disputa::findOrFail($id);
        // Usamos la vista específica para admin adaptada a Bootstrap 5
        $html = view('reda-alojamiento::admin.disputa.modal_detalle', compact('disputa'))->render();
        
        $respuesta = [
            'success' => true,
            'message' => __('Carga de detalle'),
            'mensaje_usuario' => __('Cargado con éxito'),
            'respuesta' => $html,
            'code' => 200
        ];

        return response()->json($respuesta, $respuesta['code']);
    }

}
