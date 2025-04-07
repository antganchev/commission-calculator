<?php

namespace App\Service\History;

class WithdrawHistoryService
{
    private array $history = [];

    public function getUserWeekHistory(int $userId, string $week): array
    {
        return $this->history[$userId][$week] ?? ['count' => 0, 'total' => 0];
    }

    public function addTransaction(int $userId, string $week, float $amountEur): void
    {
        if (!isset($this->history[$userId][$week])) {
            $this->history[$userId][$week] = ['count' => 0, 'total' => 0];
        }

        $this->history[$userId][$week]['count']++;
        $this->history[$userId][$week]['total'] += $amountEur;
    }
}
