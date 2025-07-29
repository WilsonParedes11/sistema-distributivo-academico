<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->unique();
            $table->foreignId('campus_id')->constrained('campus');
            $table->foreignId('carrera_id')->nullable()->constrained('carreras');
            $table->string('edificio')->nullable();
            $table->integer('capacidad');
            $table->enum('tipo', ['aula', 'laboratorio', 'taller', 'auditorio']);
            $table->json('recursos_disponibles')->nullable(); // proyector, computadoras, etc.
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('aulas');
    }
};
