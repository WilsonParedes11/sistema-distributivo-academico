<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('jornadas', function (Blueprint $table) {
        $table->id();
        $table->string('nombre')->unique();
        $table->time('hora_inicio');
        $table->integer('cantidad_horas');
        $table->integer('duracion_hora');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jornadas');
    }
};
