<?php

namespace App\Models;

use App\Services\HistorialArchivoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialArchivo extends Model
{
    use HasFactory;
    protected $fillable = [
        "historial_paciente_id",
        "archivo",
    ];

    protected $appends = ["url_archivo", "url_file", "name", "ext"];

    public function getExtAttribute()
    {
        $array = explode(".", $this->archivo);
        return $array[1];
    }

    public function getNameAttribute()
    {
        return $this->archivo;
    }

    public function getUrlFileAttribute()
    {
        $array_files = ["jpg", "jpeg", "png", "webp", "gif"];
        $ext = HistorialArchivoService::getExtNomHistorialArchivo($this->archivo);
        if (in_array($ext, $array_files)) {
            return asset("/files/historial_archivos/" . $this->archivo);
        }
        return asset("/imgs/attach.png");
    }

    public function getUrlArchivoAttribute()
    {
        if ($this->archivo) {
            return asset("files/historial_archivos/" . $this->archivo);
        }
        return asset("files/historial_archivos/default.png");
    }

    public function historial_paciente()
    {
        return $this->belongsTo(HistorialPaciente::class, 'historial_paciente_id');
    }
}
