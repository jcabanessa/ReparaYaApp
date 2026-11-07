<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empresas_gestoras')) {
            Schema::create('empresas_gestoras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('cif')->unique();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas_gestoras');
    }
};
