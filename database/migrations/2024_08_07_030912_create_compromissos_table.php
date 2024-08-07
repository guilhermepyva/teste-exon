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
        Schema::create('compromissos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultor_codigo');
            $table->date('data')->default('00-00-0000');
            $table->time('hora_inicio')->default('00:00:00');
            $table->time('hora_fim')->default('00:00:00');
            $table->time('intervalo')->default('00:00:00');

            $table->foreign('consultor_codigo')->references('id')->on('consultores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compromissos');
    }
};
