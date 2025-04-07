<?php

namespace App\Service\Commission;

use App\DTO\Transaction;
use App\Enum\OperationType;

class DepositCalculator implements CommissionCalculatorInterface
{
    public function supports(Transaction $transaction): bool
    {
        return $transaction->operationType === OperationType::Deposit;
    }

    public function calculate(Transaction $transaction): float
    {
        return round($transaction->amount * $this->getCommission(), 2); // 0.03% commission
    }

    public function getCommission(): float
    {
        return 0.0003; // 0.03% commission rate
    }
}
