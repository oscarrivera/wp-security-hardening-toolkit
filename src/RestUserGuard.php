<?php

declare(strict_types=1);

namespace OrHardening;

final class RestUserGuard
{
    public const STATUS = 401;
    public const CODE = 'or_hardening_rest_users';

    public function shouldBlock(string $route, bool $isLoggedIn): bool
    {
        if ($isLoggedIn) {
            return false;
        }

        $normalized = '/' . trim($route, '/');

        return (bool) preg_match('#^/wp/v2/users(?:/.*)?$#', $normalized);
    }

    /**
     * @return array{status: int, code: string}
     */
    public function denial(): array
    {
        return [
            'status' => self::STATUS,
            'code' => self::CODE,
        ];
    }
}
