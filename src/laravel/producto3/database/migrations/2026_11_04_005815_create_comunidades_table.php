<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('comunidades')) {
            Schema::create('comunidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('direccion');
            $table->unsignedBigInteger('empresa_gestora_id');
            $table->foreign('empresa_gestora_id')
                  ->references('id')
                  ->on('empresas_gestoras')
                  ->onDelete('cascade');
            $table->timestamps();
            $table->index('empresa_gestora_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comunidades');
    }
};
