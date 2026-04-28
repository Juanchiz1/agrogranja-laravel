<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CosechaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('usuario_id');
    }

    public function rules(): array
    {
        return [
            'producto'         => ['required', 'string', 'min:2', 'max:150'],
            'cantidad'         => ['required', 'numeric', 'min:0.01'],
            'unidad'           => ['required', 'string', 'max:50'],
            'fecha_cosecha'    => ['required', 'date'],
            'precio_unitario'  => ['nullable', 'numeric', 'min:0'],
            'valor_estimado'   => ['nullable', 'numeric', 'min:0'],
            'calidad'          => ['nullable', 'in:excelente,buena,regular,baja'],
            'destino'          => ['nullable', 'string', 'max:60'],
            'comprador'        => ['nullable', 'string', 'max:150'],
            'observaciones'    => ['nullable', 'string', 'max:5000'],
            'merma_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'almacen_ubicacion'=> ['nullable', 'string', 'max:150'],
            'almacen_hasta'    => ['nullable', 'date'],
            'foto'             => ['nullable', 'image', 'max:5120'],
            'cultivo_id'       => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'producto.required'      => 'El nombre del producto es obligatorio.',
            'producto.min'           => 'El nombre del producto debe tener al menos 2 caracteres.',
            'cantidad.required'      => 'La cantidad cosechada es obligatoria.',
            'cantidad.numeric'       => 'La cantidad debe ser un número.',
            'cantidad.min'           => 'La cantidad debe ser mayor a cero.',
            'unidad.required'        => 'Selecciona la unidad de medida.',
            'fecha_cosecha.required' => 'La fecha de cosecha es obligatoria.',
            'fecha_cosecha.date'     => 'La fecha de cosecha no tiene un formato válido.',
            'precio_unitario.numeric'=> 'El precio unitario debe ser un número.',
            'calidad.in'             => 'La calidad seleccionada no es válida.',
            'merma_porcentaje.min'   => 'El porcentaje de merma no puede ser negativo.',
            'merma_porcentaje.max'   => 'El porcentaje de merma no puede superar el 100%.',
            'almacen_hasta.date'     => 'La fecha de almacenaje no tiene un formato válido.',
            'foto.image'             => 'El archivo debe ser una imagen (jpg, png, webp).',
            'foto.max'               => 'La imagen no puede superar los 5 MB.',
        ];
    }
}