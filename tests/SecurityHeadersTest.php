<?php

declare(strict_types=1);

use OrHardening\SecurityHeaders;
use PHPUnit\Framework\TestCase;

final class SecurityHeadersTest extends TestCase
{
    public function testExpectedHeaderMap(): void
    {
        $headers = (new SecurityHeaders())->all();

        $this->assertSame('nosniff', $headers['X-Content-Type-Options']);
        $this->assertSame('DENY', $headers['X-Frame-Options']);
        $this->assertSame('strict-origin-when-cross-origin', $headers['Referrer-Policy']);
        $this->assertSame('geolocation=()', $headers['Permissions-Policy']);
        $this->assertCount(4, $headers);
    }

    public function testSendEmitsHttpLines(): void
    {
        $sent = [];
        (new SecurityHeaders())->send(static function (string $line) use (&$sent): void {
            $sent[] = $line;
        });

        $this->assertSame([
            'X-Content-Type-Options: nosniff',
            'X-Frame-Options: DENY',
            'Referrer-Policy: strict-origin-when-cross-origin',
            'Permissions-Policy: geolocation=()',
        ], $sent);
    }
}
