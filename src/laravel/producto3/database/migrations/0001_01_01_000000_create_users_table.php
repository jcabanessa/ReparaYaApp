<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('usuarios')) Schema::create('usuarios',function(Blueprint $t){
            $t->increments('id'); $t->string('nombre',100); $t->string('email',100)->unique();
            $t->string('password'); $t->enum('rol',['admin','tecnico','particular','gestora'])->default('particular');
            $t->string('telefono',20)->nullable(); $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->nullable(); $t->rememberToken();
        });
        // Nunca borra ni recrea un usuario importado de P2.
    }
    public function down(): void { /* Datos heredados: rollback destructivo deshabilitado intencionalmente. */ }
};
