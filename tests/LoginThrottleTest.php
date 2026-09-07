<?php

declare(strict_types=1);

use OrHardening\Clock;
use OrHardening\InMemoryFailureStore;
use OrHardening\LoginThrottle;
use PHPUnit\Framework\TestCase;

final class LoginThrottleTest extends TestCase
{
    private Clock $clock;
    private LoginThrottle $throttle;

    protected function setUp(): void
    {
        $this->clock = new Clock(1_700_000_000);
        $this->throttle = new LoginThrottle(new InMemoryFailureStore($this->clock), $this->clock);
    }

    public function testNotLockedBeforeThreshold(): void
    {
        $ip = '203.0.113.10';
        $login = 'admin';
        for ($i = 0; $i < LoginThrottle::MAX_FAILURES - 1; $i++) {
            $this->throttle->recordFailure($ip, $login);
        }

        $this->assertFalse($this->throttle->isLocked($ip, $login));
    }

    public function testLocksAfterFiveFailures(): void
    {
        $ip = '203.0.113.10';
        $login = 'admin';
        for ($i = 0; $i < LoginThrottle::MAX_FAILURES; $i++) {
            $this->throttle->recordFailure($ip, $login);
        }

        $this->assertTrue($this->throttle->isLocked($ip, $login));
    }

    public function testIdentityIsScopedToIpAndLogin(): void
    {
        $ip = '203.0.113.10';
        $login = 'admin';
        for ($i = 0; $i < LoginThrottle::MAX_FAILURES; $i++) {
            $this->throttle->recordFailure($ip, $login);
        }

        $this->assertFalse($this->throttle->isLocked($ip, 'editor'));
        $this->assertFalse($this->throttle->isLocked('198.51.100.20', $login));
        $this->assertTrue($this->throttle->isLocked($ip, 'Admin'));
    }

    public function testLockExpiresAfterWindow(): void
    {
        $ip = '203.0.113.10';
        $login = 'admin';
        for ($i = 0; $i < LoginThrottle::MAX_FAILURES; $i++) {
            $this->throttle->recordFailure($ip, $login);
        }
        $this->assertTrue($this->throttle->isLocked($ip, $login));

        $this->clock->advance(LoginThrottle::WINDOW_SECONDS + 1);

        $this->assertFalse($this->throttle->isLocked($ip, $login));
        $this->assertSame(1, $this->throttle->recordFailure($ip, $login));
        $this->assertFalse($this->throttle->isLocked($ip, $login));
    }

    public function testClearRemovesLock(): void
    {
        $ip = '203.0.113.10';
        $login = 'admin';
        for ($i = 0; $i < LoginThrottle::MAX_FAILURES; $i++) {
            $this->throttle->recordFailure($ip, $login);
        }
        $this->throttle->clear($ip, $login);

        $this->assertFalse($this->throttle->isLocked($ip, $login));
    }

    public function testKeyIsStableAndPrefixed(): void
    {
        $a = $this->throttle->key('1.1.1.1', 'User');
        $b = $this->throttle->key('1.1.1.1', 'user');
        $this->assertSame($a, $b);
        $this->assertStringStartsWith(LoginThrottle::KEY_PREFIX, $a);
        $this->assertNotSame($a, $this->throttle->key('1.1.1.2', 'user'));
    }
}
