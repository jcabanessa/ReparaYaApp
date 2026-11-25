# ReparaYa — Producto 3 (Laravel 13) · versión independiente para Cinthia-P3

## Origen y alcance

Esta carpeta se ha construido a partir del esqueleto Laravel del ZIP entregado por la estudiante y del SQL del Producto 2. Es **una implementación independiente completa**, no una copia exacta de `main` ni una fusión automática de las ramas `unificacion`, `Jordi-P3-Laravel` y `merce-p3`. Antes de integrar en el trabajo grupal hay que comparar cambios.

Incluye: registro/login y perfil, roles, CRUD clientes, administración, técnicos/servicios, calendario mes/semana/día, gestoras/comunidades, comisiones y liquidaciones, JSON `/api/servicios/zonas` y migraciones para la estructura original + B2B.

**No contiene credenciales privadas ni el directorio vendor**. No se ha ejecutado sobre el servidor AWS ni sobre la base de datos del grupo. Las pruebas de navegador y de DB deben hacerse en una copia de desarrollo.

## Compatibilidad

- Laravel según `composer.lock` del esqueleto proporcionado, PHP >= 8.3. El Dockerfile original usa PHP 8.3.
- MySQL 8.0; las tablas originales se llaman `usuarios`, `tecnicos`, `especialidades`, `incidencias`, `historial_estados`.
- Para una copia importada con prefijo `laravel_`, establece `DB_PREFIX=laravel_` **sin incluir el prefijo en los modelos**. El `config/database.php` de este paquete ya contempla la variable.
- `comunidades.zona` es una columna de texto, sin tabla separada `zonas`, como acordó el grupo el 14 y 24 de noviembre.
- Las tablas existentes se respetan con migraciones que comprueban su existencia; aun así, **haz respaldo antes de ejecutar cualquier migración**.
- La copia Laravel del grupo puede contener columnas y relaciones distintas. Comparar el esquema real ANTES de ejecutar comandos.

## Instalación local con Docker de P1

1. En tu copia local, crear rama `Cinthia-P3` desde una base **actualizada** que el grupo acuerde (`unificacion` o `main`). No copiar sobre `main` directamente. Revisa `git status` antes.
2. Comparar con el paquete y fusionar solo los cambios que correspondan; si quieres probar esta versión íntegra, usa otra carpeta o rama aislada.
3. Desde `src/laravel/producto3` ejecutar `composer install --no-interaction` (no `composer update`). Crear `.env` a partir de `.env.example` con las credenciales privadas del MySQL de pruebas. No subir `.env`.
4. `php artisan key:generate`; `php artisan optimize:clear`; `php artisan migrate:status`; `php artisan migrate` sobre una base de pruebas **respaldada**, verificando tablas resultantes.
5. Si importas datos históricos en una base con tablas originales sin prefijo y nuevas tablas prefijadas: primero `php artisan p3:importar-p2 --dry-run` y luego `php artisan p3:importar-p2`. Respeta los IDs; no sustituye una comprobación de relaciones.
6. URL local (si el Apache monta `src` en `/var/www/html`): `http://localhost:3000/laravel/producto3/public/`. El DocumentRoot debe apuntar a `public` si se publica en una URL limpia.
7. Inicia sesión con un administrador migrado del P2. Si no existe, créalo con un comando `artisan tinker` en LOCAL siguiendo la pauta de seguridad del grupo, nunca mediante una ruta pública.

## Validación manual obligatoria

1. Particular: registro/login/logout; crear Estándar con más de 48 h; rechazar menos de 48 h; crear Urgente futura; ver solo sus avisos; detalle e historial; cancelar solo cuando falten **más de** 48 horas.
2. Administración: registrar gestora y su usuario, asignar %; registrar comunidad y zona; gestionar usuarios/técnicos/servicios; asignar técnico de especialidad coincidente; editar solo Pendiente/Asignada; calendario mes/semana/día y detalle.
3. Técnico: `/tecnico/agenda`, validar Asignada -> En curso -> Finalizada y rechazar saltos; comprobar que no puede modificar incidencias ajenas.
4. B2B: gestora crea incidencia en propia comunidad, no accede a otras empresas; al finalizar se crea **una sola** comisión y se guarda `fecha_finalizacion` real; no se crean para pendientes ni canceladas; los totales de gestora y administrador coinciden.
5. API: GET `/api/servicios/zonas` devuelve JSON sin datos personales, porcentajes suman ~100; base vacía devuelve `{ "data":[], "total_servicios_realizados":0 }`.
6. Verificar con usuarios de prueba que accesos por URL directa devuelven 403/404 y que acciones POST requieren token CSRF.

## Despliegue AWS (responsable designado del grupo)

No sobrescribir WordPress en la raíz. Publicar **solo la entrada web `public`** de Laravel bajo `/producto3` mediante la configuración web aprobada y mantener el resto de la aplicación fuera del webroot público. Las dependencias se preparan LOCALMENTE con `composer install --no-dev --optimize-autoloader`, y se transfieren junto con `composer.lock`, `vendor`, plantillas y recursos por SSH/SFTP. No ejecutar `composer update`, `sudo` ni instalar paquetes en AWS. Preparar permisos de `storage` y `bootstrap/cache`, y establecer `APP_DEBUG=false`.

## Notas técnicas y decisiones

- En `tecnicos`, `tecnico_id` apunta al **ID de técnico**, que puede diferir de `usuario_id` del login.
- Incidencias finalizadas conservan `precio_aplicado` de creación y `finalizada_at` de cierre; la comisión es idempotente por `incidencia_id` único, con porcentajes y base congelados.
- `comunidad_id` y `empresa_gestora_id` permiten separar datos por gestora. La consulta por zonas incluye `Sin zona` para incidencias particulares: los porcentajes suman 100 % sobre todos los servicios finalizados.
- El alta B2B de un vecino genera una cuenta particular con contraseña aleatoria que no se muestra. Antes de producción debe acordarse un proceso seguro de activación o recuperación de acceso; no enviar contraseñas por WhatsApp.
- No se ha implementado el control de disponibilidad por franja para evitar doble reserva; no está exigido de forma explícita.

## Integración Git sugerida (no ejecutada automáticamente)

```bash
git fetch origin
git switch -c Cinthia-P3 origin/unificacion
# Descomprimir archivos de esta versión de P3 en una carpeta de pruebas y revisar conflictos.
git status
git diff --stat
# Añadir solo archivos revisados, nunca .env, vendor ni secretos.
git add src/laravel/producto3
# git commit -m "P3: ..." y git push -u origin Cinthia-P3 solo cuando pase pruebas.
```
