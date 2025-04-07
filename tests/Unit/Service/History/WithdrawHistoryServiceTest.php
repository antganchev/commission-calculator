<?php

namespace App\Tests\Service\History;

use App\Service\History\WithdrawHistoryService;
use PHPUnit\Framework\TestCase;

class WithdrawHistoryServiceTest extends TestCase
{
    private WithdrawHistoryService $historyService;

    protected function setUp(): void
    {
        $this->historyService = new WithdrawHistoryService();
    }

    public function testGetUserWeekHistoryReturnsDefaultWhenNoHistoryExists(): void
    {
        $userId = 1;
        $week = '2025-01-15';

        $history = $this->historyService->getUserWeekHistory($userId, $week);

        $this->assertSame(['count' => 0, 'total' => 0], $history);
    }

    public function testAddTransactionUpdatesHistory(): void
    {
        $userId = 1;
        $week = '2025-01-15';
        $amountEur = 100.00;

        $this->historyService->addTransaction($userId, $week, $amountEur);

        $history = $this->historyService->getUserWeekHistory($userId, $week);

        $this->assertSame(1, $history['count']);
        $this->assertSame(100.00, $history['total']);
    }

    public function testAddTransactionAccumulatesHistory(): void
    {
        $userId = 1;
        $week = '2025-01-15';

        $this->historyService->addTransaction($userId, $week, 100.00);
        $this->historyService->addTransaction($userId, $week, 50.00);

        $history = $this->historyService->getUserWeekHistory($userId, $week);

        $this->assertSame(2, $history['count']);
        $this->assertSame(150.00, $history['total']);
    }

    public function testAddTransactionHandlesMultipleUsersAndWeeks(): void
    {
        $userId1 = 1;
        $userId2 = 2;
        $week1 = '2025-01-15';
        $week2 = '2025-01-22';

        $this->historyService->addTransaction($userId1, $week1, 100.00);
        $this->historyService->addTransaction($userId1, $week2, 50.00);
        $this->historyService->addTransaction($userId2, $week1, 200.00);

        $historyUser1Week1 = $this->historyService->getUserWeekHistory($userId1, $week1);
        $historyUser1Week2 = $this->historyService->getUserWeekHistory($userId1, $week2);
        $historyUser2Week1 = $this->historyService->getUserWeekHistory($userId2, $week1);

        $this->assertSame(['count' => 1, 'total' => 100.00], $historyUser1Week1);
        $this->assertSame(['count' => 1, 'total' => 50.00], $historyUser1Week2);
        $this->assertSame(['count' => 1, 'total' => 200.00], $historyUser2Week1);
    }
}
