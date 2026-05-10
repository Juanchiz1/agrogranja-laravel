@extends('layouts.app')
@section('title','Diagnóstico inicial')
@section('page_title','📋 Antes de continuar')

@section('content')
<style>
.diag-wrap {
    max-width: 560px;
    margin: 0 auto;
    padding: 0 0 6rem;
}
.diag-intro {
    background: var(--verde-bg);
    border-radius: var(--radius-lg);
    padding: 1.1rem 1.25rem;
    margin-bottom: 1.25rem;
    border-left: 3px solid var(--verde-dark);
}
.diag-intro h5 {
    color: var(--verde-dark);
    font-weight: 700;
    margin: 0 0 5px;
    font-size: 1rem;
}
.diag-intro p {
    color: var(--text-secondary);
    font-size: .85rem;
    margin: 0;
    line-height: 1.55;
}
.diag-seccion {
    background: var(--surface);
    border-radius: var(--radius-lg);
    padding: 1.1rem 1.2rem;
    margin-bottom: .9rem;
    box-shadow: var(--shadow-sm);
}
.diag-num {
    display: inline-block;
    font-size: .68rem;
    font-weight: 700;
    color: var(--verde-dark);
    background: var(--verde-bg);
    padding: 1px 7px;
    border-radius: 99px;
    margin-right: 6px;
    vertical-align: middle;
}
.diag-label {
    font-size: .9rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: .85rem;
    display: block;
    line-height: 1.4;
}
.diag-label em {
    font-style: italic;
    color: var(--verde-dark);
    font-weight: 700;
}
.diag-options {
    display: flex;
    flex-direction: column;
    gap: 7px;
}
.diag-opt {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--bg);
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 9px 12px;
    cursor: pointer;
    font-size: .88rem;
    color: var(--text-primary);
    transition: border-color .15s, background .15s;
    user-select: none;
}
.diag-opt:has(input:checked) {
    border-color: var(--verde-dark);
    background: var(--verde-bg);
    font-weight: 600;
}
.diag-opt input[type="radio"] {
    accent-color: var(--verde-dark);
    width: 17px;
    height: 17px;
    flex-shrink: 0;
    cursor: pointer;
    margin: 0;
}
.diag-footer {
    margin-top: 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.diag-omitir {
    text-align: center;
    font-size: .82rem;
    color: var(--text-muted);
    margin-top: 4px;
}
</style>

<div class="diag-wrap">

    <div class="diag-intro">
        <h5>🌱 Ayúdanos a conocerte mejor</h5>
        <p>Antes de continuar, responde 6 preguntas rápidas sobre tu situación <strong>antes</strong> de usar Agrogranja. Tus respuestas son anónimas y nos ayudan a mejorar el software para productores de San Pelayo y la región. Solo toma 2 minutos.</p>
    </div>

    <form method="POST" action="{{ route('diagnostico.store') }}">
        @csrf

        {{-- Pregunta 1: Dispositivo --}}
        <div class="diag-seccion">
            <span class="diag-label">
                <span class="diag-num">1</span>
                ¿Con qué dispositivo accedes principalmente a Agrogranja?
            </span>
            <div class="diag-options">
                @foreach([
                    'Celular básico / antiguo',
                    'Smartphone moderno',
                    'Tablet',
                    'Computador o portátil',
                    'Varios dispositivos'
                ] as $op)
                <label class="diag-opt">
                    <input type="radio" name="d1" value="{{ $op }}" required>
                    {{ $op }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Pregunta 2: Conectividad --}}
        <div class="diag-seccion">
            <span class="diag-label">
                <span class="diag-num">2</span>
                ¿Cómo es tu acceso a internet <em>en la finca</em>?
            </span>
            <div class="diag-options">
                @foreach([
                    'Bueno — siempre conectado',
                    'Intermitente — a veces falla',
                    'Solo en el casco urbano / pueblo',
                    'No tengo internet en la finca'
                ] as $op)
                <label class="diag-opt">
                    <input type="radio" name="d2" value="{{ $op }}" required>
                    {{ $op }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Pregunta 3: Alfabetización digital --}}
        <div class="diag-seccion">
            <span class="diag-label">
                <span class="diag-num">3</span>
                <em>Antes</em> de Agrogranja, ¿qué tan cómodo te sentías usando apps o tecnología?
            </span>
            <div class="diag-options">
                @foreach([
                    'No usaba tecnología',
                    'Solo llamadas y WhatsApp',
                    'Usaba algunas apps básicas',
                    'Me manejo bien con tecnología'
                ] as $op)
                <label class="diag-opt">
                    <input type="radio" name="d3" value="{{ $op }}" required>
                    {{ $op }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Pregunta 4: Método de registro previo --}}
        <div class="diag-seccion">
            <span class="diag-label">
                <span class="diag-num">4</span>
                ¿Cómo registrabas la información de tu finca <em>antes</em> de Agrogranja?
            </span>
            <div class="diag-options">
                @foreach([
                    'En cuadernos o papel',
                    'Solo en la memoria',
                    'En Excel o hojas de cálculo',
                    'En otra aplicación',
                    'No registraba nada'
                ] as $op)
                <label class="diag-opt">
                    <input type="radio" name="d4" value="{{ $op }}" required>
                    {{ $op }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Pregunta 5: Ingresos anteriores --}}
        <div class="diag-seccion">
            <span class="diag-label">
                <span class="diag-num">5</span>
                Aproximadamente, ¿cuánto ganabas por mes con la finca <em>antes</em> de usar Agrogranja?
            </span>
            <div class="diag-options">
                @foreach([
                    'Menos de $500.000',
                    'Entre $500.000 y $1.000.000',
                    'Entre $1.000.000 y $3.000.000',
                    'Más de $3.000.000',
                    'No lo tenía claro'
                ] as $op)
                <label class="diag-opt">
                    <input type="radio" name="d5" value="{{ $op }}" required>
                    {{ $op }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Pregunta 6: Gastos anteriores --}}
        <div class="diag-seccion">
            <span class="diag-label">
                <span class="diag-num">6</span>
                ¿Cuánto gastabas mensualmente en la finca (insumos, personal, etc.) <em>antes</em>?
            </span>
            <div class="diag-options">
                @foreach([
                    'Menos de $200.000',
                    'Entre $200.000 y $500.000',
                    'Entre $500.000 y $1.000.000',
                    'Más de $1.000.000',
                    'No lo tenía claro'
                ] as $op)
                <label class="diag-opt">
                    <input type="radio" name="d6" value="{{ $op }}" required>
                    {{ $op }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="diag-footer">
            <button type="submit" class="btn btn-primary btn-full">
                ✅ Enviar y continuar al inicio
            </button>
        </div>
    </form>

    <div class="diag-omitir">
        <form method="POST" action="{{ route('diagnostico.omitir') }}">
            @csrf
            <button type="submit" class="btn btn-ghost" style="font-size:.82rem;color:var(--text-muted);">
                Omitir por ahora →
            </button>
        </form>
    </div>

</div>
@endsection