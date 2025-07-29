<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campus');
            $table->string('nombre');
            $table->string('codigo')->unique();
            $table->enum('tipo', ['tecnica', 'tecnologica']);
            $table->integer('duracion_semestres');
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('carreras');
    }
};
