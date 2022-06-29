<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\ExchangeRate;
use App\Storage\StorageAdapter;

class ExchangeRateGetter
{
    public function __construct(
        private StorageAdapter $storageAdapter
    ) {
    }

    public function getExchangeRate(string $currencyCode): ?ExchangeRate
    {
        $exchangeRates = $this->storageAdapter->get(StorageAdapter::REPOSITORY_EXCHANGE_RATE);
        return $exchangeRates[$currencyCode] ?? null;
    }

    public function getDefaultExchangeRate(): ExchangeRate
    {
        return $this->storageAdapter->get(StorageAdapter::REPOSITORY_DEFAULT_CURRENCY);
    }
}
