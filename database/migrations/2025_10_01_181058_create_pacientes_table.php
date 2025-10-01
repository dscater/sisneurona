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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string("nombre");
            $table->string("paterno");
            $table->string("materno")->nullable();
            $table->string("ci");
            $table->string("ci_exp");
            $table->date("fecha_nac");
            $table->string("genero");
            $table->string("cel");
            $table->string("dir", 800);
            $table->string("ocupacion", 600);
            $table->date("fecha_registro");
            $table->integer("status")->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
