<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('historial_pacientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("paciente_id");
            $table->text("motivo_consulta");
            $table->text("historial_enfermedad");
            $table->text("antecedentes_personales");
            $table->text("antecedentes_familiares");
            $table->text("antecedentes_no_personales");
            $table->text("examenes_neurologicos");
            $table->text("tratamientos");
            $table->text("evoluciones");
            $table->text("consultas");
            $table->date("fecha_registro");
            $table->integer("status")->default(1);
            $table->timestamps();

            $table->foreign("paciente_id")->on("pacientes")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_pacientes');
    }
};
