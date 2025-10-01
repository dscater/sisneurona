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
        Schema::create('historial_archivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("historial_paciente_id");
            $table->string("archivo", 300);
            $table->timestamps();

            $table->foreign("historial_paciente_id")->on("historial_pacientes")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_archivos');
    }
};
