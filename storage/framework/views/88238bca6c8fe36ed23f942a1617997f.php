<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta — Agrogranja</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('img/logo-seedling-transparente.svg')); ?>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body { height: 100%; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #F3F4F0;
            display: flex;
            flex-direction: column;
        }

        /* ── HEADER ── */
        .top-bar {
            background: linear-gradient(160deg, #2D7A45 0%, #1A4731 100%);
            padding: 1.75rem 1.5rem 1.6rem;
            text-align: center;
            flex-shrink: 0;
        }
        .top-bar img.logo {
            width: 80px; height: auto;
            margin-bottom: 0.6rem;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.3));
        }
        .top-bar h1 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 800;
            font-family: Georgia, serif;
            margin-bottom: 3px;
        }
        .top-bar p { color: rgba(255,255,255,0.7); font-size: 0.85rem; }

        /* ── MAIN: form izquierda + fotos derecha ── */
        .main {
            display: flex;
            flex: 1;
            min-height: 0;
        }

        /* ── FORMULARIO ── */
        .form-side {
            width: 100%;
            background: #fff;
            padding: 2rem 1.75rem 3rem;
            overflow-y: auto;
            box-shadow: 2px 0 20px rgba(0,0,0,0.07);
            flex-shrink: 0;
        }
        @media (min-width: 860px) {
            .form-side { width: 440px; }
        }

        .section-label {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #6B7280;
            margin: 1.5rem 0 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid #F3F4F6;
        }
        .section-label:first-child { margin-top: 0; border-top: none; padding-top: 0; }

        .field-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #374151;
            margin-bottom: 5px;
        }
        .required { color: #DC2626; }

        .field-input {
            width: 100%;
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 0.95rem;
            color: #111827;
            background: #F9FAFB;
            transition: border-color 0.15s;
            outline: none;
            margin-bottom: 1rem;
            font-family: inherit;
        }
        .field-input:focus {
            border-color: #1D9E75;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(29,158,117,0.12);
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .btn-submit {
            width: 100%;
            background: #1A4731;
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 1.5rem;
            transition: background 0.15s, transform 0.1s;
            font-family: inherit;
        }
        .btn-submit:hover { background: #245e3f; transform: translateY(-1px); }

        .login-link {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #6B7280;
        }
        .login-link a { color: #1D9E75; font-weight: 600; text-decoration: none; }

        .alert-error {
            background: #FEE2E2;
            border: 1px solid #FECACA;
            border-radius: 10px;
            padding: 10px 14px;
            color: #991B1B;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        /* ── PANEL FOTOS ── */
        .photo-side {
            display: none;
            flex: 1;
            position: relative;
            overflow: hidden;
            background: #0F2D1A;
        }
        @media (min-width: 860px) {
            .photo-side { display: block; }
        }

      
        .photo-grid {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1.6fr 1fr 1.4fr;
            gap: 3px;
        }

        .ph {
            overflow: hidden;
            position: relative;
        }
        .ph img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }
        .ph:hover img { transform: scale(1.04); }

        /* crops-2: col 1, rows 1-2 */
        .ph-crops2 {
            grid-column: 1;
            grid-row: 1 / 3;
        }

        /* farmer-1: col 2, row 1 (landscape → slot ancho y bajo) */
        .ph-farmer {
            grid-column: 2;
            grid-row: 1;
        }

        /* cows: col 2, rows 2-3 */
        .ph-cows {
            grid-column: 2;
            grid-row: 2 / 4;
        }

        /* milking: col 1, row 3 */
        .ph-milking {
            grid-column: 1;
            grid-row: 3;
        }

        /* Fallback si no carga la imagen */
        .ph-crops2  { background: #1A4731; }
        .ph-farmer  { background: #2D5C1E; }
        .ph-cows    { background: #1D4A2A; }
        .ph-milking { background: #0F2D1A; }

        /* ── OVERLAY texto encima de las fotos ── */
        .photo-overlay {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(to bottom,
                    rgba(10,30,15,0.35) 0%,
                    rgba(10,30,15,0.05) 35%,
                    rgba(10,30,15,0.05) 60%,
                    rgba(10,30,15,0.72) 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.75rem;
            pointer-events: none;
            z-index: 2;
        }

        .overlay-stats {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .stat-pill {
            background: rgba(255,255,255,0.16);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.22);
            border-radius: 12px;
            padding: 8px 16px;
            text-align: center;
        }
        .stat-num {
            display: block;
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
            line-height: 1;
        }
        .stat-lbl {
            display: block;
            font-size: 0.68rem;
            color: rgba(255,255,255,0.75);
            margin-top: 3px;
            font-weight: 500;
        }

        .overlay-bottom { pointer-events: auto; }
        .overlay-eyebrow {
            color: rgba(255,255,255,0.6);
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .overlay-title {
            color: #fff;
            font-size: 1.55rem;
            font-weight: 800;
            line-height: 1.3;
            font-family: Georgia, serif;
            margin-bottom: 1rem;
            max-width: 300px;
        }
        .overlay-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .overlay-chip {
            background: rgba(29,158,117,0.3);
            border: 1px solid rgba(29,158,117,0.55);
            border-radius: 99px;
            padding: 5px 12px;
            font-size: 0.76rem;
            color: #9FE1CB;
            font-weight: 600;
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body>

<!-- ── HEADER ── -->
<div class="top-bar">
    <img class="logo" src="<?php echo e(asset('img/logo-seedling-fondo-verde.svg')); ?>" alt="Agrogranja"
         onerror="this.style.display='none'">
    <h1>Crear tu cuenta</h1>
    <p>Gestiona tu finca desde hoy</p>
</div>

<!-- ── MAIN ── -->
<div class="main">

    <!-- FORMULARIO -->
    <div class="form-side">

        <?php if(session('error')): ?>
            <div class="alert-error"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('register')); ?>">
            <?php echo csrf_field(); ?>

            <p class="section-label">Datos personales</p>

            <label class="field-label" for="nombre">Nombre completo <span class="required">*</span></label>
            <input class="field-input" type="text" id="nombre" name="nombre"
                   placeholder="Juan Pérez" value="<?php echo e(old('nombre')); ?>" required>

            <label class="field-label" for="email">Correo electrónico <span class="required">*</span></label>
            <input class="field-input" type="email" id="email" name="email"
                   placeholder="juan@finca.com" value="<?php echo e(old('email')); ?>" required>

            <label class="field-label" for="password">Contraseña <span class="required">*</span></label>
            <input class="field-input" type="password" id="password" name="password"
                   placeholder="Mínimo 6 caracteres" required>

            <label class="field-label" for="telefono">Teléfono / WhatsApp</label>
            <input class="field-input" type="text" id="telefono" name="telefono"
                   placeholder="300 123 4567" value="<?php echo e(old('telefono')); ?>">

            <p class="section-label">Tu finca</p>

            <label class="field-label" for="nombre_finca">Nombre de la finca</label>
            <input class="field-input" type="text" id="nombre_finca" name="nombre_finca"
                   placeholder="Finca El Paraíso" value="<?php echo e(old('nombre_finca')); ?>">

            <div class="field-row">
                <div>
                    <label class="field-label" for="departamento">Departamento</label>
                    <select class="field-input" id="departamento" name="departamento">
                        <option value="">Seleccionar</option>
                        <option value="Antioquia"          <?php echo e(old('departamento')=='Antioquia' ?'selected':''); ?>>Antioquia</option>
                        <option value="Atlántico"          <?php echo e(old('departamento')=='Atlántico' ?'selected':''); ?>>Atlántico</option>
                        <option value="Bolívar"            <?php echo e(old('departamento')=='Bolívar' ?'selected':''); ?>>Bolívar</option>
                        <option value="Boyacá"             <?php echo e(old('departamento')=='Boyacá' ?'selected':''); ?>>Boyacá</option>
                        <option value="Caldas"             <?php echo e(old('departamento')=='Caldas' ?'selected':''); ?>>Caldas</option>
                        <option value="Caquetá"            <?php echo e(old('departamento')=='Caquetá' ?'selected':''); ?>>Caquetá</option>
                        <option value="Cauca"              <?php echo e(old('departamento')=='Cauca' ?'selected':''); ?>>Cauca</option>
                        <option value="Cesar"              <?php echo e(old('departamento')=='Cesar' ?'selected':''); ?>>Cesar</option>
                        <option value="Córdoba"            <?php echo e(old('departamento')=='Córdoba' ?'selected':''); ?>>Córdoba</option>
                        <option value="Cundinamarca"       <?php echo e(old('departamento')=='Cundinamarca' ?'selected':''); ?>>Cundinamarca</option>
                        <option value="Huila"              <?php echo e(old('departamento')=='Huila' ?'selected':''); ?>>Huila</option>
                        <option value="Magdalena"          <?php echo e(old('departamento')=='Magdalena' ?'selected':''); ?>>Magdalena</option>
                        <option value="Meta"               <?php echo e(old('departamento')=='Meta' ?'selected':''); ?>>Meta</option>
                        <option value="Nariño"             <?php echo e(old('departamento')=='Nariño' ?'selected':''); ?>>Nariño</option>
                        <option value="Norte de Santander" <?php echo e(old('departamento')=='Norte de Santander' ?'selected':''); ?>>Norte de Santander</option>
                        <option value="Risaralda"          <?php echo e(old('departamento')=='Risaralda' ?'selected':''); ?>>Risaralda</option>
                        <option value="Santander"          <?php echo e(old('departamento')=='Santander' ?'selected':''); ?>>Santander</option>
                        <option value="Sucre"              <?php echo e(old('departamento')=='Sucre' ?'selected':''); ?>>Sucre</option>
                        <option value="Tolima"             <?php echo e(old('departamento')=='Tolima' ?'selected':''); ?>>Tolima</option>
                        <option value="Valle del Cauca"    <?php echo e(old('departamento')=='Valle del Cauca' ?'selected':''); ?>>Valle del Cauca</option>
                        <option value="Otro"               <?php echo e(old('departamento')=='Otro' ?'selected':''); ?>>Otro</option>
                    </select>
                </div>
                <div>
                    <label class="field-label" for="municipio">Municipio</label>
                    <input class="field-input" type="text" id="municipio" name="municipio"
                           placeholder="Tu municipio" value="<?php echo e(old('municipio')); ?>">
                </div>
            </div>

            <button type="submit" class="btn-submit">Crear cuenta 🚀</button>
        </form>

        <div class="login-link">
            ¿Ya tienes cuenta? <a href="<?php echo e(route('login')); ?>">Inicia sesión</a>
        </div>
    </div>

    <!-- PANEL FOTOS -->
    <div class="photo-side">

        <div class="photo-grid">

            <!-- Col izq, filas 1-2: crops-2 (vertical, montaña/finca) -->
            <div class="ph ph-crops2">
                <img src="<?php echo e(asset('img/galeria/arroz.jpg')); ?>" alt="Finca colombiana">
            </div>

            <!-- Col der, fila 1: farmer-1 (horizontal, productor en campo) -->
            <div class="ph ph-farmer">
                <img src="<?php echo e(asset('img/galeria/vaca.jpg')); ?>" alt="Productor en su finca">
            </div>

            <!-- Col der, filas 2-3: cows (vertical, vacas en corral) -->
            <div class="ph ph-cows">
                <img src="<?php echo e(asset('img/galeria/crops-2.jpg')); ?>" alt="Ganadería">
            </div>

            <!-- Col izq, fila 3: milking (vertical oscuro, ordeño) -->
            <div class="ph ph-milking">
                <img src="<?php echo e(asset('img/galeria/fields.jpg')); ?>" alt="Producción lechera">
            </div>

        </div>

        <!-- OVERLAY con stats + texto motivacional -->
        <div class="photo-overlay">

            <div class="overlay-stats">
                <div class="stat-pill">
                    <span class="stat-num">100%</span>
                    <span class="stat-lbl">Gratis</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-num">+20</span>
                    <span class="stat-lbl">Módulos</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-num">☁️</span>
                    <span class="stat-lbl">En la nube</span>
                </div>
            </div>

            <div class="overlay-bottom">
                <p class="overlay-eyebrow">Hecho para Colombia</p>
                <h2 class="overlay-title">Tu finca más productiva empieza aquí</h2>
                <div class="overlay-chips">
                    <span class="overlay-chip">🌱 Cultivos y cosechas</span>
                    <span class="overlay-chip">🐄 Control animal</span>
                    <span class="overlay-chip">💰 Finanzas al día</span>
                    <span class="overlay-chip">📊 Reportes PDF</span>
                    <span class="overlay-chip">👥 Empleados</span>
                    <span class="overlay-chip">📦 Inventario</span>
                </div>
            </div>

        </div>
    </div>

</div>
</body>
</html><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/auth/register.blade.php ENDPATH**/ ?>