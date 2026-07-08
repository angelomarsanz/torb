<?php

namespace Reda\RedaAlojamiento\Http\Controllers\Experiencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Reda\RedaAlojamiento\Models\Experiencia\FavoritoComercio;
use Reda\RedaAlojamiento\Models\Experiencia\Experiencia;
use Auth;
use Illuminate\Support\Facades\Log;

class FavoritoController extends Controller
{
    /**
     * Obtiene el listado de comercios favoritos del usuario autenticado.
     */
    public function getFavoritosComercios(Request $request)
    {
        try {
            $userId = Auth::id();
            
            $favoritos = FavoritoComercio::with(['experiencia.fotos'])
                ->where('user_id', $userId)
                ->get()
                ->pluck('experiencia')
                ->filter(); // Eliminar nulos si el comercio fue borrado

            if ($request->ajax()) {
                $html = view('reda-alojamiento::experiencia.experiencias.frontend.partials.lista_favoritos_comercios', compact('favoritos'))->render();
                
                $respuesta = [
                    'success' => true,
                    'message' : 'Favorites retrieved successfully',
                    'mensaje_usuario': __('Listado de favoritos recuperado'),
                    'respuesta' => [
                        'html' => $html,
                        'cantidad' => $favoritos->count()
                    ],
                    'code' => 200
                ];
                return response()->json($respuesta, $respuesta['code']);
            }

            return redirect()->back();

        } catch (\Exception $e) {
            Log::error("Error en getFavoritosComercios: " . $e->getMessage());
            $respuesta = [
                'success' => false,
                'message' : 'Error retrieving favorites',
                'mensaje_usuario': __('Ocurrió un error al cargar tus favoritos'),
                'respuesta' => $e->getMessage(),
                'code' => 500
            ];
            return response()->json($respuesta, $respuesta['code']);
        }
    }

    /**
     * Alterna (Agrega o Elimina) un comercio de los favoritos del usuario.
     */
    public function toggleFavoritoComercio(Request $request, $id)
    {
        try {
            $experiencia = Experiencia::findOrFail($id);
            $userId = Auth::id();

            // 1. Seguridad: Verificar que el usuario no sea el dueño del comercio
            if ($userId == $experiencia->user_id) {
                $respuesta = [
                    'success' => false,
                    'message' => 'Owner cannot favorite their own business',
                    'mensaje_usuario' => __('No puedes agregar tu propio negocio a favoritos'),
                    'respuesta' => '',
                    'code' => 403
                ];
                return response()->json($respuesta, $respuesta['code']);
            }

            // 2. Buscar si ya existe el favorito
            $favorito = FavoritoComercio::where('user_id', $userId)
                                       ->where('experiencia_id', $id)
                                       ->first();

            if ($favorito) {
                // Si existe, lo eliminamos
                $favorito->delete();
                $accion = 'eliminado';
                $mensajeUsuario = __('Negocio eliminado de tus favoritos');
            } else {
                // Si no existe, lo creamos
                FavoritoComercio::create([
                    'user_id' => $userId,
                    'experiencia_id' => $id
                ]);
                $accion = 'agregado';
                $mensajeUsuario = __('Negocio agregado a tus favoritos');
            }

            $respuesta = [
                'success' => true,
                'message' => "Business $accion from favorites",
                'mensaje_usuario' => $mensajeUsuario,
                'respuesta' => [
                    'accion' => $accion
                ],
                'code' => 200
            ];
            return response()->json($respuesta, $respuesta['code']);

        } catch (\Exception $e) {
            Log::error("Error en toggleFavoritoComercio: " . $e->getMessage());
            $respuesta = [
                'success' => false,
                'message' => 'Error toggling favorite: ' . $e->getMessage(),
                'mensaje_usuario' => __('Error al procesar la solicitud de favoritos'),
                'respuesta' => $e->getMessage(),
                'code' => 500
            ];
            return response()->json($respuesta, $respuesta['code']);
        }
    }
}
