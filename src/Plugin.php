<?php

declare(strict_types=1);

namespace OrHardening;

final class Plugin
{
    private XmlRpcGuard $xmlRpcGuard;
    private RemoveVersionLeak $removeVersionLeak;
    private FileEditorGuard $fileEditorGuard;
    private SecurityHeaders $securityHeaders;
    private RestUserGuard $restUserGuard;
    private AppPasswordGuard $appPasswordGuard;
    private Clock $clock;
    private ?LoginThrottle $loginThrottle = null;
    private bool $headersEmitted = false;

    public function __construct(
        private Settings $settings,
        private ?FailureStore $failureStore = null,
        ?Clock $clock = null,
    ) {
        $this->clock = $clock ?? new Clock();
        $this->xmlRpcGuard = new XmlRpcGuard();
        $this->removeVersionLeak = new RemoveVersionLeak();
        $this->fileEditorGuard = new FileEditorGuard();
        $this->securityHeaders = new SecurityHeaders();
        $this->restUserGuard = new RestUserGuard();
        $this->appPasswordGuard = new AppPasswordGuard();
    }

    public static function create(): self
    {
        return new self(Settings::fromWordPress(), new WpTransientStore());
    }

    public static function activate(): void
    {
        if (get_option(Settings::OPTION_KEY, false) === false) {
            add_option(Settings::OPTION_KEY, Settings::defaults(), '', false);
        }
    }

    public function filterXmlRpcEnabled(mixed $enabled): bool
    {
        return $this->xmlRpcGuard->apply(
            (bool) $enabled,
            $this->settings->enabled(Settings::DISABLE_XMLRPC)
        );
    }

    public function filterGenerator(mixed $markup): string
    {
        if (!$this->settings->enabled(Settings::REMOVE_VERSION_LEAK)) {
            return is_string($markup) ? $markup : '';
        }

        return $this->removeVersionLeak->generator(is_string($markup) ? $markup : '');
    }

    public function filterAssetSrc(mixed $src, mixed $handle = ''): string
    {
        unset($handle);
        $src = is_string($src) ? $src : '';
        if (!$this->settings->enabled(Settings::REMOVE_VERSION_LEAK) || !function_exists('get_bloginfo')) {
            return $src;
        }

        return $this->removeVersionLeak->stripWpVer($src, (string) get_bloginfo('version'));
    }

    public function removeGeneratorAction(): void
    {
        if (!$this->settings->enabled(Settings::REMOVE_VERSION_LEAK) || !function_exists('remove_action')) {
            return;
        }
        remove_action('wp_head', 'wp_generator');
    }

    public function sendSecurityHeaders(): void
    {
        if ($this->headersEmitted || !$this->settings->enabled(Settings::SECURITY_HEADERS)) {
            return;
        }
        if (function_exists('headers_sent') && headers_sent()) {
            return;
        }
        $this->securityHeaders->send(static function (string $line): void {
            header($line);
        });
        $this->headersEmitted = true;
    }

    public function filterRestPreDispatch(mixed $result, mixed $server, mixed $request): mixed
    {
        unset($server);
        if (!$this->settings->enabled(Settings::BLOCK_REST_USERS)) {
            return $result;
        }
        $route = '';
        if (is_object($request) && method_exists($request, 'get_route')) {
            $route = (string) $request->get_route();
        }
        $loggedIn = function_exists('is_user_logged_in') && is_user_logged_in();
        if (!$this->restUserGuard->shouldBlock($route, $loggedIn)) {
            return $result;
        }
        $denial = $this->restUserGuard->denial();
        if (class_exists('\WP_Error')) {
            return new \WP_Error(
                $denial['code'],
                'Enumeración de usuarios REST denegada.',
                ['status' => $denial['status']]
            );
        }

        return $result;
    }

    public function filterAppPasswordsAvailable(mixed $available): bool
    {
        $available = (bool) $available;
        if (!$this->settings->enabled(Settings::DISABLE_APP_PASSWORDS)) {
            return $available;
        }
        $allow = function_exists('apply_filters')
            ? (bool) apply_filters('or_hardening_allow_application_passwords', false)
            : false;

        return $this->appPasswordGuard->available($available, $allow);
    }

    /**
     * @param list<string> $caps
     * @return list<string>
     */
    public function filterMapMetaCap(mixed $caps, mixed $cap, mixed $userId = 0, mixed $args = []): array
    {
        unset($userId, $args);
        $caps = is_array($caps) ? array_values($caps) : [];
        $cap = is_string($cap) ? $cap : '';

        return $this->fileEditorGuard->filterMapMetaCap(
            $caps,
            $cap,
            $this->settings->enabled(Settings::DISABLE_FILE_EDITOR)
        );
    }

    public function hideEditorMenus(): void
    {
        if (!$this->settings->enabled(Settings::DISABLE_FILE_EDITOR) || !function_exists('remove_submenu_page')) {
            return;
        }
        remove_submenu_page('themes.php', 'theme-editor.php');
        remove_submenu_page('plugins.php', 'plugin-editor.php');
    }

