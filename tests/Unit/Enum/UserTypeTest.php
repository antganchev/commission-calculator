<?php

namespace App\Tests\Enum;

use App\Enum\UserType;
use PHPUnit\Framework\TestCase;

class UserTypeTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertSame('private', UserType::Private->value);
        $this->assertSame('business', UserType::Business->value);
    }

    public function testEnumCases(): void
    {
        $cases = UserType::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(UserType::Private, $cases);
        $this->assertContains(UserType::Business, $cases);
    }

    public function testEnumFromValue(): void
    {
        $this->assertSame(UserType::Private, UserType::from('private'));
        $this->assertSame(UserType::Business, UserType::from('business'));
    }

    public function testEnumTryFromValue(): void
    {
        $this->assertSame(UserType::Private, UserType::tryFrom('private'));
        $this->assertSame(UserType::Business, UserType::tryFrom('business'));
        $this->assertNull(UserType::tryFrom('invalid'));
    }
}
