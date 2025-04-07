<?php

namespace App\Tests\Validator\Constraints;

use App\Validator\Constraints\TransactionCsvRow;
use App\Validator\Constraints\TransactionCsvRowValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;


class TransactionCsvRowValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ConstraintValidatorInterface
    {
        return new TransactionCsvRowValidator();
    }

    public function testValidRow(): void
    {
        $row = ['2025-04-07', '1', 'private', 'withdraw', '100.00', 'EUR'];
        $this->validator->validate($row, new TransactionCsvRow());
        $this->assertNoViolation();
    }

    public function testInvalidNumberOfElements(): void
    {
        $row = ['2025-04-07', '1', 'private', 'withdraw', '100.00']; // Missing currency
        $this->validator->validate($row, new TransactionCsvRow());
        $this->buildViolation('Invalid number of elements in the row.')
            ->assertRaised();
    }

    public function testInvalidDateFormat(): void
    {
        $row = ['07-04-2025', '1', 'private', 'withdraw', '100.00', 'EUR'];
        $this->validator->validate($row, new TransactionCsvRow());
        $this->buildViolation('Invalid date format.')
            ->assertRaised();
    }

    public function testInvalidUserType(): void
    {
        $row = ['2025-04-07', '1', 'invalid_user_type', 'withdraw', '100.00', 'EUR'];
        $this->validator->validate($row, new TransactionCsvRow());
        $this->buildViolation('Invalid user type.')
            ->assertRaised();
    }

    public function testInvalidOperationType(): void
    {
        $row = ['2025-04-07', '1', 'private', 'invalid_operation', '100.00', 'EUR'];
        $this->validator->validate($row, new TransactionCsvRow());
        $this->buildViolation('Invalid operation type.')
            ->assertRaised();
    }

    public function testInvalidAmount(): void
    {
        $row = ['2025-04-07', '1', 'private', 'withdraw', '-100.00', 'EUR'];
        $this->validator->validate($row, new TransactionCsvRow());
        $this->buildViolation('Amount must be a valid positive number.')
            ->assertRaised();
    }

    public function testInvalidCurrency(): void
    {
        $row = ['2025-04-07', '1', 'private', 'withdraw', '100.00', 'INVALID'];
        $this->validator->validate($row, new TransactionCsvRow());
        $this->buildViolation('Invalid currency.')
            ->assertRaised();
    }
}
