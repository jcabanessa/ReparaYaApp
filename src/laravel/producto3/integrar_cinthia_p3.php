<?php
/**
 * Integración aditiva para la rama Cinthia-P3-para-unificacion.
 * Conserva las clases de otros integrantes y añade solo los métodos/ rutas que necesita este módulo.
 * Ejecutar UNA VEZ desde Docker: php integrar_cinthia_p3.php
 */
$base = __DIR__;
$updates = [];
$read = static function(string $relative) use($base):string {
    $path = $base.'/'.$relative;
    if (!is_file($path)) throw new RuntimeException("Archivo base no encontrado: $relative");
    return file_get_contents($path);
};
$replaceOne = static function(string $source,string $old,string $new,string $description):string {
    if (!str_contains($source,$old)) throw new RuntimeException("La rama tiene un contenido diferente en: $description. No se ha modificado nada.");
    return str_replace($old,$new,$source);
};
$appendToClass = static function(string $source,string $methods,string $description):string {
    $position = strrpos($source,'}');
    if ($position === false) throw new RuntimeException("No se puede localizar el cierre de la clase: $description");
    return substr($source,0,$position)."\n".$methods."\n".substr($source,$position);
};

// 1. Rutas: cambiar únicamente los dos endpoints que el grupo dejó como plantilla.
$p='routes/web.php';$s=$read($p);
if (!str_contains($s,"require __DIR__.'/cinthia.php';")) {
    $s=$replaceOne($s,
      "Route::get('/calendario', [AdminController::class, 'calendario'])->name('admin.calendario');",
      "Route::get('/calendario', [\\App\\Http\\Controllers\\CinthiaAdminController::class, 'calendario'])->name('admin.calendario');",
      'ruta del calendario');
    $pattern="~Route::get\('/cliente/avisos', function \(\) \{\s*return view\('cliente\.avisos'\);\s*\}\)->name\('cliente\.avisos'\);~";
    if (!preg_match($pattern,$s)) throw new RuntimeException('La ruta antigua de cliente ya no coincide. No se ha modificado nada.');
    $s=preg_replace_callback($pattern,static fn()=>"Route::get('/cliente/avisos', [\\App\\Http\\Controllers\\ClienteController::class, 'index'])->name('cliente.avisos');",$s,1);
    $s=rtrim($s)."\n\n// Aportaciones de Cinthia: rutas adicionales sin sustituir las de otros integrantes.\nrequire __DIR__.'/cinthia.php';\n";
    $updates[$p]=$s;
}

// 2. Modelo Incidencia: añadir soporte para fecha de servicio, historial y técnico (sin quitar 'técnico').
$p='app/Models/Incidencia.php';$s=$read($p);
if (!str_contains($s,'function historial(')) {
    $s=$replaceOne($s,"        'descripcion',\n", "        'descripcion',\n        'franja_horaria',\n        'telefono_contacto',\n        'finalizada_at',\n",'fillable de Incidencia');
    $s=$replaceOne($s,'    use HasFactory;',"    use HasFactory;\n\n    protected function casts(): array {\n        return ['fecha_servicio'=>'datetime', 'finalizada_at'=>'datetime', 'precio_aplicado'=>'decimal:2'];\n    }",'casts de Incidencia');
    $s=$appendToClass($s,<<<'METHODS'
    public function tecnico() { return $this->belongsTo(Tecnico::class, 'tecnico_id'); }
    public function historial() { return $this->hasMany(HistorialEstado::class, 'incidencia_id')->orderBy('created_at')->orderBy('id'); }
METHODS, $p);
    $updates[$p]=$s;
}

// 3. Modelo EmpresaGestora: incorporar comunidad manteniendo usuario y comisiones existentes.
$p='app/Models/EmpresaGestora.php';$s=$read($p);
if (!str_contains($s,'function comunidades(')) {
    $s=$appendToClass($s,<<<'METHODS'
    public function comunidades() { return $this->hasMany(Comunidad::class, 'empresa_gestora_id'); }
METHODS,$p);
    $updates[$p]=$s;
}

// 4. Modelo Comunidad: mantener el método empresaGestora() del grupo y añadir alias/relación.
$p='app/Models/Comunidad.php';$s=$read($p);
if (!str_contains($s,'function incidencias(')) {
    $s=$appendToClass($s,<<<'METHODS'
    public function gestora() { return $this->belongsTo(EmpresaGestora::class, 'empresa_gestora_id'); }
    public function incidencias() { return $this->hasMany(Incidencia::class, 'comunidad_id'); }
METHODS,$p);
    $updates[$p]=$s;
}

// 5. Menú: solo añadir accesos. No cambiar el diseño ni sustituir la plantilla del grupo.
$p='resources/views/layouts/app.blade.php';$s=$read($p);
if (!str_contains($s,"route('admin.liquidaciones')")) {
    $anchor='<li><a href="{{ route(\'admin.calendario\') }}" class="nav-link">Calendario</a></li>';
    if (str_contains($s,$anchor)) {
        $s=str_replace($anchor,$anchor."\n                    <li><a href=\"{{ route('admin.incidencias') }}\" class=\"nav-link\">Incidencias</a></li>\n                    <li><a href=\"{{ route('admin.comunidades') }}\" class=\"nav-link\">Comunidades</a></li>\n                    <li><a href=\"{{ route('admin.liquidaciones') }}\" class=\"nav-link\">Liquidaciones</a></li>",$s);
    }
    $old='<li><a href="#" class="nav-link">Mis Avisos</a></li>';
    if(str_contains($s,$old))$s=str_replace($old,'<li><a href="{{ route(\'cliente.avisos\') }}" class="nav-link">Mis Avisos</a></li>',$s);
    $updates[$p]=$s;
}

foreach($updates as $p=>$content) {
    $full=$base.'/'.$p;
    if(file_put_contents($full,$content)===false)throw new RuntimeException("No se pudo escribir: $p");
    echo "INTEGRADO: $p\n";
}
echo "Integración preparada. Ahora hay que comprobar sintaxis y rutas ANTES de hacer commit.\n";
