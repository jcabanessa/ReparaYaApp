<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=h($titulo??'ReparaYa')?> · ReparaYa</title>
<link rel="stylesheet" href="assets/style.css"></head><body>
<header class="site-header"><div class="wrap header-inner"><a class="brand" href="<?=h(url('inicio'))?>">🛠 ReparaYa</a>
<nav aria-label="Navegación principal"><a href="<?=h(url('inicio'))?>">Inicio</a>
<?php $usuario=me(); if (!$usuario): ?>
<a href="<?=h(url('login'))?>">Acceder</a><a href="<?=h(url('registro'))?>">Registrarse</a>
<?php else: ?>
<?php if ($usuario['rol']==='particular'): ?><a href="<?=h(url('cliente_avisos'))?>">Mis avisos</a><a href="<?=h(url('cliente_nueva'))?>">Nueva solicitud</a><?php endif; ?>
<?php if ($usuario['rol']==='admin'): ?><a href="<?=h(url('admin_avisos'))?>">Incidencias</a><a href="<?=h(url('admin_calendario'))?>">Calendario</a><a href="<?=h(url('admin_maestros'))?>">Técnicos y servicios</a><?php endif; ?>
<?php if ($usuario['rol']==='tecnico'): ?><a href="<?=h(url('tecnico_agenda'))?>">Mi agenda</a><?php endif; ?>
<a href="<?=h(url('perfil'))?>">Mi perfil</a><form method="post" action="<?=h(url('salir'))?>" class="inline"><?=csrf_field()?><button class="link-button" type="submit">Salir</button></form>
<?php endif; ?></nav></div></header>
<main class="wrap"><h1><?=h($titulo??'ReparaYa')?></h1>
<?php if(isset($_SESSION['flash'])): [$tipo,$mensaje]=$_SESSION['flash'];unset($_SESSION['flash']); ?>
<div role="alert" class="alert <?= $tipo==='ok'?'success':'error' ?>"><?=h($mensaje)?></div>
<?php endif; ?>
