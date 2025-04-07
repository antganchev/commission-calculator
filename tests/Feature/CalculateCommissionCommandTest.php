<?php

namespace App\Tests\Feature\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateCommissionCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:calculate-commissions');
        $this->commandTester = new CommandTester($command);
    }

    public function testValidCsvFile(): void
    {
        $csvFile = __DIR__ . '/valid-transactions.csv';
        file_put_contents($csvFile, <<<CSV
2025-04-05,1,private,withdraw,100.00,EUR
2025-04-06,1,private,withdraw,1000.00,EUR
2025-04-08,2,business,deposit,200.00,USD
CSV);

        $this->commandTester->execute(['file' => $csvFile]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Transaction: 100 | Commission:', $output);
        $this->assertStringContainsString('Transaction: 1000 | Commission:', $output);
        $this->assertStringContainsString('Transaction: 200 | Commission:', $output);

        unlink($csvFile); // Clean up
    }

    public function testInvalidCsvFile(): void
    {
        $csvFile = __DIR__ . '/invalid-transactions.csv';
        file_put_contents($csvFile, <<<CSV
invalid-date,1,private,withdraw,100.00,EUR
2025-04-08,2,invalid-user-type,deposit,200.00,USD
CSV);

        $this->commandTester->execute(['file' => $csvFile]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Invalid date format.', $output);
        $this->assertStringContainsString('Invalid user type.', $output);

        unlink($csvFile); // Clean up
    }

    public function testFileNotFound(): void
    {
        $this->commandTester->execute(['file' => 'non-existent-file.csv']);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('File not found', $output);
    }

    public function testEmptyCsvFile(): void
    {
        $csvFile = __DIR__ . '/empty.csv';
        file_put_contents($csvFile, '');

        $this->commandTester->execute(['file' => $csvFile]);

        $output = $this->commandTester->getDisplay();

        $this->assertEmpty($output);

        unlink($csvFile); // Clean up
    }
}
