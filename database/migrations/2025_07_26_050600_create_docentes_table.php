<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('titulo_profesional');
            $table->enum('grado_ocupacional', ['SP1', 'SP2', 'SP3', 'SP4', 'SP5', 'SP6', 'SP7', 'SP8']);
            $table->enum('modalidad_trabajo', ['MT', 'TC'])->nullable(); // Medio Tiempo, Tiempo Completo
            $table->date('fecha_vinculacion');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices
            $table->index('grado_ocupacional');
            $table->index('modalidad_trabajo');
            $table->index('fecha_vinculacion');
        });
    }

    public function down()
    {
        Schema::dropIfExists('docentes');
    }
};
