<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialPaciente extends Model
{
    use HasFactory;

    protected $fillable = [
        "paciente_id",
        "motivo_consulta",
        "historial_enfermedad",
        "antecedentes_personales",
        "antecedentes_familiares",
        "antecedentes_no_personales",
        "examenes_neurologicos",
        "tratamientos",
        "evoluciones",
        "consultas",
        "fecha_registro",
        "status",
    ];

    protected $appends = ["fecha_registro_t"];

    public function getFechaRegistroTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_registro));
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function historial_archivos()
    {
        return $this->hasMany(HistorialArchivo::class, 'historial_paciente_id');
    }
}
