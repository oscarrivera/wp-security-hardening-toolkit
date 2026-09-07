<?php

declare(strict_types=1);

use OrHardening\RestUserGuard;
use PHPUnit\Framework\TestCase;

final class RestUserGuardTest extends TestCase
{
    private RestUserGuard $guard;

    protected function setUp(): void
    {
        $this->guard = new RestUserGuard();
    }

    public function testBlocksUsersCollectionWhenAnonymous(): void
    {
        $this->assertTrue($this->guard->shouldBlock('/wp/v2/users', false));
        $this->assertTrue($this->guard->shouldBlock('wp/v2/users', false));
    }

    public function testBlocksUserResourceWhenAnonymous(): void
    {
        $this->assertTrue($this->guard->shouldBlock('/wp/v2/users/1', false));
        $this->assertTrue($this->guard->shouldBlock('/wp/v2/users/me', false));
    }

    public function testAllowsWhenLoggedIn(): void
    {
        $this->assertFalse($this->guard->shouldBlock('/wp/v2/users', true));
        $this->assertFalse($this->guard->shouldBlock('/wp/v2/users/1', true));
    }

    public function testDoesNotBlockOtherRoutes(): void
    {
        $this->assertFalse($this->guard->shouldBlock('/wp/v2/posts', false));
        $this->assertFalse($this->guard->shouldBlock('/wp/v2/userspace', false));
        $this->assertFalse($this->guard->shouldBlock('/or/v1/users', false));
    }

    public function testDenialIsUnauthorized(): void
    {
        $denial = $this->guard->denial();
        $this->assertSame(401, $denial['status']);
        $this->assertSame(RestUserGuard::CODE, $denial['code']);
    }
}
