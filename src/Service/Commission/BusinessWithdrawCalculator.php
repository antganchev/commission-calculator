<?php

namespace App\Service\Commission;

use App\DTO\Transaction;
use App\Enum\OperationType;
use App\Enum\UserType;

class BusinessWithdrawCalculator implements CommissionCalculatorInterface
{
    private const COMMISSION_RATE = 0.005; // 0.5% commission rate

    public function supports(Transaction $transaction): bool
    {
        return $transaction->userType === UserType::Business && $transaction->operationType === OperationType::Withdraw;
    }

    public function calculate(Transaction $transaction): float
    {
        return round($transaction->amount * $this->getCommission(), 2);
    }

    public function getCommission(): float
    {
        return self::COMMISSION_RATE;
    }
}
