<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\ExchangeRate;
use App\Exception\MultipleDefaultCurrenciesProvidedException;
use App\Exception\NoDefaultCurrencyProvidedException;
use App\Storage\StorageAdapter;

class ExchangeRateGetter
{
    public function __construct(
        private StorageAdapter $storageAdapter
    ) {
    }

    public function getExchangeRate(string $currencyCode)
    {
        return $this->storageAdapter->get(StorageAdapter::REPOSITORY_EXCHANGE_RATE);
    }
}
