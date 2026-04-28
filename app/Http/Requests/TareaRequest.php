<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TareaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('usuario_id');
    }

    public function rules(): array
    {
        return [
            'titulo'     => ['required', 'string', 'min:2', 'max:200'],
            'tipo'       => ['nullable', 'string', 'max:60'],
            'fecha'      => ['nullable', 'date'],
            'hora'       => ['nullable', 'date_format:H:i'],
            'prioridad'  => ['nullable', 'in:baja,media,alta'],
            'notas'      => ['nullable', 'string', 'max:5000'],
            'responsable'=> ['nullable', 'string', 'max:100'],
            'cultivo_id' => ['nullable', 'integer'],
            'animal_id'  => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'El título de la tarea es obligatorio.',
            'titulo.min'      => 'El título debe tener al menos 2 caracteres.',
            'titulo.max'      => 'El título no puede superar los 200 caracteres.',
            'fecha.date'      => 'La fecha no tiene un formato válido.',
            'hora.date_format'=> 'La hora debe tener el formato HH:MM.',
            'prioridad.in'    => 'La prioridad debe ser baja, media o alta.',
        ];
    }
}