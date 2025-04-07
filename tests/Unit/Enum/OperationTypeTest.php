<?php

namespace App\Tests\Enum;

use App\Enum\OperationType;
use PHPUnit\Framework\TestCase;

class OperationTypeTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertSame('deposit', OperationType::Deposit->value);
        $this->assertSame('withdraw', OperationType::Withdraw->value);
    }

    public function testEnumCases(): void
    {
        $cases = OperationType::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(OperationType::Deposit, $cases);
        $this->assertContains(OperationType::Withdraw, $cases);
    }

    public function testEnumFromValue(): void
    {
        $this->assertSame(OperationType::Deposit, OperationType::from('deposit'));
        $this->assertSame(OperationType::Withdraw, OperationType::from('withdraw'));
    }

    public function testEnumTryFromValue(): void
    {
        $this->assertSame(OperationType::Deposit, OperationType::tryFrom('deposit'));
        $this->assertSame(OperationType::Withdraw, OperationType::tryFrom('withdraw'));
        $this->assertNull(OperationType::tryFrom('invalid'));
    }
}
