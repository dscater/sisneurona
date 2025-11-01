<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DiagnosticoCNNController extends Controller
{

    public function diagnosticar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:edf',
        ]);

        // guardamos el archivo
        $archivo = $request->file('archivo');
        $rutaArchivo = $archivo->public('files/diagnosticos/', $archivo->getClientOriginalName());

        // Ruta al script de Python
        $rutaScript = public_path('scripts/DiagnosticoCNN.py');

        // Ejecutar el script de Python
        $process = new Process(['python', $rutaScript, $rutaArchivo]);
        $process->run();

        // Verificar si hubo error
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Resultado del diagnóstico (texto devuelto por Python)
        $salida = trim($process->getOutput());
        $resultado = json_decode($salida, true);
        return response()->json($resultado);
    }
}
