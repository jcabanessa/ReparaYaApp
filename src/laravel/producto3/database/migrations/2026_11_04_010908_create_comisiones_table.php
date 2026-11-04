<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('comisiones')) {
            Schema::create('comisiones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_gestora_id');
            $table->unsignedInteger('incidencia_id');
            $table->decimal('importe', 10, 2);
            $table->enum('estado', ['Pendiente', 'Pagada'])->default('Pendiente');
            $table->timestamps();

            $table->foreign('empresa_gestora_id')
                  ->references('id')
                  ->on('empresas_gestoras')
                  ->onDelete('cascade');

            $table->foreign('incidencia_id')
                  ->references('id')
                  ->on('incidencias')
                  ->onDelete('cascade');

            $table->index(['empresa_gestora_id', 'estado']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comisiones');
    }
};
