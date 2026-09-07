<?php

declare(strict_types=1);

namespace OrHardening;

final class LoginThrottle
{
    public const MAX_FAILURES = 5;
    public const WINDOW_SECONDS = 900;
    public const KEY_PREFIX = 'orh_login_';

    public function __construct(
        private FailureStore $store,
        private Clock $clock,
    ) {
    }

    public function key(string $ip, string $login): string
    {
        $identity = strtolower(trim($login)) . '|' . trim($ip);

        return self::KEY_PREFIX . hash('sha256', $identity);
    }

    public function isLocked(string $ip, string $login): bool
    {
        $record = $this->read($ip, $login);

        return $record !== null && $record['count'] >= self::MAX_FAILURES;
    }

    public function recordFailure(string $ip, string $login): int
    {
        $now = $this->clock->now();
        $record = $this->read($ip, $login);
        if ($record === null) {
            $record = [
                'count' => 0,
                'expires' => $now + self::WINDOW_SECONDS,
            ];
        }
        $record['count']++;
        $ttl = max(1, $record['expires'] - $now);
        $this->store->set($this->key($ip, $login), $record, $ttl);

        return $record['count'];
    }

    public function clear(string $ip, string $login): void
    {
        $this->store->delete($this->key($ip, $login));
    }

    /**
     * @return array{count: int, expires: int}|null
     */
    private function read(string $ip, string $login): ?array
    {
        $raw = $this->store->get($this->key($ip, $login));
        if (!is_array($raw)) {
            return null;
        }
        $count = (int) ($raw['count'] ?? 0);
        $expires = (int) ($raw['expires'] ?? 0);
        if ($expires <= $this->clock->now() || $count < 1) {
            return null;
        }

        return ['count' => $count, 'expires' => $expires];
    }
}
