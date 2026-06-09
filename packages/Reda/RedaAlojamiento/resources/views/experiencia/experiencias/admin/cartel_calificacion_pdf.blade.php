<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cartel de Calificación - {{ $experiencia->titulo }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #222;
        }
        .page-container {
            width: 100%;
            height: 100%;
            padding: 40px;
            box-sizing: border-box;
            text-align: center;
        }
        .header {
            margin-top: 20px;
        }
        .logo {
            width: 150px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 48px;
            font-weight: bold;
            color: #FF5A5F; /* Color Airbnb aproximado */
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 24px;
            margin-bottom: 50px;
            color: #484848;
        }
        .qr-container {
            margin: 40px auto;
            padding: 30px;
            border: 2px solid #eee;
            display: inline-block;
            background: #fff;
            border-radius: 20px;
        }
        .qr-image {
            width: 350px;
            height: 350px;
        }
        .footer {
            margin-top: 50px;
            border-top: 1px solid #eee;
            padding-top: 30px;
        }
        .business-name {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .category {
            font-size: 18px;
            color: #767676;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .instructions {
            margin-top: 40px;
            font-size: 16px;
            color: #767676;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="header">
            {{-- Logo Fijo por ahora --}}
            <div class="logo-placeholder">
                <h1 style="font-size: 60px; color: #FF5A5F; margin: 0;">Torbian</h1>
            </div>
        </div>

        <div class="content">
            <div class="title">¡Danos tu opinión!</div>
            <div class="subtitle">Escanea el código para calificarnos</div>

            <div class="qr-container">
                {{-- Usamos el helper de mPDF para generar QR directamente en el PDF --}}
                <barcode code="{{ $urlCalificar }}" type="QR" size="2.5" error="H" />
            </div>

            <div class="instructions">
                Abre la cámara de tu celular y apunta al código QR
            </div>
        </div>

        <div class="footer">
            <div class="business-name">{{ $experiencia->titulo }}</div>
            <div class="category">{{ $experiencia->categoria_negocio }}</div>
        </div>
    </div>
</body>
</html>
