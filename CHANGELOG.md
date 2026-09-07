# Changelog

El formato sigue [Keep a Changelog](https://keepachangelog.com/es/1.1.0/).
El versionado sigue [SemVer](https://semver.org/lang/es/).

## [0.1.0] — 2026-09-07

### Añadido

- MVP de endurecimiento con controles conmutable (todos ON por defecto).
- XML-RPC desactivado vía `xmlrpc_enabled`.
- Eliminación de la versión del núcleo en generador y `ver=` de assets del core.
- Restricción del editor de temas/plugins (capacidades + aviso de `DISALLOW_FILE_EDIT` en `wp-config.php`).
- Throttle de login: 5 fallos / 15 min por IP + identificador (transients en WP, almacén en memoria en tests).
- Cabeceras: `X-Content-Type-Options`, `X-Frame-Options: DENY`, `Referrer-Policy`, `Permissions-Policy`.
- Bloqueo anónimo de `/wp/v2/users` (HTTP 401).
- Contraseñas de aplicación desactivadas, con filtro `or_hardening_allow_application_passwords`.
- Pantalla de ajustes (`manage_options`, nonce, opción `or_hardening_settings` sin autoload).
- `uninstall.php` elimina la opción.
- Tests PHPUnit 10 sin bootstrap de WordPress.

[0.1.0]: https://github.com/oscarrivera/wp-security-hardening-toolkit/releases/tag/v0.1.0
