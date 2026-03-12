<?php

namespace App\Tests;

use App\Entity\User;
use App\Service\UserManager;
use PHPUnit\Framework\TestCase;

class UserManagerTest extends TestCase
{
    private UserManager $manager;

    protected function setUp(): void
    {
        $this->manager = new UserManager();
    }

    public function testValidUser(): void
    {
        $user = new User();
        $user->setEmail("test@edulink.com");
        $user->setPassword("password123");

        $this->assertTrue($this->manager->validate($user));
    }

    public function testInvalidEmailThrowsException(): void
    {
        $user = new User();
        $user->setEmail("invalid-email");
        $user->setPassword("password123");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid email format.");

        $this->manager->validate($user);
    }

    public function testShortPasswordThrowsException(): void
    {
        $user = new User();
        $user->setEmail("test@edulink.com");
        $user->setPassword("1234567");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Password must be at least 8 characters long.");

        $this->manager->validate($user);
    }
}
