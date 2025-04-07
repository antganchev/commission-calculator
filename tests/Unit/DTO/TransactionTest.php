<?php

namespace App\Tests\DTO;

use App\DTO\Transaction;
use App\Enum\OperationType;
use App\Enum\UserType;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function testTransactionInitialization(): void
    {
        $date = new \DateTimeImmutable('2025-04-07');
        $userId = 1;
        $userType = UserType::Private;
        $operationType = OperationType::Withdraw;
        $amount = 100.50;
        $currency = 'EUR';
        $decimalPlaces = 2;

        $transaction = new Transaction(
            $date,
            $userId,
            $userType,
            $operationType,
            $amount,
            $currency,
            $decimalPlaces
        );

        $this->assertSame($date, $transaction->date);
        $this->assertSame($userId, $transaction->userId);
        $this->assertSame($userType, $transaction->userType);
        $this->assertSame($operationType, $transaction->operationType);
        $this->assertSame($amount, $transaction->amount);
        $this->assertSame($currency, $transaction->currency);
        $this->assertSame($decimalPlaces, $transaction->decimalPlaces);
    }
}
