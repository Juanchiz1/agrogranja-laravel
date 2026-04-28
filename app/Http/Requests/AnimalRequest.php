<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('usuario_id');
    }

    public function rules(): array
    {
        return [
            'especie'          => ['required', 'string', 'max:100'],
            'nombre_lote'      => ['nullable', 'string', 'max:150'],
            'cantidad'         => ['nullable', 'integer', 'min:1'],
            'fecha_ingreso'    => ['nullable', 'date'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'estado'           => ['nullable', 'in:activo,vendido,muerte'],
            'peso_promedio'    => ['nullable', 'numeric', 'min:0'],
            'unidad_peso'      => ['nullable', 'in:kg,lb'],
            'ubicacion'        => ['nullable', 'string', 'max:150'],
            'etapa_vida'       => ['nullable', 'string', 'max:20'],
            'precio_kilo'      => ['nullable', 'numeric', 'min:0'],
            'precio_unidad'    => ['nullable', 'numeric', 'min:0'],
            'notas'            => ['nullable', 'string', 'max:5000'],
            'foto'             => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'especie.required'    => 'Selecciona la especie del animal.',
            'especie.max'         => 'La especie no puede superar los 100 caracteres.',
            'cantidad.integer'    => 'La cantidad debe ser un número entero.',
            'cantidad.min'        => 'La cantidad debe ser al menos 1.',
            'fecha_ingreso.date'  => 'La fecha de ingreso no tiene un formato válido.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no tiene un formato válido.',
            'estado.in'           => 'El estado seleccionado no es válido.',
            'peso_promedio.numeric' => 'El peso debe ser un número.',
            'unidad_peso.in'      => 'La unidad de peso debe ser kg o lb.',
            'precio_kilo.numeric' => 'El precio por kilo debe ser un número.',
            'precio_unidad.numeric' => 'El precio por unidad debe ser un número.',
            'foto.image'          => 'El archivo debe ser una imagen (jpg, png, webp).',
            'foto.max'            => 'La imagen no puede superar los 5 MB.',
        ];
    }
}