<?php

declare(strict_types=1);

namespace OrHardening;

/**
 * DISALLOW_FILE_EDIT solo es eficaz si está en wp-config.php antes de cargar WordPress.
 * Esta clase no define la constante: documenta el requisito y, con WP presente,
 * deniega capacidades del editor de temas/plugins.
 */
final class FileEditorGuard
{
    public const WP_CONFIG_SNIPPET = "define('DISALLOW_FILE_EDIT', true);";

    /**
     * @var list<string>
     */
    public const DENIED_CAPS = ['edit_themes', 'edit_plugins', 'edit_files'];

    public function wpConfigSnippet(): string
    {
        return self::WP_CONFIG_SNIPPET;
    }

    public function isConstantDefined(): bool
    {
        return defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT;
    }

    /**
     * @param list<string> $caps
     * @return list<string>
     */
    public function filterMapMetaCap(array $caps, string $cap, bool $active): array
    {
        if (!$active || !in_array($cap, self::DENIED_CAPS, true)) {
            return $caps;
        }
        $caps[] = 'do_not_allow';

        return $caps;
    }
}
