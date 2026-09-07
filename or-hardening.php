<?php
/**
 * Plugin Name: OR Hardening
 * Description: Endurece WordPress: XML-RPC, filtrado de versión, editor de archivos, throttle de login, cabeceras HTTP, enumeración REST y contraseñas de aplicación.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Óscar Rivera
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: or-hardening
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('OR_HARDENING_VERSION', '0.1.0');
define('OR_HARDENING_FILE', __FILE__);
define('OR_HARDENING_DIR', __DIR__);

$or_hardening_autoload = __DIR__ . '/vendor/autoload.php';
if (is_file($or_hardening_autoload)) {
    require_once $or_hardening_autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'OrHardening\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $file = OR_HARDENING_DIR . '/src/' . $relative . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    });
}

if (!function_exists('or_hardening')) {
    function or_hardening(): \OrHardening\Plugin
    {
        static $instance = null;
        if (!$instance instanceof \OrHardening\Plugin) {
            $instance = \OrHardening\Plugin::create();
        }

        return $instance;
    }
}

register_activation_hook(__FILE__, static function (): void {
    \OrHardening\Plugin::activate();
});

require_once __DIR__ . '/inc/hooks.php';
