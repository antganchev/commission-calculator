<?php

namespace App\Tests\Service\Commission;

use App\DTO\Transaction;
use App\Enum\OperationType;
use App\Enum\UserType;
use App\Service\Commission\PrivateWithdrawCalculator;
use App\Service\Exchange\ExchangeRateServiceInterface;
use App\Service\History\WithdrawHistoryService;
use PHPUnit\Framework\TestCase;

class PrivateWithdrawCalculatorTest extends TestCase
{
    private PrivateWithdrawCalculator $calculator;
    private $mockHistoryService;
    private $mockExchangeRateService;

    protected function setUp(): void
    {
        $this->mockHistoryService = $this->createMock(WithdrawHistoryService::class);
        $this->mockExchangeRateService = $this->createMock(ExchangeRateServiceInterface::class);

        $this->calculator = new PrivateWithdrawCalculator(
            $this->mockHistoryService,
            $this->mockExchangeRateService
        );
    }

    public function testSupportsReturnsTrueForPrivateWithdraw(): void
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

        $this->assertTrue($this->calculator->supports($transaction));
    }

    public function testSupportsReturnsFalseForNonPrivateWithdraw(): void
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

        $this->assertFalse($this->calculator->supports($transaction));
    }

    public function testCalculateNoCommissionWithinFreeLimit(): void
    {
        $transactionDate = new \DateTimeImmutable('2025-04-07');
        $monday = (clone $transactionDate)->modify('monday this week')->format('Y-m-d');

        $transaction = new Transaction(
            $transactionDate,
            1,
            UserType::Private,
            OperationType::Withdraw,
            500.00,
            'EUR',
            2
        );

        $this->mockHistoryService
            ->method('getUserWeekHistory')
            ->with($transaction->userId, $monday)
            ->willReturn(['count' => 1, 'total' => 400.00]);

        $this->mockHistoryService
            ->expects($this->once())
            ->method('addTransaction')
            ->with($transaction->userId, $monday, 500.00);

        $commission = $this->calculator->calculate($transaction);

        $this->assertSame(0.00, $commission);
    }

    public function testCalculatePartialCommissionExceedingFreeLimit(): void
    {
        $transactionDate = new \DateTimeImmutable('2025-04-07');
        $monday = (clone $transactionDate)->modify('monday this week')->format('Y-m-d');

        $transaction = new Transaction(
            $transactionDate,
            1,
            UserType::Private,
            OperationType::Withdraw,
            700.00,
            'EUR',
            2
        );

        $this->mockHistoryService
            ->method('getUserWeekHistory')
            ->with($transaction->userId, $monday)
            ->willReturn(['count' => 1, 'total' => 400.00]);

        $this->mockHistoryService
            ->expects($this->once())
            ->method('addTransaction')
            ->with($transaction->userId, $monday, 700.00);

        $commission = $this->calculator->calculate($transaction);

        // Free limit is 1000.00, so commission applies to 100.00 (700 + 400 - 1000)
        $expectedCommission = 100.00 * 0.003;
        $this->assertSame(round($expectedCommission, 2), $commission);
    }

    public function testCalculateFullCommissionExceedingFreeCount(): void
    {
        $transactionDate = new \DateTimeImmutable('2025-04-07');
        $monday = (clone $transactionDate)->modify('monday this week')->format('Y-m-d');

        $transaction = new Transaction(
            $transactionDate,
            1,
            UserType::Private,
            OperationType::Withdraw,
            500.00,
            'EUR',
            2
        );

        $this->mockHistoryService
            ->method('getUserWeekHistory')
            ->with($transaction->userId, $monday)
            ->willReturn(['count' => 3, 'total' => 1000.00]);

        $this->mockHistoryService
            ->expects($this->once())
            ->method('addTransaction')
            ->with($transaction->userId, $monday, 500.00);

        $commission = $this->calculator->calculate($transaction);

        // Full commission applies to the entire amount
        $expectedCommission = 500.00 * 0.003;
        $this->assertSame(round($expectedCommission, 2), $commission);
    }

    public function testCalculateConvertsCurrency(): void
    {
        $transactionDate = new \DateTimeImmutable('2025-04-07');
        $monday = (clone $transactionDate)->modify('monday this week')->format('Y-m-d');

        $transaction = new Transaction(
            $transactionDate,
            1,
            UserType::Private,
            OperationType::Withdraw,
            500.00,
            'USD',
            2
        );

        $this->mockExchangeRateService
            ->method('getRate')
            ->with('USD')
            ->willReturn(1.2); // Example exchange rate: 1 EUR = 1.2 USD

        $this->mockHistoryService
            ->method('getUserWeekHistory')
            ->with($transaction->userId, $monday)
            ->willReturn(['count' => 3, 'total' => 1000.00]);

        $this->mockHistoryService
            ->expects($this->once())
            ->method('addTransaction')
            ->with($transaction->userId, $monday, $this->anything());

        $commission = $this->calculator->calculate($transaction);

        // Full commission applies to the entire amount in EUR
        $amountEur = 500.00 / 1.2; // Convert to EUR
        $expectedCommission = $amountEur * 0.003; // Apply commission
        $expectedCommissionInUsd = $expectedCommission * 1.2; // Convert back to USD

        $this->assertSame(round($expectedCommissionInUsd, 2), $commission);
    }
}
