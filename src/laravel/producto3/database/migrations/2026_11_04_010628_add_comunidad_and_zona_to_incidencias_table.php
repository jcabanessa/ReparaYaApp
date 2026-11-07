<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            if (!Schema::hasColumn('incidencias', 'comunidad_id')) {
                $table->unsignedBigInteger('comunidad_id')->nullable()->after('id');
                $table->foreign('comunidad_id')->references('id')->on('comunidades')->onDelete('set null');
                $table->index('comunidad_id');
            }

            if (!Schema::hasColumn('incidencias', 'zona_id')) {
                $table->unsignedBigInteger('zona_id')->nullable()->after('comunidad_id');
                $table->foreign('zona_id')->references('id')->on('zonas')->onDelete('set null');
                $table->index('zona_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropForeign(['comunidad_id']);
            $table->dropForeign(['zona_id']);
            $table->dropColumn(['comunidad_id', 'zona_id']);
        });
    }
};
