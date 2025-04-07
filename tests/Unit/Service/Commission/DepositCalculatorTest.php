<?php

namespace App\Tests\Service\Commission;

use App\DTO\Transaction;
use App\Enum\OperationType;
use App\Enum\UserType;
use App\Service\Commission\DepositCalculator;
use PHPUnit\Framework\TestCase;

class DepositCalculatorTest extends TestCase
{
    private DepositCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new DepositCalculator();
    }

    public function testSupportsReturnsTrueForDeposit(): void
    {
        $transaction = new Transaction(
            new \DateTimeImmutable('2025-04-07'),
            1,
            UserType::Private,
            OperationType::Deposit,
            100.00,
            'EUR',
            2
        );

        $this->assertTrue($this->calculator->supports($transaction));
    }

    public function testSupportsReturnsFalseForNonDeposit(): void
    {
        $transaction = new Transaction(
            new \DateTimeImmutable('2025-04-07'),
            1,
            UserType::Private,
            OperationType::Withdraw,
            100.00,
            'EUR',
            2
        );

        $this->assertFalse($this->calculator->supports($transaction));
    }

    public function testCalculateReturnsCorrectCommission(): void
    {
        $transaction = new Transaction(
            new \DateTimeImmutable('2025-04-07'),
            1,
            UserType::Private,
            OperationType::Deposit,
            1000.00,
            'EUR',
            2
        );

        $expectedCommission = round(1000.00 * 0.0003, 2); // 0.03% of 1000.00
        $this->assertSame($expectedCommission, $this->calculator->calculate($transaction));
    }

    public function testGetCommissionReturnsCorrectRate(): void
    {
        $this->assertSame(0.0003, $this->calculator->getCommission());
    }
}
