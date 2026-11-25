<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void {
        if (!Schema::hasTable('empresas_gestoras')) Schema::create('empresas_gestoras',function(Blueprint $t){
            $t->id();$t->unsignedInteger('usuario_id')->unique();$t->string('nombre',150);$t->string('cif',20)->unique();
            $t->decimal('porcentaje_comision',5,2);$t->timestamps();
            $t->foreign('usuario_id')->references('id')->on('usuarios');
        });
        if (!Schema::hasTable('comunidades')) Schema::create('comunidades',function(Blueprint $t){
            $t->id();$t->foreignId('empresa_gestora_id')->constrained('empresas_gestoras');
            $t->string('nombre',150);$t->string('direccion');$t->string('zona',80);$t->timestamps();
            $t->index(['empresa_gestora_id','zona']);
        });
        if (!Schema::hasColumn('comunidades','zona')) Schema::table('comunidades',fn(Blueprint $t)=>$t->string('zona',80)->default('Sin zona'));
        if (!Schema::hasColumn('incidencias','comunidad_id')) Schema::table('incidencias',function(Blueprint $t){
            $t->unsignedBigInteger('comunidad_id')->nullable();$t->foreign('comunidad_id')->references('id')->on('comunidades');
        });
        if (!Schema::hasColumn('incidencias','empresa_gestora_id')) Schema::table('incidencias',function(Blueprint $t){
            $t->unsignedBigInteger('empresa_gestora_id')->nullable();$t->foreign('empresa_gestora_id')->references('id')->on('empresas_gestoras');
        });
        if (!Schema::hasTable('comisiones')) Schema::create('comisiones',function(Blueprint $t){
            $t->id();$t->foreignId('empresa_gestora_id')->constrained('empresas_gestoras');
            $t->unsignedInteger('incidencia_id')->unique();$t->foreign('incidencia_id')->references('id')->on('incidencias');
            $t->decimal('precio_base_aplicado',10,2);$t->decimal('porcentaje_aplicado',5,2);
            $t->decimal('importe',10,2);$t->dateTime('fecha_finalizacion');
            $t->enum('estado',['Pendiente','Pagada'])->default('Pendiente');$t->timestamps();
            $t->index(['empresa_gestora_id','fecha_finalizacion']);
        });
        foreach(['precio_base_aplicado'=>['decimal',10,2],'porcentaje_aplicado'=>['decimal',5,2]] as $name=>$kind){
            if (!Schema::hasColumn('comisiones',$name)) Schema::table('comisiones',fn(Blueprint $t)=>$t->decimal($name,$kind[1],$kind[2])->default(0));
        }
        if (!Schema::hasColumn('comisiones','fecha_finalizacion')) Schema::table('comisiones',fn(Blueprint $t)=>$t->dateTime('fecha_finalizacion')->nullable());
        // Si el P2 migrado no tenía el campo nuevo de cierre, ya se añadió en 000100.
    }
    public function down():void {/* sin destrucción de registros importados */}
};
