<?php

namespace App\Services;

use App\Models\HistorialPaciente;
use App\Models\HistorialArchivo;
use Illuminate\Http\UploadedFile;

class HistorialArchivoService
{
    private $pathFiles = "";
    public function __construct(private  CargarArchivoService $cargarArchivoService)
    {
        $this->pathFiles = public_path("files/historial_archivos");
    }

    /**
     * Cargar archivo
     *
     * @param HistorialPaciente $historial_archivo
     * @param UploadedFile $foto
     * @return HistorialArchivo
     */
    public function guardarHistorialArchivo(HistorialPaciente $historial_paciente, UploadedFile $archivo, int $index = -1): HistorialArchivo
    {
        $nombre = ($index != -1 ? $index : 0) . $historial_paciente->id . time();
        return $historial_paciente->historial_archivos()->create([
            "archivo" => $this->cargarArchivoService->cargarArchivo($archivo, $this->pathFiles, $nombre)
        ]);
    }

    /**
     * Eliminacion fisica de archivo historial_archivo
     *
     * @param HistorialArchivo $historial_archivo
     * @return void
     */
    public function eliminarHistorialArchivo(HistorialArchivo $historial_archivo): void
    {
        $archivo = $historial_archivo->archivo;
        if ($historial_archivo->delete()) {
            \File::delete($this->pathFiles . "/" . $archivo);
        }
        $historial_archivo->delete();
    }

    /**
     * Obtener extension del nombre de la archivo
     * ejem: image.png -> png
     * 
     * @param string $archivo
     * @return string
     */
    public static function getExtNomHistorialArchivo(string $archivo): string
    {
        $array = explode(".", $archivo);
        return $array[count($array) - 1];
    }
}
