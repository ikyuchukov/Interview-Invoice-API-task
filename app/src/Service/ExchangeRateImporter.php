<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\Currency;
use App\DTO\ExchangeRate;
use App\Exception\MultipleDefaultCurrenciesProvidedException;
use App\Exception\NoDefaultCurrencyProvidedException;
use App\Storage\StorageAdapter;

class ExchangeRateImporter
{
    public function __construct(
        private StorageAdapter $storageAdapter
    ) {
    }

    /**
     * @param ExchangeRate[] $exchangeRates
     *
     * @throws NoDefaultCurrencyProvidedException
     * @throws MultipleDefaultCurrenciesProvidedException
     */
    public function importMultipleRates(array $exchangeRates): void
    {
        $defaultCurrency = $this->extractDefaultCurrency($exchangeRates);
        if (null === $this->storageAdapter->get(StorageAdapter::REPOSITORY_EXCHANGE_RATE)) {
            $this->storageAdapter->set(StorageAdapter::REPOSITORY_EXCHANGE_RATE, []);
        }
        $this->storageAdapter->set(StorageAdapter::REPOSITORY_DEFAULT_CURRENCY, $defaultCurrency);
        foreach ($exchangeRates as $exchangeRate) {
            $this->storageAdapter->addWithAssociativeKey(
                StorageAdapter::REPOSITORY_EXCHANGE_RATE,
                $exchangeRate->getCurrency()->getCode(),
                $exchangeRate
            );
        }
    }

    /**
     * @param ExchangeRate[] $exchangeRates
     *
     * @throws MultipleDefaultCurrenciesProvidedException
     * @throws NoDefaultCurrencyProvidedException
     * @return Currency
     */
    private function extractDefaultCurrency(array $exchangeRates): Currency
    {
        $defaultCurrency = null;
        $ratesEqualToOneCounter = 0;
        foreach ($exchangeRates as $exchangeRate) {
            if (0 === bccomp($exchangeRate->getRate(), '1', 6)) {
                $ratesEqualToOneCounter++;
                $defaultCurrency = $exchangeRate;
            }
        }

        if (1 === $ratesEqualToOneCounter) {
            return $defaultCurrency->getCurrency();
        } elseif (0 === $ratesEqualToOneCounter) {
            throw new NoDefaultCurrencyProvidedException('No default currency provided while importing.');
        }

        throw new MultipleDefaultCurrenciesProvidedException('Multiple currency rates equal to 1 provided');
    }
}
