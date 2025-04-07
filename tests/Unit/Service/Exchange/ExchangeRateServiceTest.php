<?php

namespace App\Tests\Service\Exchange;

use App\Service\Exchange\ExchangeRateService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ExchangeRateServiceTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;
    private ExchangeRateService $exchangeRateService;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);

        $this->exchangeRateService = new ExchangeRateService(
            $this->httpClient,
            'https://api.exchangeratesapi.io/latest', // Example API URL
            'test_api_key',           // Example API key
            $this->cache
        );
    }

    public function testGetRatesReturnsCachedRates(): void
    {
        $cachedRates = ['USD' => 1.1, 'EUR' => 1.0];

        $this->cache
            ->method('get')
            ->with('exchange_rates')
            ->willReturn($cachedRates);

        $rates = $this->exchangeRateService->getRates();

        $this->assertSame($cachedRates, $rates);
    }

    public function testGetRatesFetchesFromApiWhenNotCached(): void
    {
        $apiResponseData = [
            'success' => true,
            'rates' => ['USD' => 1.1, 'EUR' => 1.0],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('toArray')
            ->willReturn($apiResponseData);

        $this->httpClient
            ->method('request')
            ->with('GET', 'https://api.exchangeratesapi.io/latest', [
                'query' => ['access_key' => 'test_api_key'],
            ])
            ->willReturn($response);

        $this->cache
            ->method('get')
            ->with('exchange_rates')
            ->willReturnCallback(function ($key, $callback) {
                $item = $this->createMock(ItemInterface::class);
                $item->method('expiresAfter')->with(900); // 15 minutes
                return $callback($item);
            });

        $rates = $this->exchangeRateService->getRates();

        $this->assertSame($apiResponseData['rates'], $rates);
    }

    public function testGetRatesThrowsExceptionOnApiError(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('toArray')
            ->willReturn(['success' => false]);

        $this->httpClient
            ->method('request')
            ->willReturn($response);

        $this->cache
            ->method('get')
            ->willReturnCallback(function ($key, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Exchange rate API returned an error.');

        $this->exchangeRateService->getRates();
    }

    public function testGetRateReturnsCorrectRate(): void
    {
        $cachedRates = ['USD' => 1.1, 'EUR' => 1.0];

        $this->cache
            ->method('get')
            ->with('exchange_rates')
            ->willReturn($cachedRates);

        $rate = $this->exchangeRateService->getRate('USD');

        $this->assertSame(1.1, $rate);
    }

    public function testGetRateThrowsExceptionForUnknownCurrency(): void
    {
        $cachedRates = ['USD' => 1.1, 'EUR' => 1.0];

        $this->cache
            ->method('get')
            ->with('exchange_rates')
            ->willReturn($cachedRates);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Exchange rate for currency "GBP" not found.');

        $this->exchangeRateService->getRate('GBP');
    }

    public function testGetRatesHandlesTransportException(): void
    {
        $this->httpClient
            ->method('request')
            ->willThrowException($this->createMock(TransportExceptionInterface::class));

        $this->cache
            ->method('get')
            ->willReturnCallback(function ($key, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });

        $this->expectException(\Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException::class);
        $this->expectExceptionMessage('Failed to fetch exchange rates');

        $this->exchangeRateService->getRates();
    }
}
