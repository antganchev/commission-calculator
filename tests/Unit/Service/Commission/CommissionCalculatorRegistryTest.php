<?php

namespace App\Tests\Service\Commission;

use App\DTO\Transaction;
use App\Enum\OperationType;
use App\Enum\UserType;
use App\Service\Commission\CommissionCalculatorInterface;
use App\Service\Commission\CommissionCalculatorRegistry;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorRegistryTest extends TestCase
{
    private CommissionCalculatorRegistry $registry;
    private $mockCalculator1;
    private $mockCalculator2;

    protected function setUp(): void
    {
        // Create mock calculators
        $this->mockCalculator1 = $this->createMock(CommissionCalculatorInterface::class);
        $this->mockCalculator2 = $this->createMock(CommissionCalculatorInterface::class);

        // Pass the mock calculators to the registry
        $this->registry = new CommissionCalculatorRegistry([$this->mockCalculator1, $this->mockCalculator2]);
    }

    public function testGetCalculatorReturnsCorrectCalculator(): void
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

        // Configure the first mock to not support the transaction
        $this->mockCalculator1
            ->expects($this->once())
            ->method('supports')
            ->with($transaction)
            ->willReturn(false);

        // Configure the second mock to support the transaction
        $this->mockCalculator2
            ->expects($this->once())
            ->method('supports')
            ->with($transaction)
            ->willReturn(true);

        // Assert that the second calculator is returned
        $calculator = $this->registry->getCalculator($transaction);
        $this->assertSame($this->mockCalculator2, $calculator);
    }

    public function testGetCalculatorThrowsExceptionWhenNoCalculatorSupportsTransaction(): void
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

        // Configure both mocks to not support the transaction
        $this->mockCalculator1
            ->expects($this->once())
            ->method('supports')
            ->with($transaction)
            ->willReturn(false);

        $this->mockCalculator2
            ->expects($this->once())
            ->method('supports')
            ->with($transaction)
            ->willReturn(false);

        // Assert that an exception is thrown
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No calculator found for transaction');

        $this->registry->getCalculator($transaction);
    }
}
