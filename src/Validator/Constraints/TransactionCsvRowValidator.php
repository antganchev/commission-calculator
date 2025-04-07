<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Enum\UserType;
use App\Enum\OperationType;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TransactionCsvRowValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TransactionCsvRow) {
            throw new UnexpectedTypeException($constraint, TransactionCsvRow::class);
        }

        // Validate transaction csv row data, expected data order:
        // date, userId, userType, operationType, amount, currency
        if (!is_array($value) || count($value) !== 6) {
            $this->context->buildViolation('Invalid number of elements in the row.')
                ->addViolation();
            return;
        }

        list($date, $userId, $userType, $operationType, $amount, $currency) = $value;

        // Validate date format
        $dateObj = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
            $this->context->buildViolation('Invalid date format.')
                ->addViolation();
        }

        // // Validate userType
        if (!in_array($userType, array_map(fn($case) => $case->value, UserType::cases()), true)) {
            $this->context->buildViolation('Invalid user type.')
                ->addViolation();
        }

        if (!in_array($operationType, array_map(fn($case) => $case->value, OperationType::cases()), true)) {
            $this->context->buildViolation('Invalid operation type.')
                ->addViolation();
        }

        // Validate amount is numeric
        if (!is_numeric($amount) || (float)$amount < 0) {
            $this->context->buildViolation('Amount must be a valid positive number.')
                ->addViolation();
        }

        // Validate currency
        $validCurrencies = ['EUR', 'USD', 'JPY'];
        if (!in_array($currency, $validCurrencies, true)) {
            $this->context->buildViolation('Invalid currency.')
                ->addViolation();
        }
    }
}
