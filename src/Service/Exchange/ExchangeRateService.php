<?php

namespace App\Service\Exchange;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class ExchangeRateService implements ExchangeRateServiceInterface
{
    private const CACHE_TIMEOUT = 900; // 15 minutes

    public function __construct(
        private HttpClientInterface $client,
        private string $apiUrl,
        private string $apiKey,
        private CacheInterface $cache
    ) {
    }

    public function getRates(): array
    {
        $client = $this->client;
        $apiUrl = $this->apiUrl;
        $apiKey = $this->apiKey;

        return $this->cache->get('exchange_rates', function (ItemInterface $item) use ($client, $apiUrl, $apiKey) {

            $item->expiresAfter(self::CACHE_TIMEOUT); // Cache for 15 minutes

            try {
                $response = $client->request('GET', $apiUrl, [
                    'query' => ['access_key' => $apiKey],
                ]);

                $data = $response->toArray();

                if (!($data['success'] ?? true)) {
                    throw new \RuntimeException('Exchange rate API returned an error.');
                }

                return $data['rates'] ?? [];
            } catch (TransportExceptionInterface $e) {
                throw new ServiceUnavailableHttpException(null, 'Failed to fetch exchange rates: ' . $e->getMessage());
            }
        });
    }

    public function getRate(string $currency): float
    {
        $rates = $this->getRates(); // Ensure rates are fetched

        if (!array_key_exists($currency, $rates)) {
            throw new \InvalidArgumentException(sprintf('Exchange rate for currency "%s" not found.', $currency));
        }

        return $rates[$currency];
    }
}