    public function registerSettingsPage(): void
    {
        if (!function_exists('add_options_page')) {
            return;
        }
        add_options_page(
            'OR Hardening',
            'OR Hardening',
            'manage_options',
            'or-hardening',
            [$this, 'renderSettingsPage']
        );
    }

    public function renderSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('No tiene permiso para ver esta página.', 403);
        }
        $updated = isset($_GET['or-hardening-updated']);
        echo '<div class="wrap">';
        echo '<h1>OR Hardening</h1>';
        if ($updated) {
            echo '<div class="notice notice-success is-dismissible"><p>Ajustes guardados.</p></div>';
        }
        echo '<p>Controles de endurecimiento. Por defecto todos están activos. Desactive solo lo que rompa un flujo real del sitio.</p>';
        echo '<form method="post" action="">';
        wp_nonce_field('or_hardening_settings_save');
        echo '<input type="hidden" name="or_hardening_save" value="1" />';
        echo '<table class="form-table" role="presentation">';
        foreach (Settings::schema() as $key => $meta) {
            $checked = $this->settings->enabled($key) ? ' checked="checked"' : '';
            echo '<tr>';
            echo '<th scope="row">' . esc_html($meta['label']) . '</th>';
            echo '<td>';
            echo '<label><input type="checkbox" name="or_hardening[' . esc_attr($key) . ']" value="1"' . $checked . ' /> Activado</label>';
            echo '<p class="description">' . esc_html($meta['help']) . '</p>';
            if ($key === Settings::DISABLE_FILE_EDITOR) {
                echo '<p class="description">Añada en <code>wp-config.php</code> (antes de <code>That\'s all, stop editing!</code>): <code>' . esc_html($this->fileEditorGuard->wpConfigSnippet()) . '</code></p>';
            }
            echo '</td></tr>';
        }
        echo '</table>';
        submit_button('Guardar cambios');
        echo '</form></div>';
    }

    public function handleSettingsSave(): void
    {
        if (!isset($_POST['or_hardening_save'])) {
            return;
        }
        if (!current_user_can('manage_options')) {
            wp_die('No tiene permiso para guardar estos ajustes.', 403);
        }
        check_admin_referer('or_hardening_settings_save');
        $posted = isset($_POST['or_hardening']) && is_array($_POST['or_hardening']) ? $_POST['or_hardening'] : [];
        $clean = Settings::fromPost($posted);
        update_option(Settings::OPTION_KEY, $clean, false);
        $this->settings = new Settings($clean);
        wp_safe_redirect(
            add_query_arg(
                'or-hardening-updated',
                '1',
                admin_url('options-general.php?page=or-hardening')
            )
        );
        exit;
    }

    public function fileEditorConstantNotice(): void
    {
        if (!$this->settings->enabled(Settings::DISABLE_FILE_EDITOR)) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }
        if ($this->fileEditorGuard->isConstantDefined()) {
            return;
        }
        echo '<div class="notice notice-warning"><p>';
        echo 'OR Hardening: el editor de archivos está restringido por capacidades, pero <code>DISALLOW_FILE_EDIT</code> no está definido. Añada <code>';
        echo esc_html($this->fileEditorGuard->wpConfigSnippet());
        echo '</code> a <code>wp-config.php</code>.';
        echo '</p></div>';
    }

    public function filterAuthenticate(mixed $user, mixed $username, mixed $password): mixed
    {
        unset($password);
        if (!$this->settings->enabled(Settings::LOGIN_THROTTLE)) {
            return $user;
        }
        if (!is_string($username) || $username === '') {
            return $user;
        }
        if ($this->throttle()->isLocked($this->clientIp(), $username)) {
            if (class_exists('\WP_Error')) {
                return new \WP_Error(
                    'or_hardening_login_locked',
                    'Demasiados intentos fallidos. Espere 15 minutos e inténtelo de nuevo.'
                );
            }
        }

        return $user;
    }

    public function onLoginFailed(mixed $username): void
    {
        if (!$this->settings->enabled(Settings::LOGIN_THROTTLE) || !is_string($username) || $username === '') {
            return;
        }
        $this->throttle()->recordFailure($this->clientIp(), $username);
    }

    public function onLoginSuccess(mixed $userLogin): void
    {
        if (!$this->settings->enabled(Settings::LOGIN_THROTTLE) || !is_string($userLogin) || $userLogin === '') {
            return;
        }
        $this->throttle()->clear($this->clientIp(), $userLogin);
    }

    private function throttle(): LoginThrottle
    {
        if ($this->loginThrottle === null) {
            $store = $this->failureStore ?? new InMemoryFailureStore($this->clock);
            $this->loginThrottle = new LoginThrottle($store, $this->clock);
        }

        return $this->loginThrottle;
    }

    private function clientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        return is_string($ip) ? $ip : '0.0.0.0';
    }
}
