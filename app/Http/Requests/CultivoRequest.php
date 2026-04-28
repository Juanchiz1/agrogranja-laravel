<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CultivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('usuario_id');
    }

    public function rules(): array
    {
        return [
            'nombre'        => ['required', 'string', 'min:2', 'max:150'],
            'tipo'          => ['required', 'string', 'max:100'],
            'fecha_siembra' => ['nullable', 'date'],
            'area'          => ['nullable', 'numeric', 'min:0'],
            'unidad'        => ['nullable', 'in:hectareas,metros2,fanegadas,lotes'],
            'estado'        => ['nullable', 'in:activo,cosechado,vendido'],
            'notas'         => ['nullable', 'string', 'max:5000'],
            'imagen'        => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del cultivo es obligatorio.',
            'nombre.min'      => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'      => 'El nombre no puede superar los 150 caracteres.',
            'tipo.required'   => 'Selecciona un tipo de cultivo.',
            'fecha_siembra.date' => 'La fecha de siembra no tiene un formato válido.',
            'area.numeric'    => 'El área debe ser un número.',
            'area.min'        => 'El área no puede ser negativa.',
            'unidad.in'       => 'La unidad de área no es válida.',
            'estado.in'       => 'El estado seleccionado no es válido.',
            'imagen.image'    => 'El archivo debe ser una imagen (jpg, png, webp).',
            'imagen.max'      => 'La imagen no puede superar los 5 MB.',
        ];
    }
}