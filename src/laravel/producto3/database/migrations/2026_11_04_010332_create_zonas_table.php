<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('zonas')) {
            Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('comunidad_id');
            $table->foreign('comunidad_id')
                  ->references('id')
                  ->on('comunidades')
                  ->onDelete('cascade');
            $table->timestamps();
            $table->index('comunidad_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('zonas');
    }
};
