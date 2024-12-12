<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testGetUserIdentifier(): void
    {
        $data = "some_username";

        $this->assertInstanceOf(User::class, $this->user->setUsername($data));
        $this->assertEquals($data, $this->user->getUserIdentifier());
    }

    public function testGetRoles(): void
    {
        $data = "some_role";

        $this->assertInstanceOf(User::class, $this->user->setRoles([$data]));
        $this->assertEquals([$data, "ROLE_USER"], $this->user->getRoles());
    }

    public function testGetHashedToken(): void
    {
        $data = "some_token";

        $this->assertInstanceOf(User::class, $this->user->setHashedToken($data));
        $this->assertEquals($data, $this->user->getHashedToken());
    }

    public function testGetIsDisabled(): void
    {
        $this->assertInstanceOf(User::class, $this->user->setIsDisabled(true));
        $this->assertTrue($this->user->getIsDisabled());

        $this->assertInstanceOf(User::class, $this->user->setIsDisabled(false));
        $this->assertFalse($this->user->getIsDisabled());
    }

    public function testGetCreatedAt(): void
    {
        $data = new \DateTimeImmutable();

        $this->assertInstanceOf(User::class, $this->user->setCreatedAt($data));
        $this->assertEquals($data, $this->user->getCreatedAt());
    }
}
