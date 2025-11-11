<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class HistorialArchivoRule implements ValidationRule
{
    /**
     * Tipos de archivos permitidos.
     */
    protected $tiposPermitidos = ['application/pdf', 'biosig/edf', "biosig/EDF"];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if (!is_array($value)) {
            $fail("Debes enviar un array de archivos(imagenes)");
            return;
        }

        foreach ($value as $archivo) {
            if ($archivo["id"] == 0) {
                if (is_string($archivo["file"])) {
                    $fail("El archivo {$archivo->getClientOriginalName()} no es válido.");
                    return;
                }

                // Validar tamaño (Máximo 100MB)
                if ($archivo["file"]->getSize() > 100 * 1024 * 1024) {
                    $fail("El archivo {$archivo["file"]->getClientOriginalName()} supera los 8MB.");
                    return;
                }

                // Validar tipo MIME
                if (!in_array($archivo["file"]->getMimeType(), $this->tiposPermitidos)) {
                    $fail("El archivo {$archivo["file"]->getClientOriginalName()} debe ser un archivo válido (pdf).");
                    return;
                }
            }
        }
    }
}
