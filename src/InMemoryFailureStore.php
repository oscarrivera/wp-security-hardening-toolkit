<?php

declare(strict_types=1);

namespace OrHardening;

final class InMemoryFailureStore implements FailureStore
{
    /**
     * @var array<string, array{value: mixed, expires_at: int}>
     */
    private array $items = [];

    public function __construct(private Clock $clock)
    {
    }

    public function get(string $key): mixed
    {
        if (!isset($this->items[$key])) {
            return null;
        }
        if ($this->items[$key]['expires_at'] <= $this->clock->now()) {
            unset($this->items[$key]);

            return null;
        }

        return $this->items[$key]['value'];
    }

    public function set(string $key, mixed $value, int $ttlSeconds): void
    {
        $this->items[$key] = [
            'value' => $value,
            'expires_at' => $this->clock->now() + max(1, $ttlSeconds),
        ];
    }

    public function delete(string $key): void
    {
        unset($this->items[$key]);
    }
}
