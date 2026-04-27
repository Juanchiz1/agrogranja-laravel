<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agrogranja — Tu finca en la palma de tu mano</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('img/logo-seedling-transparente.svg')); ?>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #1A4731;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── HERO SECTION ── */
        .hero {
            background: linear-gradient(160deg, #2D7A45 0%, #1A4731 45%, #3D6B28 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Decorative background circles */
        .hero::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            pointer-events: none;
        }
        .hero::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -80px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            pointer-events: none;
        }

        .hero-logo {
            width: 150px;
            height: auto;
            margin-bottom: 1.25rem;
            filter: drop-shadow(0 4px 16px rgba(0,0,0,0.3));
        }

        .hero h1 {
            font-size: clamp(2.4rem, 6vw, 3.5rem);
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
            margin-bottom: 0.75rem;
            font-family: Georgia, 'Times New Roman', serif;
        }

        .hero p {
            font-size: clamp(0.95rem, 2.5vw, 1.1rem);
            color: rgba(255,255,255,0.82);
            line-height: 1.6;
            max-width: 420px;
            margin-bottom: 2.2rem;
        }

        .hero-btns {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
            max-width: 380px;
        }

        .btn-primary {
            background: #fff;
            color: #1A4731;
            border: none;
            border-radius: 14px;
            padding: 16px 28px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,0.28); }

        .btn-secondary {
            background: transparent;
            color: #fff;
            border: 2px solid rgba(255,255,255,0.5);
            border-radius: 14px;
            padding: 14px 28px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: border-color 0.15s, background 0.15s;
        }
        .btn-secondary:hover { border-color: #fff; background: rgba(255,255,255,0.08); }

        .demo-hint {
            margin-top: 1.2rem;
            font-size: 0.78rem;
            color: rgba(255,255,255,0.45);
        }

        /* ── FEATURE CHIPS ── */
        .chips {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .chip {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 99px;
            padding: 6px 14px;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.85);
        }

        /* ── PHOTO GALLERY STRIP ── */
        .gallery-strip {
            background: #0F2D1A;
            padding: 0;
            overflow: hidden;
        }

        .gallery-label {
            text-align: center;
            padding: 2.5rem 1rem 1.5rem;
            color: rgba(255,255,255,0.55);
            font-size: 0.75rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 3px;
        }

        @media (min-width: 640px) {
            .gallery-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (min-width: 900px) {
            .gallery-grid { grid-template-columns: repeat(4, 1fr); }
        }

        .gallery-item {
            position: relative;
            aspect-ratio: 4/3;
            overflow: hidden;
        }
        .gallery-item img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
            display: block;
        }
        .gallery-item:hover img { transform: scale(1.07); }

        .gallery-item-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(10,30,15,0.75) 0%, transparent 60%);
            display: flex;
            align-items: flex-end;
            padding: 14px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .gallery-item:hover .gallery-item-overlay { opacity: 1; }
        .gallery-item-overlay span {
            color: #9FE1CB;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Featured large item */
        .gallery-item.tall {
            grid-row: span 2;
            aspect-ratio: auto;
        }

        /* ── FEATURES SECTION ── */
        .features {
            background: linear-gradient(180deg, #0F2D1A 0%, #1A4731 100%);
            padding: 2.5rem 1.25rem 3rem;
        }

        .features-title {
            text-align: center;
            color: rgba(255,255,255,0.6);
            font-size: 0.75rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            max-width: 520px;
            margin: 0 auto;
        }

        @media (min-width: 640px) {
            .features-grid { grid-template-columns: repeat(4, 1fr); max-width: 700px; }
        }

        .feature-card {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 1.1rem 0.75rem;
            text-align: center;
            transition: background 0.2s, transform 0.2s;
        }
        .feature-card:hover {
            background: rgba(255,255,255,0.12);
            transform: translateY(-2px);
        }

        .feature-icon { font-size: 1.8rem; margin-bottom: 8px; display: block; }
        .feature-name {
            color: #9FE1CB;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 3px;
        }
        .feature-desc {
            color: rgba(255,255,255,0.5);
            font-size: 0.72rem;
        }

        /* ── TESTIMONIAL STRIP ── */
        .testimonial {
            background: #1D9E75;
            padding: 2rem 1.5rem;
            text-align: center;
        }
        .testimonial p {
            color: #fff;
            font-size: 1rem;
            font-style: italic;
            max-width: 500px;
            margin: 0 auto 0.75rem;
            line-height: 1.6;
        }
        .testimonial cite {
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
            font-style: normal;
        }

        /* ── FOOTER CTA ── */
        .footer-cta {
            background: #0F2D1A;
            padding: 2.5rem 1.5rem;
            text-align: center;
        }
        .footer-cta p {
            color: rgba(255,255,255,0.55);
            font-size: 0.82rem;
            margin-top: 1rem;
        }
        .footer-cta a { color: #5DCAA5; }
    </style>
</head>
<body>

<!-- ══ HERO ══ -->
<section class="hero">
    <img src="<?php echo e(asset('img/logo-seedling-fondo-verde.svg')); ?>"
     alt="Agrogranja"
     class="hero-logo"
     width="250"
     height="250"
     onerror="this.style.display='none'">

    <h1>Agrogranja</h1>
    <p>Tu finca en la palma de tu mano.<br>Gestiona cultivos, gastos y cosechas.</p>

    <div class="chips">
        <span class="chip">🌱 Cultivos</span>
        <span class="chip">🐄 Animales</span>
        <span class="chip">💰 Finanzas</span>
        <span class="chip">📦 Inventario</span>
        <span class="chip">👥 Empleados</span>
    </div>

    <div class="hero-btns">
        <a href="<?php echo e(route('register')); ?>" class="btn-primary">✨ Crear cuenta gratis</a>
        <a href="<?php echo e(route('login')); ?>" class="btn-secondary">Ya tengo cuenta</a>
    </div>

    <p class="demo-hint">Demo: demo@demo.com / demo123</p>
</section>

<!-- ══ PHOTO GALLERY ══ -->
<section class="gallery-strip">
    <p class="gallery-label">Diseñada para el productor colombiano</p>

    <div class="gallery-grid">
        <div class="gallery-item tall">
            <img src="<?php echo e(asset('img/galeria/farmer-1.jpg')); ?>" alt="Productor en su finca"
                 onerror="this.parentElement.style.background='#1D4A2A'">
            <div class="gallery-item-overlay"><span>Ganadería</span></div>
        </div>
        <div class="gallery-item">
            <img src="<?php echo e(asset('img/galeria/crops-1.jpg')); ?>" alt="Cultivos"
                 onerror="this.parentElement.style.background='#2A5E30'">
            <div class="gallery-item-overlay"><span>Cultivos</span></div>
        </div>
        <div class="gallery-item">
            <img src="<?php echo e(asset('img/galeria/cows.jpg')); ?>" alt="Ganado bovino"
                 onerror="this.parentElement.style.background='#1A3D22'">
            <div class="gallery-item-overlay"><span>Bovinos</span></div>
        </div>
        <div class="gallery-item">
            <img src="<?php echo e(asset('img/galeria/harvest.jpg')); ?>" alt="Cosecha"
                 onerror="this.parentElement.style.background='#2D5C1E'">
            <div class="gallery-item-overlay"><span>Cosechas</span></div>
        </div>
        <div class="gallery-item">
            <img src="<?php echo e(asset('img/galeria/farmer-2.jpg')); ?>" alt="Trabajador"
                 onerror="this.parentElement.style.background='#1D4A2A'">
            <div class="gallery-item-overlay"><span>Trabajadores</span></div>
        </div>
        <div class="gallery-item">
            <img src="<?php echo e(asset('img/galeria/sheep.jpg')); ?>" alt="Ovejas"
                 onerror="this.parentElement.style.background='#2A5E30'">
            <div class="gallery-item-overlay"><span>Ovejas</span></div>
        </div>
        <div class="gallery-item">
            <img src="<?php echo e(asset('img/galeria/fields.jpg')); ?>" alt="Campo"
                 onerror="this.parentElement.style.background='#1A3D22'">
            <div class="gallery-item-overlay"><span>Cultivos</span></div>
        </div>
    </div>
</section>

<!-- ══ FEATURES ══ -->
<section class="features">
    <p class="features-title">Todo lo que necesitas en un solo lugar</p>
    <div class="features-grid">
        <div class="feature-card">
            <span class="feature-icon">🌱</span>
            <p class="feature-name">Cultivos</p>
            <p class="feature-desc">Siembras y cosechas</p>
        </div>
        <div class="feature-card">
            <span class="feature-icon">💰</span>
            <p class="feature-name">Gastos</p>
            <p class="feature-desc">Insumos y costos</p>
        </div>
        <div class="feature-card">
            <span class="feature-icon">📅</span>
            <p class="feature-name">Agenda</p>
            <p class="feature-desc">Actividades</p>
        </div>
        <div class="feature-card">
            <span class="feature-icon">📊</span>
            <p class="feature-name">Reportes</p>
            <p class="feature-desc">Análisis y gráficas</p>
        </div>
    </div>
</section>

<!-- ══ FOOTER CTA ══ -->
<section class="footer-cta">
    <a href="<?php echo e(route('register')); ?>" class="btn-primary"
       style="display:inline-block; padding: 14px 40px; border-radius: 14px; background: #1D9E75; color: #fff; text-decoration: none; font-weight: 700; font-size: 1rem;">
        Empezar gratis →
    </a>
    <p>¿Ya tienes cuenta? <a href="<?php echo e(route('login')); ?>">Inicia sesión</a></p>
</section>

</body>
</html><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/auth/welcome.blade.php ENDPATH**/ ?>