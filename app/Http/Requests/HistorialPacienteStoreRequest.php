<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HistorialArchivoRule;

class HistorialPacienteStoreRequest extends FormRequest
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
            "motivo_consulta" => "required|min:4",
            "historial_enfermedad" => "required|min:4",
            "antecedentes_personales" => "required|min:4",
            "antecedentes_familiares" => "required|min:4",
            "antecedentes_no_personales" => "required|min:4",
            "examenes_neurologicos" => "required|min:4",
            "tratamientos" => "required|min:4",
            "evoluciones" => "required|min:4",
            "consultas" => "required|min:4",
            "historial_archivos" => ["required", "array", "min:1", new HistorialArchivoRule],
        ];
    }

    public function messages(): array
    {
        return [
            "paciente_id.required" => "Debes  seleccionar un paciente",
            "motivo_consulta.required" => "Debes completar este campo",
            "motivo_consulta.min" => "Debes ingresar al menos :min caracteres",
            "historial_enfermedad.required" => "Debes completar este campo",
            "historial_enfermedad.min" => "Debes ingresar al menos :min caracteres",
            "antecedentes_personales.required" => "Debes completar este campo",
            "antecedentes_personales.min" => "Debes ingresar al menos :min caracteres",
            "antecedentes_familiares.required" => "Debes completar este campo",
            "antecedentes_familiares.min" => "Debes ingresar al menos :min caracteres",
            "antecedentes_no_personales.required" => "Debes completar este campo",
            "antecedentes_no_personales.min" => "Debes ingresar al menos :min caracteres",
            "examenes_neurologicos.required" => "Debes completar este campo",
            "examenes_neurologicos.min" => "Debes ingresar al menos :min caracteres",
            "tratamientos.required" => "Debes completar este campo",
            "tratamientos.min" => "Debes ingresar al menos :min caracteres",
            "evoluciones.required" => "Debes completar este campo",
            "evoluciones.min" => "Debes ingresar al menos :min caracteres",
            "consultas.required" => "Debes completar este campo",
            "consultas.min" => "Debes ingresar al menos :min caracteres",
            "historial_archivos.required" => "Debes cargar al menos 1 archivo",
            "historial_archivos.min" => "Debes cargar al menos :min archivo",
        ];
    }
}
