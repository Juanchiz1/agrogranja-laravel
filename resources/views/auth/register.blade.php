@extends('layouts.app')
@section('title','Crear cuenta')
@section('content')
<div style="min-height:100vh;background:var(--crema);">
  <div style="background:linear-gradient(135deg,var(--verde-dark),var(--verde-mid));padding:52px 24px 32px;text-align:center;">
    <div style="font-size:3rem;">🌱</div>
    <h2 style="font-family:var(--font-serif);color:#fff;font-size:1.5rem;margin-top:8px;">Crear tu cuenta</h2>
    <p style="color:rgba(255,255,255,.8);font-size:.88rem;">Gestiona tu finca desde hoy</p>
  </div>
  <div class="auth-card" style="border-radius:var(--radius-xl) var(--radius-xl) 0 0;margin-top:-20px;">
    @if($errors->any())
    <div class="alert alert-error mb-3">❌ {{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('register.post') }}">
      @csrf
      <p class="section-title" style="margin-top:0;">Datos personales</p>
      <div class="form-group">
        <label>Nombre completo *</label>
        <input type="text" name="nombre" class="form-control" placeholder="Juan Pérez" required value="{{ old('nombre') }}">
      </div>
      <div class="form-group">
        <label>Correo electrónico *</label>
        <input type="email" name="email" class="form-control" placeholder="juan@finca.com" required value="{{ old('email') }}">
      </div>
      <div class="form-group">
        <label>Contraseña *</label>
        <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
      </div>
      <div class="form-group">
        <label>Teléfono</label>
        <input type="tel" name="telefono" class="form-control" placeholder="300 123 4567" value="{{ old('telefono') }}">
      </div>
      <p class="section-title">Tu finca</p>
      <div class="form-group">
        <label>Nombre de la finca</label>
        <input type="text" name="finca" class="form-control" placeholder="Finca El Paraíso" value="{{ old('finca') }}">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Departamento</label>
          <select name="departamento" class="form-control">
            <option value="">Seleccionar</option>
            @foreach(['Antioquia','Atlántico','Bolívar','Boyacá','Caldas','Caquetá','Cauca','Cesar','Córdoba','Cundinamarca','Huila','Magdalena','Meta','Nariño','Norte de Santander','Quindío','Risaralda','Santander','Sucre','Tolima','Valle del Cauca'] as $dep)
            <option {{ old('departamento') === $dep ? 'selected' : '' }}>{{ $dep }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label>Municipio</label>
          <input type="text" name="municipio" class="form-control" placeholder="Tu municipio" value="{{ old('municipio') }}">
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="padding:13px;font-size:.95rem;margin-top:4px;">Crear cuenta 🚀</button>
    </form>
    <p style="text-align:center;margin-top:16px;font-size:.85rem;color:var(--gris);">
      ¿Ya tienes cuenta? <a href="{{ route('login') }}" style="color:var(--verde-dark);font-weight:700;">Ingresar</a>
    </p>
  </div>
</div>
@endsection
