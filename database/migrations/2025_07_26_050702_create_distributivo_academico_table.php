<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('distributivo_academico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_academico_id')->constrained('periodos_academicos');
            $table->foreignId('docente_id')->constrained('docentes');
            $table->foreignId('asignatura_id')->constrained('asignaturas');
            $table->foreignId('carrera_id')->constrained('carreras');
            $table->foreignId('campus_id')->constrained('campus');
            $table->string('paralelo');
            $table->integer('semestre');
            $table->enum('jornada', ['matutina', 'vespertina', 'nocturna', 'intensiva']);
            $table->integer('horas_componente_practico')->default(0);
            $table->integer('horas_clase_semana');
            $table->integer('horas_actividades_docencia');
            $table->integer('horas_investigacion_semanal')->default(0);
            $table->string('nombre_proyecto_investigacion')->nullable();
            $table->integer('horas_direccion_academica_semanal')->default(0);
            $table->string('detalle_horas_direccion')->nullable();
            $table->integer('total_horas_semanales');
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índice único para evitar duplicados
            $table->unique([
                'periodo_academico_id',
                'docente_id',
                'asignatura_id',
                'carrera_id',
                'campus_id',
                'paralelo'
            ], 'distributivo_unico');
        });
    }

    public function down()
    {
        Schema::dropIfExists('distributivo_academico');
    }
};
