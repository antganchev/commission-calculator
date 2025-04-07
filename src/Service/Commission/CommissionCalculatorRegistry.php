<?php

namespace App\Service\Commission;

use App\DTO\Transaction;

class CommissionCalculatorRegistry
{
    public function __construct(
        private iterable $calculators // For autowiring all calculators
    ) {
    }

    public function getCalculator(Transaction $transaction): CommissionCalculatorInterface
    {
        foreach ($this->calculators as $calculator) {
            if ($calculator->supports($transaction)) {
                return $calculator;
            }
        }

        throw new \RuntimeException("No calculator found for transaction");
    }
}
