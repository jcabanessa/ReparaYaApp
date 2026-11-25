<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('especialidades')) Schema::create('especialidades', function(Blueprint $t){
            $t->increments('id'); $t->string('nombre_especialidad',50); $t->decimal('precio_base',10,2)->default(0);
        });
        if (!Schema::hasColumn('especialidades','precio_base')) Schema::table('especialidades',fn(Blueprint $t)=>$t->decimal('precio_base',10,2)->default(0));
        if (!Schema::hasTable('tecnicos')) Schema::create('tecnicos',function(Blueprint $t){
            $t->increments('id'); $t->unsignedInteger('usuario_id')->nullable()->unique();
            $t->string('nombre_completo',100); $t->unsignedInteger('especialidad_id')->nullable();
            $t->boolean('disponible')->default(true); $t->foreign('usuario_id')->references('id')->on('usuarios')->nullOnDelete();
            $t->foreign('especialidad_id')->references('id')->on('especialidades');
        });
        if (!Schema::hasTable('incidencias')) Schema::create('incidencias',function(Blueprint $t){
            $t->increments('id'); $t->string('localizador',13)->unique();
            $t->unsignedInteger('cliente_id'); $t->unsignedInteger('tecnico_id')->nullable();$t->unsignedInteger('especialidad_id');
            $t->text('descripcion'); $t->string('direccion');$t->dateTime('fecha_servicio');
            $t->enum('tipo_urgencia',['Estándar','Urgente'])->default('Estándar');
            $t->enum('estado',['Pendiente','Asignada','En curso','Finalizada','Cancelada'])->default('Pendiente');
            $t->string('franja_horaria',50)->nullable();$t->string('telefono_contacto',20)->nullable();
            $t->decimal('precio_aplicado',10,2)->default(0); $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->nullable();$t->timestamp('finalizada_at')->nullable();
            $t->foreign('cliente_id')->references('id')->on('usuarios');
            $t->foreign('tecnico_id')->references('id')->on('tecnicos');
            $t->foreign('especialidad_id')->references('id')->on('especialidades');
            $t->index(['estado','fecha_servicio']);
        });
        if (!Schema::hasColumn('incidencias','franja_horaria')) Schema::table('incidencias',fn(Blueprint $t)=>$t->string('franja_horaria',50)->nullable());
        if (!Schema::hasColumn('incidencias','telefono_contacto')) Schema::table('incidencias',fn(Blueprint $t)=>$t->string('telefono_contacto',20)->nullable());
        if (!Schema::hasColumn('incidencias','precio_aplicado')) Schema::table('incidencias',fn(Blueprint $t)=>$t->decimal('precio_aplicado',10,2)->default(0));
        if (!Schema::hasColumn('incidencias','updated_at')) Schema::table('incidencias',fn(Blueprint $t)=>$t->timestamp('updated_at')->nullable());
        if (!Schema::hasColumn('incidencias','finalizada_at')) Schema::table('incidencias',fn(Blueprint $t)=>$t->timestamp('finalizada_at')->nullable());
        // Migración aditiva necesaria para instalaciones del SQL P2 sin ampliaciones.
        if (DB::getDriverName()==='mysql') {
            $tabla='`'.DB::getTablePrefix().'incidencias`';
            DB::statement("ALTER TABLE {$tabla} MODIFY COLUMN localizador VARCHAR(13) NOT NULL");
            DB::statement("ALTER TABLE {$tabla} MODIFY COLUMN estado ENUM('Pendiente','Asignada','En curso','Finalizada','Cancelada') NOT NULL DEFAULT 'Pendiente'");
        }
        if (!Schema::hasTable('historial_estados')) Schema::create('historial_estados',function(Blueprint $t){
            $t->increments('id');$t->unsignedInteger('incidencia_id');$t->string('estado_anterior',50)->nullable();
            $t->string('nuevo_estado',50);$t->unsignedInteger('usuario_id');$t->text('observaciones')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->foreign('incidencia_id')->references('id')->on('incidencias')->cascadeOnDelete();
            $t->foreign('usuario_id')->references('id')->on('usuarios');
            $t->index(['incidencia_id','created_at']);
        });
        if (!Schema::hasColumn('usuarios','updated_at')) Schema::table('usuarios',fn(Blueprint $t)=>$t->timestamp('updated_at')->nullable());
        if (!Schema::hasColumn('usuarios','remember_token')) Schema::table('usuarios',fn(Blueprint $t)=>$t->rememberToken());
        // Necesario para roles B2B en instalaciones P2 con enum antiguo.
        if (DB::getDriverName()==='mysql') DB::statement("ALTER TABLE `".DB::getTablePrefix()."usuarios` MODIFY COLUMN rol ENUM('admin','tecnico','particular','gestora') NOT NULL DEFAULT 'particular'");
    }
    public function down():void {/* conservar datos de P2 */}
};
