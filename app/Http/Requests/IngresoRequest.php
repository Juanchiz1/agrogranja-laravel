<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IngresoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('usuario_id');
    }

    public function rules(): array
    {
        return [
            'descripcion'    => ['required', 'string', 'max:255'],
            'valor_total'    => ['required', 'numeric', 'min:0'],
            'fecha'          => ['nullable', 'date'],
            'tipo'           => ['nullable', 'string', 'max:50'],
            'cantidad'       => ['nullable', 'numeric', 'min:0'],
            'unidad'         => ['nullable', 'string', 'max:50'],
            'precio_unitario'=> ['nullable', 'numeric', 'min:0'],
            'comprador'      => ['nullable', 'string', 'max:150'],
            'notas'          => ['nullable', 'string', 'max:5000'],
            'foto_soporte'   => ['nullable', 'image', 'max:5120'],
            'cultivo_id'     => ['nullable', 'integer'],
            'animal_id'      => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'descripcion.required'  => 'La descripción del ingreso es obligatoria.',
            'descripcion.max'       => 'La descripción no puede superar los 255 caracteres.',
            'valor_total.required'  => 'El valor total del ingreso es obligatorio.',
            'valor_total.numeric'   => 'El valor debe ser un número.',
            'valor_total.min'       => 'El valor no puede ser negativo.',
            'fecha.date'            => 'La fecha no tiene un formato válido.',
            'precio_unitario.numeric' => 'El precio unitario debe ser un número.',
            'foto_soporte.image'    => 'El archivo debe ser una imagen (jpg, png, webp).',
            'foto_soporte.max'      => 'La imagen no puede superar los 5 MB.',
        ];
    }
}