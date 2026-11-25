<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

Artisan::command('p3:importar-p2 {--dry-run : Solo contar filas}',function(){
    if (DB::getTablePrefix()==='') {
        $this->error('Para copiar las tablas del P2 sin prefijo a la copia Laravel, define DB_PREFIX=laravel_.');return 1;
    }
    $orden=['usuarios','especialidades','tecnicos','incidencias','historial_estados'];
    foreach($orden as $tabla){
        $source=$tabla; $dest=DB::getTablePrefix().$tabla;
        if (!Schema::hasTable($tabla)) { $this->error('Falta tabla destino '.$dest);return 1; }
        // Source unprefixed comprobada como tabla simple de lista blanca.
        $sourceExists=DB::selectOne('SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',[$source]);
        if(!(int)($sourceExists->n??0)){$this->warn('No existe tabla de origen '.$source);continue;}
        $filas=DB::select('SELECT * FROM `'.$source.'`');$this->info($source.': '.count($filas).' registros de origen.');
        if($this->option('dry-run'))continue;
        $cols=Schema::getColumnListing($tabla);
        $permitidas=array_flip($cols);
        // Operación aditiva: evita duplicados por PK y no borra ni sobreescribe registros ya importados.
        foreach(array_chunk($filas,100) as $bloque){
            $datos=array_map(fn($r)=>array_intersect_key((array)$r,$permitidas),$bloque);
            if($datos)DB::table($tabla)->insertOrIgnore($datos);
        }
    }
    $this->info('Importación no destructiva finalizada. Revisar conteos y claves externas.');return 0;
})->purpose('Importar registros del P2 sin sobrescribir los existentes en las tablas Laravel prefijadas.');
