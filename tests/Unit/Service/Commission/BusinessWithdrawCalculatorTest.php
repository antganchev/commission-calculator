<?php

namespace App\Tests\Service\Commission;

use App\DTO\Transaction;
use App\Enum\OperationType;
use App\Enum\UserType;
use App\Service\Commission\BusinessWithdrawCalculator;
use PHPUnit\Framework\TestCase;

class BusinessWithdrawCalculatorTest extends TestCase
{
    private BusinessWithdrawCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new BusinessWithdrawCalculator();
    }

    public function testSupportsReturnsTrueForBusinessWithdraw(): void
    {
        $transaction = new Transaction(
            new \DateTimeImmutable('2025-04-07'),
            1,
            UserType::Business,
            OperationType::Withdraw,
            100.00,
            'EUR',
            2
        );

        $this->assertTrue($this->calculator->supports($transaction));
    }

    public function testSupportsReturnsFalseForNonBusinessWithdraw(): void
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

    public function testSupportsReturnsFalseForBusinessDeposit(): void
    {
        $transaction = new Transaction(
            new \DateTimeImmutable('2025-04-07'),
            1,
            UserType::Business,
            OperationType::Deposit,
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
            UserType::Business,
            OperationType::Withdraw,
            200.00,
            'EUR',
            2
        );

        $expectedCommission = round(200.00 * 0.005, 2); // 0.5% of 200.00
        $this->assertSame($expectedCommission, $this->calculator->calculate($transaction));
    }

    public function testGetCommissionReturnsCorrectRate(): void
    {
        $this->assertSame(0.005, $this->calculator->getCommission());
        $this->assertIsFloat($this->calculator->getCommission());
    }
}
