<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('codigo_estudiante')->unique();
            $table->foreignId('carrera_id')->constrained('carreras');
            $table->foreignId('campus_id')->constrained('campus');
            $table->integer('semestre_actual');
            $table->string('paralelo', 2);
            $table->enum('jornada', ['matutina', 'vespertina', 'nocturna', 'intensiva']);
            $table->date('fecha_ingreso');
            $table->enum('estado', ['activo', 'inactivo', 'graduado', 'retirado'])->default('activo');
            $table->timestamps();

            // Índices
            $table->index(['carrera_id', 'semestre_actual', 'paralelo']);
            $table->index('campus_id');
            $table->index('estado');
            $table->index('jornada');
        });
    }

    public function down()
    {
        Schema::dropIfExists('estudiantes');
    }
};
