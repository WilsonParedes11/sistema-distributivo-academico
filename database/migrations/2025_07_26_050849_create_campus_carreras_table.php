<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('campus_carreras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campus');
            $table->foreignId('carrera_id')->constrained('carreras');
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->unique(['campus_id', 'carrera_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('campus_carreras');
    }
};
