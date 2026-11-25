<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Migración aditiva: no elimina ni reconstruye registros del grupo. */
return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('incidencias')) {
            if (!Schema::hasColumn('incidencias','franja_horaria'))
                Schema::table('incidencias',fn(Blueprint $t)=>$t->string('franja_horaria',50)->nullable());
            if (!Schema::hasColumn('incidencias','telefono_contacto'))
                Schema::table('incidencias',fn(Blueprint $t)=>$t->string('telefono_contacto',20)->nullable());
            if (!Schema::hasColumn('incidencias','precio_aplicado'))
                Schema::table('incidencias',fn(Blueprint $t)=>$t->decimal('precio_aplicado',10,2)->default(0));
            if (!Schema::hasColumn('incidencias','updated_at'))
                Schema::table('incidencias',fn(Blueprint $t)=>$t->timestamp('updated_at')->nullable());
            if (!Schema::hasColumn('incidencias','finalizada_at'))
                Schema::table('incidencias',fn(Blueprint $t)=>$t->timestamp('finalizada_at')->nullable());
            // No alterar la longitud de localizador ni el ENUM de estado del grupo.
            // Deben conservarse para evitar cambios incompatibles en datos de Producto 2.
        }
        if (Schema::hasTable('comunidades') && !Schema::hasColumn('comunidades','zona'))
            Schema::table('comunidades',fn(Blueprint $t)=>$t->string('zona',80)->nullable());
        if (Schema::hasTable('usuarios') && !Schema::hasColumn('usuarios','updated_at'))
            Schema::table('usuarios',fn(Blueprint $t)=>$t->timestamp('updated_at')->nullable());
        if (Schema::hasTable('usuarios') && !Schema::hasColumn('usuarios','remember_token'))
            Schema::table('usuarios',fn(Blueprint $t)=>$t->rememberToken());
        if (!Schema::hasTable('historial_estados') && Schema::hasTable('incidencias') && Schema::hasTable('usuarios'))
            Schema::create('historial_estados',function(Blueprint $t){
                $t->increments('id');$t->unsignedInteger('incidencia_id');
                $t->string('estado_anterior',50)->nullable();$t->string('nuevo_estado',50);
                $t->unsignedInteger('usuario_id');$t->text('observaciones')->nullable();
                $t->timestamp('created_at')->useCurrent();
                $t->foreign('incidencia_id')->references('id')->on('incidencias')->cascadeOnDelete();
                $t->foreign('usuario_id')->references('id')->on('usuarios');
            });
    }
    public function down(): void {
        // Intencional: conservar datos, historiales y otras aportaciones del grupo.
    }
};
