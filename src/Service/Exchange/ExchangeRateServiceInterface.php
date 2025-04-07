<?php

namespace App\Service\Exchange;

interface ExchangeRateServiceInterface
{
    public function getRates(): array;

    public function getRate(string $currency): float;
}
