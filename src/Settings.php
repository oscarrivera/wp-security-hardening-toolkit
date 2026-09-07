<?php

declare(strict_types=1);

namespace OrHardening;

final class Settings
{
    public const OPTION_KEY = 'or_hardening_settings';

    public const DISABLE_XMLRPC = 'disable_xmlrpc';
    public const REMOVE_VERSION_LEAK = 'remove_version_leak';
    public const DISABLE_FILE_EDITOR = 'disable_file_editor';
    public const LOGIN_THROTTLE = 'login_throttle';
    public const SECURITY_HEADERS = 'security_headers';
    public const BLOCK_REST_USERS = 'block_rest_users';
    public const DISABLE_APP_PASSWORDS = 'disable_app_passwords';

    /**
     * @var list<string>
     */
    public const KEYS = [
        self::DISABLE_XMLRPC,
        self::REMOVE_VERSION_LEAK,
        self::DISABLE_FILE_EDITOR,
        self::LOGIN_THROTTLE,
        self::SECURITY_HEADERS,
        self::BLOCK_REST_USERS,
        self::DISABLE_APP_PASSWORDS,
    ];

    /**
     * @param array<string, bool> $values
     */
    public function __construct(private array $values)
    {
        $this->values = self::merge($values);
    }

    /**
     * Todos los controles de endurecimiento están activos por defecto.
     *
     * @return array<string, bool>
     */
    public static function defaults(): array
    {
        return [
            self::DISABLE_XMLRPC => true,
            self::REMOVE_VERSION_LEAK => true,
            self::DISABLE_FILE_EDITOR => true,
            self::LOGIN_THROTTLE => true,
            self::SECURITY_HEADERS => true,
            self::BLOCK_REST_USERS => true,
            self::DISABLE_APP_PASSWORDS => true,
        ];
    }

    /**
     * Etiquetas y ayuda para la pantalla de ajustes (castellano).
     *
     * @return array<string, array{label: string, help: string}>
     */
    public static function schema(): array
    {
        return [
            self::DISABLE_XMLRPC => [
                'label' => 'Desactivar XML-RPC',
                'help' => 'Fuerza xmlrpc_enabled a false. Cierra pingbacks y un vector habitual de fuerza bruta.',
            ],
            self::REMOVE_VERSION_LEAK => [
                'label' => 'Ocultar versión de WordPress',
                'help' => 'Vacía el generador y elimina el parámetro ver= coincidente con la versión del núcleo en scripts y estilos.',
            ],
            self::DISABLE_FILE_EDITOR => [
                'label' => 'Desactivar editor de archivos',
                'help' => 'Oculta el editor de temas/plugins y deniega las capacidades. DISALLOW_FILE_EDIT debe definirse en wp-config.php; el plugin no puede declararla a tiempo.',
            ],
            self::LOGIN_THROTTLE => [
                'label' => 'Limitar intentos de acceso',
                'help' => '5 fallos / 15 minutos por IP + identificador. Tras un acceso correcto se limpia el contador.',
            ],
            self::SECURITY_HEADERS => [
                'label' => 'Cabeceras de seguridad',
                'help' => 'X-Content-Type-Options, X-Frame-Options DENY, Referrer-Policy y Permissions-Policy geolocation=().',
            ],
            self::BLOCK_REST_USERS => [
                'label' => 'Bloquear enumeración REST de usuarios',
                'help' => 'Si no hay sesión, /wp/v2/users responde 401. Los usuarios autenticados no se ven afectados.',
            ],
            self::DISABLE_APP_PASSWORDS => [
                'label' => 'Desactivar contraseñas de aplicación',
                'help' => 'Las deja inactivas salvo que el filtro or_hardening_allow_application_passwords devuelva true.',
            ],
        ];
    }

    /**
     * @param array<string, mixed>|null $stored
     * @return array<string, bool>
     */
    public static function merge(?array $stored): array
    {
        $out = self::defaults();
        if (!is_array($stored)) {
            return $out;
        }
        foreach (self::KEYS as $key) {
            if (array_key_exists($key, $stored)) {
                $out[$key] = (bool) $stored[$key];
            }
        }

        return $out;
    }

    /**
     * Checkboxes: ausencia en POST equivale a desactivado.
     *
     * @param array<string, mixed> $post
     * @return array<string, bool>
     */
    public static function fromPost(array $post): array
    {
        $out = [];
        foreach (self::KEYS as $key) {
            $out[$key] = isset($post[$key]) && (string) $post[$key] === '1';
        }

        return $out;
    }

    public static function fromWordPress(): self
    {
        $raw = [];
        if (function_exists('get_option')) {
            $stored = get_option(self::OPTION_KEY, []);
            $raw = is_array($stored) ? $stored : [];
        }

        return new self($raw);
    }

    public function enabled(string $key): bool
    {
        return (bool) ($this->values[$key] ?? false);
    }

    /**
     * @return array<string, bool>
     */
    public function all(): array
    {
        return $this->values;
    }
}
