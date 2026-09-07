<?php

declare(strict_types=1);

namespace OrHardening;

/**
 * Adaptador WordPress. No se usa en tests unitarios.
 */
final class WpTransientStore implements FailureStore
{
    public function get(string $key): mixed
    {
        $value = get_transient($key);

        return $value === false ? null : $value;
    }

    public function set(string $key, mixed $value, int $ttlSeconds): void
    {
        set_transient($key, $value, max(1, $ttlSeconds));
    }

    public function delete(string $key): void
    {
        delete_transient($key);
    }
}
