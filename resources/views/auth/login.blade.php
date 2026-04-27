<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — Agrogranja</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo-seedling-transparente.svg') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body { height: 100%; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex;
            min-height: 100vh;
        }

        /* ── PANEL IZQUIERDO — fotos ── */
        .photo-panel {
            display: none;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
            background: #0F2D1A;
        }
        @media (min-width: 900px) {
            .photo-panel { display: block; width: 52%; }
        }

        /*
         * GRID — 2 columnas, 3 filas
         * Fotos disponibles y sus proporciones:
         *   crops-2  : portrait alto  (montaña/finca)     → col izq, filas 1-2
         *   cows     : portrait       (vacas en corral)    → col der, fila 1
         *   crops-1  : portrait alto  (cultivos vegetales) → col der, fila 2
         *   farmer-1 : landscape ancho(productor en campo) → ambas cols, fila 3
         *
         *   ┌──────────┬──────────┐
         *   │          │  cows    │  fila 1
         *   │ crops-2  ├──────────┤
         *   │          │ crops-1  │  fila 2
         *   ├──────────┴──────────┤
         *   │     farmer-1        │  fila 3 (landscape ancho)
         *   └─────────────────────┘
         */
        .photo-mosaic {
            width: 100%;
            height: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1.6fr 1.2fr 1fr;
            gap: 3px;
        }

        /* crops-2: col 1, filas 1-2 (portrait alto) */
        .p-crops2 {
            grid-column: 1;
            grid-row: 1 / 3;
        }

        /* cows: col 2, fila 1 */
        .p-cows {
            grid-column: 2;
            grid-row: 1;
        }

        /* crops-1: col 2, fila 2 */
        .p-crops1 {
            grid-column: 2;
            grid-row: 2;
        }

        /* farmer-1: ambas cols, fila 3 (landscape ocupa todo el ancho) */
        .p-farmer {
            grid-column: 1 / 3;
            grid-row: 3;
        }

        .mosaic-item {
            overflow: hidden;
            position: relative;
        }
        .mosaic-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }
        .mosaic-item:hover img { transform: scale(1.04); }

        /* Fallback colors si no carga imagen */
        .p-crops2  { background: #1A4731; }
        .p-cows    { background: #1D4A2A; }
        .p-crops1  { background: #2D5C1E; }
        .p-farmer  { background: #1A3D22; }

        /* ── LOGO arriba ── */
        .panel-logo {
            position: absolute;
            top: 1.75rem;
            left: 1.75rem;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 3;
        }
        .panel-logo img {
            width: 42px;
            height: auto;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.45));
        }
        .panel-logo span {
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            font-family: Georgia, serif;
            text-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }

        /* ── OVERLAY texto abajo ── */
        .photo-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(10,30,15,0.35) 0%,
                rgba(10,30,15,0.05) 30%,
                rgba(10,30,15,0.05) 60%,
                rgba(10,30,15,0.75) 100%
            );
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 2.25rem;
            z-index: 2;
        }

        .panel-tagline {
            color: rgba(255,255,255,0.65);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .panel-quote {
            color: #fff;
            font-size: 1.45rem;
            font-weight: 800;
            line-height: 1.35;
            margin-bottom: 1.1rem;
            font-family: Georgia, serif;
            max-width: 340px;
        }
        .panel-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }
        .panel-chip {
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.22);
            border-radius: 99px;
            padding: 5px 13px;
            color: rgba(255,255,255,0.9);
            font-size: 0.78rem;
            font-weight: 500;
            backdrop-filter: blur(4px);
        }

        /* ── PANEL DERECHO — formulario ── */
        .form-panel {
            flex: 1;
            background: linear-gradient(160deg, #2D7A45 0%, #1A4731 55%, #3D6B28 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            min-height: 100vh;
        }

        /* Logo solo en mobile */
        .mobile-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
        }
        .mobile-logo img { width: 72px; height: auto; margin-bottom: 0.75rem; }
        .mobile-logo span { color: #fff; font-size: 1.6rem; font-weight: 800; font-family: Georgia, serif; }
        @media (min-width: 900px) { .mobile-logo { display: none; } }

        .form-card {
            background: #fff;
            border-radius: 20px;
            padding: 2.2rem 2rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.25);
        }

        .form-card h2 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1A2E1A;
            margin-bottom: 4px;
        }
        .form-card .subtitle {
            color: #6B7280;
            font-size: 0.88rem;
            margin-bottom: 1.75rem;
        }

        .field-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #374151;
            margin-bottom: 6px;
        }

        .field-input {
            width: 100%;
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 0.95rem;
            color: #111827;
            background: #F9FAFB;
            transition: border-color 0.15s, background 0.15s;
            outline: none;
            margin-bottom: 1rem;
            font-family: inherit;
        }
        .field-input:focus {
            border-color: #1D9E75;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(29,158,117,0.12);
        }

        .btn-login {
            width: 100%;
            background: #1A4731;
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 13px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: background 0.15s, transform 0.1s;
            font-family: inherit;
        }
        .btn-login:hover { background: #245e3f; transform: translateY(-1px); }

        .form-footer {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.85rem;
            color: #6B7280;
        }
        .form-footer a { color: #1D9E75; font-weight: 600; text-decoration: none; }
        .form-footer a:hover { text-decoration: underline; }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 0.75rem;
            color: rgba(255,255,255,0.55);
            font-size: 0.82rem;
            text-decoration: none;
        }
        .back-link:hover { color: rgba(255,255,255,0.85); }

        .alert-error {
            background: #FEE2E2;
            border: 1px solid #FECACA;
            border-radius: 10px;
            padding: 10px 14px;
            color: #991B1B;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<!-- ── PANEL FOTOS (desktop) ── -->
<div class="photo-panel">

    <div class="photo-mosaic">

        <!-- Col izq, filas 1-2: crops-2 (portrait alto, montaña/finca) -->
        <div class="mosaic-item p-crops2">
            <img src="{{ asset('img/galeria/crops-2.jpg') }}" alt="Finca colombiana">
        </div>

        <!-- Col der, fila 1: cows (portrait, vacas en corral) -->
        <div class="mosaic-item p-cows">
            <img src="{{ asset('img/galeria/finca.jpg') }}" alt="Ganadería">
        </div>

        <!-- Col der, fila 2: crops-1 (portrait, cultivos vegetales) -->
        <div class="mosaic-item p-crops1">
            <img src="{{ asset('img/galeria/gallina.jpg') }}" alt="Cultivos">
        </div>

        <!-- Ambas cols, fila 3: farmer-1 (landscape, productor en campo) -->
        <div class="mosaic-item p-farmer">
            <img src="{{ asset('img/galeria/arroz.jpg') }}" alt="Productor">
        </div>

    </div>

    <!-- Logo arriba izquierda -->
    <div class="panel-logo">
        <img src="{{ asset('img/logo-seedling-fondo-verde.svg') }}" alt="Agrogranja">
        <span>Agrogranja</span>
    </div>

    <!-- Overlay con texto abajo -->
    <div class="photo-overlay">
        <p class="panel-tagline">Para pequeños productores</p>
        <h2 class="panel-quote">Gestiona tu finca como nunca antes</h2>
        <div class="panel-chips">
            <span class="panel-chip">🌱 Cultivos</span>
            <span class="panel-chip">🐄 Animales</span>
            <span class="panel-chip">💰 Finanzas</span>
            <span class="panel-chip">📅 Tareas</span>
        </div>
    </div>

</div>

<!-- ── PANEL FORMULARIO ── -->
<div class="form-panel">

    <!-- Logo mobile -->
    <div class="mobile-logo">
        <img src="{{ asset('img/logo-seedling-transparente.svg') }}" alt="Agrogranja"
             onerror="this.style.display='none'">
        <span>Agrogranja</span>
    </div>

    <div class="form-card">
        <h2>Iniciar sesión</h2>
        <p class="subtitle">Accede a tu cuenta</p>

        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <label class="field-label" for="email">Correo electrónico</label>
            <input class="field-input" type="email" id="email" name="email"
                   placeholder="tu@correo.com" value="{{ old('email') }}" required autofocus>

            <label class="field-label" for="password">Contraseña</label>
            <input class="field-input" type="password" id="password" name="password"
                   placeholder="••••••••" required>

            <button type="submit" class="btn-login">
                Ingresar →
            </button>
        </form>

        <div class="form-footer">
            ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a>
        </div>
    </div>

    <a href="{{ url('/') }}" class="back-link">← Volver</a>
</div>

</body>
</html>