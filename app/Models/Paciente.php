<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable  = [
        "nombre",
        "paterno",
        "materno",
        "ci",
        "ci_exp",
        "fecha_nac",
        "genero",
        "cel",
        "dir",
        "ocupacion",
        "fecha_registro",
        "status",
    ];

    protected $appends = ["fecha_registro_t", 'fecha_nac_t', 'full_name', 'full_ci'];

    public function getFechaRegistroTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_registro));
    }

    public function getFUllCiAttribute()
    {
        return $this->ci . ' ' . $this->ci_exp;
    }

    public function getFechaNacTAttribute()
    {
        return date('d/m/Y', strtotime($this->fecha_nac));
    }

    public function getFullNameAttribute()
    {
        return $this->nombre . ' ' . $this->paterno . ' ' . $this->materno;
    }
}
