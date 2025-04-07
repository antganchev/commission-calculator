<?php

namespace App\Command;

use App\DTO\Transaction;
use App\Enum\OperationType;
use App\Enum\UserType;
use App\Service\Commission\CommissionCalculatorRegistry;
use App\Validator\Constraints\TransactionCsvRow;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CalculateCommissionCommand extends Command
{
    public function __construct(
        private CommissionCalculatorRegistry $calculatorRegistry,
        private ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:calculate-commissions')
            ->setDescription('Calculate commission fees from a CSV file.')
            ->addArgument('file', null, 'Path to the CSV file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');

        if (!file_exists($file)) {
            $output->writeln('File not found');
            return Command::FAILURE;
        }

        $handle = fopen($file, 'r');
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $violations = $this->validator->validate($data, new TransactionCsvRow());

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $output->writeln($violation->getMessage());
                }
                continue; // Skip invalid rows
            }

            list($date, $userId, $userType, $operationType, $amount, $currency) = $data;

            $transaction = new Transaction(
                new \DateTimeImmutable($date),
                (int)$userId,
                UserType::from($userType),
                OperationType::from($operationType),
                (float)$amount,
                $currency,
                max(strlen(strrchr($amount, '.')) - 1, 0) // Get the number of decimal places
            );

            $calculator = $this->calculatorRegistry->getCalculator($transaction);
            $commission = number_format($calculator->calculate($transaction), $transaction->decimalPlaces, '.', '');
            $output->writeln("Transaction: {$transaction->amount} | Commission: {$commission}");
        }
        fclose($handle);

        return Command::SUCCESS;
    }
}
