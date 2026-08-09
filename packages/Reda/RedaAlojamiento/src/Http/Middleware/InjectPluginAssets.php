<?php

namespace Reda\RedaAlojamiento\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectPluginAssets
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if it's a standard response and contains HTML
        if (method_exists($response, 'getContent') && 
            $response->headers->get('Content-Type') && 
            str_contains($response->headers->get('Content-Type'), 'text/html')) {

            $content = $response->getContent();

            // Check if it's not an admin page and script not already there
            if (!$request->is('admin*') && !str_contains($content, 'chat-injection.min.js')) {
                
                // Added ?v= parameter to force browser refresh after compilation
                $chatScriptUrl = '/public/js/reda/general/chat-injection.min.js?v=' . time();
                $reserveScriptUrl = '/public/js/reda/general/reserve-injection.min.js?v=' . time();
                
                $scriptTag = '<!-- REDA PLUGIN -->';
                $scriptTag .= '<script src="' . $chatScriptUrl . '"></script>';
                $scriptTag .= '<script src="' . $reserveScriptUrl . '"></script>';

                // Inject before </body>
                $pos = strripos($content, '</body>');
                if ($pos !== false) {
                    $content = substr($content, 0, $pos) . $scriptTag . substr($content, $pos);
                    $response->setContent($content);
                }
            }
        }

        return $response;
    }
}

