<?php

namespace App\Service\Commission;

use App\DTO\Transaction;
use App\Enum\OperationType;
use App\Enum\UserType;
use App\Service\Exchange\ExchangeRateServiceInterface;
use App\Service\History\WithdrawHistoryService;

class PrivateWithdrawCalculator implements CommissionCalculatorInterface
{
    private const MAX_FREE_WITHDRAWAL = 1000.0; // Maximum free withdrawal amount
    private const MAX_FREE_COUNT = 3; // Maximum free withdrawal count

    public function __construct(
        private WithdrawHistoryService $historyService,
        private ExchangeRateServiceInterface $exchangeRateService
    ) {
    }

    public function supports(Transaction $transaction): bool
    {
        return $transaction->userType === UserType::Private && $transaction->operationType === OperationType::Withdraw;
    }

    public function calculate(Transaction $transaction): float
    {
        // Convert the amount to EUR if necessary
        $amountEur = $this->convertToEur($transaction->amount, $transaction->currency);

        // Get current week's history
        $week = $this->getWeekKey($transaction->date);
        $userHistory = $this->historyService->getUserWeekHistory($transaction->userId, $week);

        $this->historyService->addTransaction($transaction->userId, $week, $amountEur);


        if (
            $userHistory['count'] < self::MAX_FREE_COUNT
            && $userHistory['total'] + $amountEur <= self::MAX_FREE_WITHDRAWAL
        ) {
            $commission = 0; // No commission for the first 3 withdrawals or within the free amount
        } elseif (
            $userHistory['count'] < self::MAX_FREE_COUNT
            && $userHistory['total'] <= self::MAX_FREE_WITHDRAWAL
            && $userHistory['total'] + $amountEur > self::MAX_FREE_WITHDRAWAL
        ) {
            // If the user has already exceeded the free withdrawal amount, apply commission on the entire amount
            $commission = ($userHistory['total'] + $amountEur - self::MAX_FREE_WITHDRAWAL)  * $this->getCommission();
        } else {
            // If the user has already exceeded the free withdrawal count, apply commission on the entire amount
            $commission = $amountEur * $this->getCommission();
        }

        if ($transaction->currency !== 'EUR') {
            // Convert the commission to the original currency
            $commission *= $this->exchangeRateService->getRate($transaction->currency);
        }

        return round($commission, $transaction->decimalPlaces);
    }

    private function convertToEur(float $amount, string $currency): float
    {
        // If currency is already EUR, no conversion is needed
        if ($currency === 'EUR') {
            return $amount;
        }

        // Convert using the exchange rates from an external service
        $rate = $this->exchangeRateService->getRate($currency);
        return round($amount / $rate);
    }

    public function getCommission(): float
    {
        return 0.003; // 0.3% commission rate
    }

    private function getWeekKey(\DateTimeImmutable $date): string
    {
        $monday = (clone $date)->modify('monday this week');
        return $monday->format('Y-m-d');
    }
}
