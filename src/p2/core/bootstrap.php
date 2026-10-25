<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Madrid');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/db.php';
foreach (['User', 'Specialty', 'Technician', 'Incident'] as $model) {
    require_once dirname(__DIR__) . '/models/' . $model . '.php';
}
foreach (['AuthController', 'ClientController', 'AdminController', 'TechnicianController'] as $controller) {
    require_once dirname(__DIR__) . '/controllers/' . $controller . '.php';
}

function db(): PDO { return conectarBD(); }
function h(mixed $value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $route, array $params = []): string { return 'index.php?' . http_build_query(array_merge(['r' => $route], $params)); }
function redirect(string $route, array $params = []): never { header('Location: ' . url($route, $params)); exit; }
function me(): ?array { return $_SESSION['usuario'] ?? null; }
function require_login(): array { $u = me(); if (!$u) { flash('error', 'Inicia sesión para continuar.'); redirect('login'); } return $u; }
function require_role(string ...$roles): array { $u = require_login(); if (!in_array($u['rol'], $roles, true)) { http_response_code(403); render('message', ['titulo' => 'Acceso denegado', 'mensaje' => 'Tu cuenta no tiene permiso para realizar esta acción.']); exit; } return $u; }
function csrf(): string { if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); } return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . h(csrf()) . '">'; }
function check_csrf(): void { if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) { http_response_code(400); exit('Formulario inválido: token CSRF.'); } }
function post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }
function flash(string $type, string $message): void { $_SESSION['flash'] = [$type, $message]; }
function render(string $view, array $data = []): void {
    if (!preg_match('/^[a-z_]+$/', $view)) { throw new RuntimeException('Vista inválida'); }
    extract($data, EXTR_SKIP);
    require dirname(__DIR__) . '/views/layout_start.php';
    require dirname(__DIR__) . '/views/' . $view . '.php';
    require dirname(__DIR__) . '/views/layout_end.php';
}
function int_param(string $key): int { return filter_var($_REQUEST[$key] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0; }
function fail_form(string $message, string $route, array $params = []): never { flash('error', $message); redirect($route, $params); }
function valid_date(string $input): ?DateTimeImmutable {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $input, new DateTimeZone('Europe/Madrid'));
    return $date && $date->format('Y-m-d\TH:i') === $input ? $date : null;
}
function date_service(string $date): string { return (new DateTimeImmutable($date, new DateTimeZone('Europe/Madrid')))->format('d/m/Y H:i'); }
function label_estado(string $estado): string { return '<span class="pill">' . h($estado) . '</span>'; }
