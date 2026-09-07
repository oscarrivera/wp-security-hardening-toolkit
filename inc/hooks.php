<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$or_hardening = or_hardening();

add_filter('xmlrpc_enabled', [$or_hardening, 'filterXmlRpcEnabled']);
add_filter('the_generator', [$or_hardening, 'filterGenerator']);
add_filter('style_loader_src', [$or_hardening, 'filterAssetSrc'], 10, 2);
add_filter('script_loader_src', [$or_hardening, 'filterAssetSrc'], 10, 2);
add_action('wp_head', [$or_hardening, 'removeGeneratorAction'], 1);
add_action('send_headers', [$or_hardening, 'sendSecurityHeaders']);
add_action('admin_init', [$or_hardening, 'sendSecurityHeaders']);
add_action('login_init', [$or_hardening, 'sendSecurityHeaders']);
add_filter('rest_pre_dispatch', [$or_hardening, 'filterRestPreDispatch'], 10, 3);
add_filter('wp_is_application_passwords_available', [$or_hardening, 'filterAppPasswordsAvailable']);
add_filter('map_meta_cap', [$or_hardening, 'filterMapMetaCap'], 10, 4);
add_action('admin_menu', [$or_hardening, 'hideEditorMenus'], 999);
add_action('admin_menu', [$or_hardening, 'registerSettingsPage']);
add_action('admin_init', [$or_hardening, 'handleSettingsSave'], 1);
add_action('admin_notices', [$or_hardening, 'fileEditorConstantNotice']);
add_filter('authenticate', [$or_hardening, 'filterAuthenticate'], 30, 3);
add_action('wp_login_failed', [$or_hardening, 'onLoginFailed']);
add_action('wp_login', [$or_hardening, 'onLoginSuccess']);
