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
        Schema::table('jornadas', function (Blueprint $table) {
            $table->time('hora_inicio_receso')->nullable()->after('duracion_hora');
            $table->time('hora_fin_receso')->nullable()->after('hora_inicio_receso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jornadas', function (Blueprint $table) {
            $table->dropColumn(['hora_inicio_receso', 'hora_fin_receso']);
        });
    }
};
