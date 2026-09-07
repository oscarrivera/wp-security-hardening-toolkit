# Contribuir

Código PHP 8.1, WordPress 6.4+ en runtime. Los tests no cargan WordPress.

## Entorno

```bash
composer install
vendor/bin/phpunit
```

Sin PHP local, use las imágenes Docker indicadas en el README.

No envíe `vendor/`. No hace falta bootstrap de WP-CLI ni de `wordpress-develop` para la suite unitaria.

## Alcance de un cambio

- Clases de dominio en `src/` sin funciones de WordPress (salvo adaptadores explícitos: `WpTransientStore`, `Plugin`).
- Ganchos WP solo en `inc/hooks.php`: `add_action` / `add_filter`.
- Un control nuevo: clase pura + wrapper en `Plugin` + clave en `Settings` (documente el valor por defecto; el endurecimiento se asume ON).
- Tests en `tests/` contra la clase pura.

## Estilo

- `declare(strict_types=1);`
- PSR-4 `OrHardening\` → `src/`
- Sin dependencias de runtime más allá de PHP y WordPress.
- Castellano en interfaz y documentación. Identificadores de código en inglés.

## Parches

Pull requests pequeñas, un tema por PR. Actualice `CHANGELOG.md` si el cambio es observable. No incluya secretos ni dumps de sitios reales.

Los informes de seguridad van por el proceso de `SECURITY.md`, no por un issue público.
