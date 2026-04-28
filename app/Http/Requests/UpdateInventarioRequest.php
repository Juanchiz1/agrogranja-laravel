<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('usuario_id');
    }

    public function rules(): array
    {
        return [
            'nombre'           => ['required', 'string', 'min:2', 'max:150'],
            'categoria'        => ['required', 'string', 'max:100'],
            'stock_minimo'     => ['required', 'numeric', 'min:0'],
            'unidad'           => ['required', 'string', 'max:50'],
            'precio_unitario'  => ['nullable', 'numeric', 'min:0'],
            'proveedor'        => ['nullable', 'string', 'max:150'],
            'fecha_vencimiento'=> ['nullable', 'date'],
            'ubicacion'        => ['nullable', 'string', 'max:150'],
            'uso_principal'    => ['nullable', 'in:cultivo,animal,general'],
            'notas'            => ['nullable', 'string', 'max:5000'],
            'foto'             => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'        => 'El nombre del insumo es obligatorio.',
            'nombre.min'             => 'El nombre debe tener al menos 2 caracteres.',
            'categoria.required'     => 'Selecciona una categoría.',
            'stock_minimo.required'  => 'El stock mínimo es obligatorio.',
            'stock_minimo.numeric'   => 'El stock mínimo debe ser un número.',
            'stock_minimo.min'       => 'El stock mínimo no puede ser negativo.',
            'unidad.required'        => 'La unidad de medida es obligatoria.',
            'precio_unitario.numeric'=> 'El precio debe ser un número.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento no tiene un formato válido.',
            'uso_principal.in'       => 'El uso principal seleccionado no es válido.',
            'foto.image'             => 'El archivo debe ser una imagen (jpg, png, webp).',
            'foto.max'               => 'La imagen no puede superar los 5 MB.',
        ];
    }
}