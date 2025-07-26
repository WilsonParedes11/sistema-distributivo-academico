<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributivo_academico_id')->constrained('distributivo_academico');
            $table->enum('dia_semana', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('aula')->nullable();
            $table->string('edificio')->nullable();
            $table->enum('tipo_clase', ['teorica', 'practica', 'laboratorio']);
            $table->timestamps();

            // Índice único para evitar choques de horario por aula
            $table->unique([
                'dia_semana',
                'hora_inicio',
                'hora_fin',
                'aula'
            ], 'horario_aula_unico');
        });
    }

    public function down()
    {
        Schema::dropIfExists('horarios');
    }
};
