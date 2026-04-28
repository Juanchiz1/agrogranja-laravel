<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('usuario_id');
    }

    public function rules(): array
    {
        return [
            'nombre'        => ['required', 'string', 'min:2', 'max:150'],
            'tipo'          => ['required', 'in:trabajador,proveedor,comprador,vecino,familiar,contacto,otro'],
            'telefono'      => ['nullable', 'string', 'max:30'],
            'email'         => ['nullable', 'email', 'max:150'],
            'documento'     => ['nullable', 'string', 'max:30'],
            'direccion'     => ['nullable', 'string', 'max:255'],
            'cargo'         => ['nullable', 'string', 'max:100'],
            'tipo_contrato' => ['nullable', 'in:jornal,mensual,destajo,temporal,otro'],
            'valor_jornal'  => ['nullable', 'numeric', 'min:0'],
            'valor_mensual' => ['nullable', 'numeric', 'min:0'],
            'fecha_ingreso' => ['nullable', 'date'],
            'labores'       => ['nullable', 'string', 'max:255'],
            'notas'         => ['nullable', 'string', 'max:5000'],
            'foto'          => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'   => 'El nombre de la persona es obligatorio.',
            'nombre.min'        => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'        => 'El nombre no puede superar los 150 caracteres.',
            'tipo.required'     => 'Selecciona el tipo de persona.',
            'tipo.in'           => 'El tipo de persona seleccionado no es válido.',
            'email.email'       => 'El correo electrónico no tiene un formato válido.',
            'tipo_contrato.in'  => 'El tipo de contrato seleccionado no es válido.',
            'valor_jornal.numeric'  => 'El valor del jornal debe ser un número.',
            'valor_mensual.numeric' => 'El valor mensual debe ser un número.',
            'fecha_ingreso.date'    => 'La fecha de ingreso no tiene un formato válido.',
            'foto.image'        => 'El archivo debe ser una imagen (jpg, png, webp).',
            'foto.max'          => 'La imagen no puede superar los 5 MB.',
        ];
    }
}