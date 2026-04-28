<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GastoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('usuario_id');
    }

    public function rules(): array
    {
        return [
            'categoria'       => ['required', 'string', 'max:100'],
            'descripcion'     => ['required', 'string', 'max:255'],
            'valor'           => ['required', 'numeric', 'min:0'],
            'fecha'           => ['nullable', 'date'],
            'cantidad'        => ['nullable', 'numeric', 'min:0'],
            'unidad_cantidad' => ['nullable', 'string', 'max:50'],
            'proveedor'       => ['nullable', 'string', 'max:150'],
            'factura_numero'  => ['nullable', 'string', 'max:100'],
            'notas'           => ['nullable', 'string', 'max:5000'],
            'foto_factura'    => ['nullable', 'image', 'max:5120'],
            'cultivo_id'      => ['nullable', 'integer'],
            'animal_id'       => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'categoria.required'   => 'Selecciona una categoría de gasto.',
            'descripcion.required' => 'La descripción del gasto es obligatoria.',
            'descripcion.max'      => 'La descripción no puede superar los 255 caracteres.',
            'valor.required'       => 'El valor del gasto es obligatorio.',
            'valor.numeric'        => 'El valor debe ser un número.',
            'valor.min'            => 'El valor no puede ser negativo.',
            'fecha.date'           => 'La fecha no tiene un formato válido.',
            'cantidad.numeric'     => 'La cantidad debe ser un número.',
            'foto_factura.image'   => 'El archivo debe ser una imagen (jpg, png, webp).',
            'foto_factura.max'     => 'La imagen no puede superar los 5 MB.',
        ];
    }
}