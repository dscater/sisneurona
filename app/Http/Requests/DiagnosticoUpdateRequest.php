<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiagnosticoUpdateRequest extends FormRequest
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
            "paciente_id" => "required",
            "diagnostico" => "required",
            "archivo_edf" => "required",
            "tipo_patologia_id" => "required",
        ];
    }

    public function messages()
    {
        return [
            "paciente_id" => "Debes completar este campo",
            "diagnostico" => "Debes completar este campo",
            "archivo_edf" => "Debes completar este campo",
            "tipo_patologia_id" => "Debes completar este campo",
        ];
    }
}
