<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PacienteStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "nombre" => "required|regex:/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]+$/u",
            "paterno" => "required|regex:/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]+$/u",
            "materno" => "nullable|regex:/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]+$/u",
            "ci" => "required",
            "ci_exp" => "nullable",
            "fecha_nac" => "required|date",
            "genero" => "required",
            "cel" => "required",
            "dir" => "required|regex:/^[0-9\pL\s\-\#\.,º°\/]+$/u",
            "ocupacion" => "nullable|regex:/^[\pL0-9\s]+$/u",
        ];
    }

    /**
     * Mensajes validacion
     *
     * @return void
     */
    public function messages(): array
    {
        return [
            "nombre.required" => "Debes completar este campo",
            "paterno.required" => "Debes completar este campo",
            "ci.required" => "Debes completar este campo",
            "nacionalidad.required" => "Debes completar este campo",
            "fecha_nac.required" => "Debes completar este campo",
            "fecha_nac.date" => "Debes ingresar una fecha valida",
            "genero.required" => "Debes completar este campo",
            "cel.required" => "Debes completar este campo",
            "dir.required" => "Debes completar este campo",
        ];
    }
}
