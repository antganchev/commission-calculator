<?php

namespace App\Service\Commission;

use App\DTO\Transaction;

interface CommissionCalculatorInterface
{
    public function supports(Transaction $transaction): bool;
    public function calculate(Transaction $transaction): float;
    public function getCommission(): float;
}
