<?php
// init.php - Escudo global total para la UOC (~uocx9)

if (strpos($_SERVER['HTTP_HOST'] ?? '', 'uoc.edu') !== false) {
    
    // 1. Interceptar redirecciones de PHP (header("Location: ..."))
    ob_start(function($buffer) {
        // Corrige el HTML visual, formularios y enlaces
        $buffer = str_replace('href="/views/', 'href="/~uocx9/views/', $buffer);
        $buffer = str_replace('src="/views/', 'src="/~uocx9/views/', $buffer);
        $buffer = str_replace('href="/css/', 'href="/~uocx9/css/', $buffer);
        $buffer = str_replace('src="/css/', 'src="/~uocx9/css/', $buffer);
        $buffer = str_replace('action="/', 'action="/~uocx9/', $buffer);
        $buffer = str_replace('href="/"', 'href="/~uocx9/"', $buffer);
        return $buffer;
    });
}

// 2. Interceptor global para cabeceras HTTP de redirección
if (strpos($_SERVER['HTTP_HOST'] ?? '', 'uoc.edu') !== false) {
    // Sobrescribimos el comportamiento de las redirecciones si empiezan por /
    foreach (headers_list() as $header) {
        if (stripos($header, 'Location: /') === 0 && stripos($header, '~uocx9') === false) {
            $url = trim(substr($header, 10));
            header("Location: /~uocx9" . $url, true);
            break;
        }
    }
}