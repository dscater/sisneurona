<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DiagnosticoCNNController extends Controller
{

    public function diagnosticar(Request $request)
    {
        $request->validate([
            "archivo_edf" => ["required", "file", function ($attribute, $value, $fail) {
                if (strtolower($value->getClientOriginalExtension()) !== 'edf') {
                    $fail('Debes cargar un archivo de tipo .EDF');
                }
            },],
        ], [
            "archivo_edf.required" => "Debes cargar un archivo",
            "archivo_edf.file" => "Debes cargar un archivo",
            "archivo_edf.mimes" => "Debes cargar un archivo de tipo .EDF",
        ]);

        // Guardar archivo_edf en public/files/diagnosticos
        $nombre = time() . '_' . $request->file('archivo_edf')->getClientOriginalName();
        $request->file('archivo_edf')->move(public_path('files/diagnosticos'), $nombre);
        $rutaArchivo = public_path('files/diagnosticos/' . $nombre);

        // Ruta del script
        $rutaScript = public_path('scripts/DiagnosticoCNN.py');

        $python = 'C:\Users\victo\AppData\Local\Programs\Python\Python310\python.exe';
        // Ejecutar Python
        $process = new Process([$python, $rutaScript, $rutaArchivo]);
        $process->run();

        if (!$process->isSuccessful()) {
            \File::delete($rutaArchivo);
            throw new ProcessFailedException($process);
        }

        $json = trim($process->getOutput());

        $data = json_decode($json, true);
        Log::debug($json);
        $array_tipo_id = [
            "EPILEPSIA" => 1,
            "ENCEFALOPATIAS" => 2,
            "NORMAL" => 3,
        ];

        // Log::debug($data["diagnostico"]);
        // Log::debug($data["senales"]);

        return response()->JSON([
            "tipo_patologia_id" => $array_tipo_id[$data['diagnostico']],
            "diagnostico" => $data['diagnostico'],
            "data" => $data["senales"],
            "confianza" => $data["confianza"]
        ]);
    }
}
