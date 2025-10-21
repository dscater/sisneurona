<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diagnostico extends Model
{
    use HasFactory;

    protected $fillable = [
        "paciente_id",
        "archivo_edf",
        "diagnostico",
        "tipo_patologia_id",
        "fecha_registro",
        "status",
    ];

    protected $appends = ["fecha_registro_t", "url_archivo_edf"];

    public function getFechaRegistroTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_registro));
    }

    public function getUrlArchivoEdfAttribute()
    {
        return asset("files/diagnosticos/" . $this->archivo_edf);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function tipo_patologia()
    {
        return $this->belongsTo(TipoPatologia::class, 'tipo_patologia_id');
    }
}
