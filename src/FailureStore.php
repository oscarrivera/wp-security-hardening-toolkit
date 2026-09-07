<?php

declare(strict_types=1);

namespace OrHardening;

interface FailureStore
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value, int $ttlSeconds): void;

    public function delete(string $key): void;
}
