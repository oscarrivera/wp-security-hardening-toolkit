# OR Hardening

Plugin WordPress de endurecimiento. Activa un conjunto pequeño de controles comprobables: cierra XML-RPC, recorta fugas de versión, limita el editor de archivos, aplica throttle de login, envía cabeceras HTTP, bloquea la enumeración REST de usuarios y desactiva las contraseñas de aplicación. Cada control se puede apagar. Por defecto todos están **activos**.

No sustituye un WAF, un antivirus ni una política de copias de seguridad.

## Requisitos

- WordPress 6.4 o superior
- PHP 8.1 o superior

## Instalación

1. Copie el directorio del plugin a `wp-content/plugins/or-hardening`. El slug del directorio debe ser `or-hardening`.
2. En el escritorio: *Plugins → Plugins instalados → OR Hardening → Activar*.
3. Ajustes: *Ajustes → OR Hardening*.
4. Si activa «Desactivar editor de archivos», añada en `wp-config.php` (antes de `That's all, stop editing!`):

```php
define('DISALLOW_FILE_EDIT', true);
```

WordPress carga las constantes de `wp-config.php` antes que cualquier plugin. Definir `DISALLOW_FILE_EDIT` desde el plugin llega tarde.

## Controles

Opción almacenada: `or_hardening_settings` (autoload desactivado). Capacidad: `manage_options`. El formulario usa nonce.

| Control | Clave | Por defecto | Qué hace |
| --- | --- | --- | --- |
| Desactivar XML-RPC | `disable_xmlrpc` | ON | Filtro `xmlrpc_enabled` → `false`. |
| Ocultar versión | `remove_version_leak` | ON | Vacía `the_generator`, quita `wp_generator` y el `ver=` de scripts/estilos cuando coincide con la versión del núcleo. |
| Editor de archivos | `disable_file_editor` | ON | Deniega `edit_themes`, `edit_plugins`, `edit_files` y oculta los submenús. La constante debe ir en `wp-config.php`. |
| Throttle de login | `login_throttle` | ON | 5 fallos / 15 min por IP (`REMOTE_ADDR`) + identificador. Transients en WordPress. |
| Cabeceras | `security_headers` | ON | `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy: geolocation=()`. |
| REST usuarios | `block_rest_users` | ON | Anónimo en `/wp/v2/users` (y recursos bajo esa ruta) → HTTP 401. |
| Contraseñas de aplicación | `disable_app_passwords` | ON | `wp_is_application_passwords_available` → `false`, salvo el filtro de escape. |

### Filtro de escape (contraseñas de aplicación)

```php
add_filter('or_hardening_allow_application_passwords', static fn (): bool => true);
```

Si el filtro devuelve `true`, se respeta el valor que WordPress iba a devolver.

El throttle **no** confía en `X-Forwarded-For`. Detrás de un proxy hay que normalizar `REMOTE_ADDR` en la capa del servidor.

Al desinstalar el plugin se elimina `or_hardening_settings`.

## Lo que no hace

- No es un WAF ni un cortafuegos de aplicación.
- No escanea malware ni ficheros modificados.
- No corrige vulnerabilidades de temas o plugins de terceros.
- No aporta 2FA, captcha ni política de contraseñas.
- No mitiga DDoS ni fuerza bruta distribuida a gran escala.
- No gestiona copias de seguridad ni actualizaciones.

## Desarrollo y tests

Los tests unitarios **no** arrancan WordPress. Cubren clases puras (`LoginThrottle`, `RestUserGuard`, `SecurityHeaders`).

```bash
composer install
vendor/bin/phpunit
```

Si no hay PHP local:

```bash
docker run --rm -v "$PWD":/app -w /app composer:2.7 \
  composer install --no-interaction --prefer-dist
docker run --rm -v "$PWD":/app -w /app php:8.3-cli \
  vendor/bin/phpunit
```

CI: `.github/workflows/php.yml` (setup-php + PHPUnit).

## Licencia

MIT. Compatible con el requisito de WordPress.org de licencia GPL-compatible. El encabezado del plugin declara `License: MIT`. El archivo `LICENSE` es MIT.
