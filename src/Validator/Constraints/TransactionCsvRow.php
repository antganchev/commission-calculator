<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TransactionCsvRow extends Constraint
{
    public string $message = 'The transaction data is invalid: {{ error }}';

    public function validatedBy(): string
    {
        return TransactionCsvRowValidator::class;
    }
}
