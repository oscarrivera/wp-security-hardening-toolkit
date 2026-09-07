<?php

declare(strict_types=1);

namespace OrHardening;

final class SecurityHeaders
{
    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=()',
        ];
    }

    /**
     * @param callable(string): void $sendHeader Recibe una línea "Name: value".
     */
    public function send(callable $sendHeader): void
    {
        foreach ($this->all() as $name => $value) {
            $sendHeader($name . ': ' . $value);
        }
    }
}
