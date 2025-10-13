<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TipoPatologiaUpdateRequest extends FormRequest
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
            "nombre" => "required|unique:tipo_patologias,nombre," . $this->id,
            "descripcion" => "nullable",
        ];
    }

    public function messages(): array
    {
        return [
            "nombre.required" => "Debes completar este campo",
            "nombre.unique" => "Este nombre no esta disponible",
        ];
    }
}
