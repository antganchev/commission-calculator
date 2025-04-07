<?php

namespace App\DTO;

use App\Enum\OperationType;
use App\Enum\UserType;

class Transaction
{
    public function __construct(
        public readonly \DateTimeImmutable $date,
        public readonly int $userId,
        public readonly UserType $userType,
        public readonly OperationType $operationType,
        public readonly float $amount,
        public readonly string $currency,
        public readonly int $decimalPlaces
    ) {
    }
}
