<?php

declare(strict_types=1);

namespace OrHardening;

final class Clock
{
    public function __construct(private ?int $frozenAt = null)
    {
    }

    public function now(): int
    {
        return $this->frozenAt ?? time();
    }

    public function advance(int $seconds): void
    {
        $this->frozenAt = $this->now() + $seconds;
    }
}
