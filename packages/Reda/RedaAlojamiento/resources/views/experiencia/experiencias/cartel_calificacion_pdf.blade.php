<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cartel de Calificación - {{ $experiencia->titulo }}</title>
    <style>
        /* Estilos específicos para mPDF */
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #222;
            background-color: #fff;
        }
        .pdf-cartel-container {
            width: 100%;
            height: 100%;
            padding: 60px 40px;
            box-sizing: border-box;
            text-align: center;
            position: relative;
        }
        
        /* Cabecera con Logo de Torbian (Destacado) */
        .pdf-cartel-header {
            margin-bottom: 60px;
            text-align: center;
        }
        .pdf-cartel-logo-torbian {
            width: 220px;
            height: auto;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        /* Contenido Principal */
        .pdf-cartel-content {
            margin-top: 20px;
        }
        .pdf-cartel-title {
            font-size: 56px;
            font-weight: 900;
            color: #FF385C; /* Color Torbian Red */
            margin-bottom: 15px;
            letter-spacing: -2px;
        }
        .pdf-cartel-subtitle {
            font-size: 24px;
            margin-bottom: 60px;
            color: #717171;
            font-weight: 400;
        }

        /* Contenedor QR con diseño limpio */
        .pdf-cartel-qr-wrapper {
            margin: 0 auto;
            padding: 40px;
            background: #fff;
            border-radius: 40px;
            border: 1px solid #DDDDDD;
            display: inline-block;
        }

        .pdf-cartel-instructions {
            margin-top: 50px;
            font-size: 18px;
            color: #222;
            font-weight: 600;
        }
        .pdf-cartel-sub-instructions {
            font-size: 14px;
            color: #717171;
            margin-top: 5px;
        }

        /* Pie de página con Logo del Negocio */
        .pdf-cartel-footer {
            position: absolute;
            bottom: 60px;
            left: 0;
            right: 0;
            text-align: center;
            padding: 0 40px;
        }
        .pdf-cartel-footer-divider {
            width: 80%;
            height: 1px;
            background-color: #DDDDDD;
            margin: 0 auto 40px auto;
        }
        
        .pdf-cartel-business-name {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 5px;
            color: #222;
        }
        .pdf-cartel-category {
            font-size: 16px;
            color: #717171;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 600;
        }
        .pdf-cartel-business-logo {
            max-height: 80px;
            width: auto;
            margin-bottom: 15px;
            /* Se elimina el border sólido para respetar la forma geométrica */
        }
        .pdf-icon-placeholder {
            font-size: 40px;
            color: #717171;
            margin-bottom: 15px;
        }
        </style>
        </head>
        <body class="pdf-cartel-body">
        @php
        // Lógica para el logo del negocio o imagen destacada siguiendo PRIORIDAD ESTRICTA
        $rutaImagenNegocio = null;
        
        // Prioridad 1: Logo del comercio (ruta_imagenes)
        if (!empty($experiencia->ruta_imagenes)) {
            $pathLogo = public_path('images/logos_negocios/' . $experiencia->ruta_imagenes);
            if (file_exists($pathLogo)) {
                $rutaImagenNegocio = $pathLogo;
            }
        }

        // Prioridad 2 y 3: Foto de portada (Destacada > Primera) usando el accesor del modelo
        if (!$rutaImagenNegocio) {
            $fotoPortada = $experiencia->foto_portada;
            if ($fotoPortada) {
                $pathFoto = public_path('images/experiencias/' . $experiencia->id . '/' . $fotoPortada->photo);
                if (file_exists($pathFoto)) {
                    $rutaImagenNegocio = $pathFoto;
                }
            }
        }

        // Ruta del logo de Torbian
        $pathLogoTorbian = public_path('front/images/logos/1755989952_logo.png');
        @endphp


    <div class="pdf-cartel-container">
        <div class="pdf-cartel-header">
            @if(file_exists($pathLogoTorbian))
                <img src="{{ $pathLogoTorbian }}" class="pdf-cartel-logo-torbian">
            @endif
        </div>

        <div class="pdf-cartel-content">
            <div class="pdf-cartel-title">{{ __('¡Danos tu opinión!') }}</div>
            <div class="pdf-cartel-subtitle">{{ __('Escanea el código para calificarnos') }}</div>

            <div class="pdf-cartel-qr-wrapper">
                <barcode code="{{ $urlCalificar }}" type="QR" size="2.5" error="H" />
            </div>

            <div class="pdf-cartel-instructions">
                {{ __('Abre la cámara de tu celular') }}
            </div>
            <div class="pdf-cartel-sub-instructions">
                {{ __('y apunta directamente al código QR') }}
            </div>
        </div>

        <div class="pdf-cartel-footer">
            <div class="pdf-cartel-footer-divider"></div>
            
            @if($rutaImagenNegocio)
                <img src="{{ $rutaImagenNegocio }}" class="pdf-cartel-business-logo">
            @else
                {{-- Prioridad 4: Ícono genérico si no hay imagen --}}
                <div class="pdf-icon-placeholder">🛒</div>
            @endif

            <div class="pdf-cartel-business-name">{{ $experiencia->titulo }}</div>
            <div class="pdf-cartel-category">{{ $experiencia->categoria_negocio }}</div>
        </div>
    </div>
</body>
</html>
