<?php
declare(strict_types=1);

namespace App\Service;

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
        $defaultCurrency = $this->getDefaultCurrency($exchangeRates);
        if (null === $this->storageAdapter->get('exchange_rate')) {
            $this->storageAdapter->set('exchange_rate', []);
        }
        $this->storageAdapter->set('default_currency', $defaultCurrency);
        foreach ($exchangeRates as $exchangeRate) {
            $this->storageAdapter->addWithAssociativeKey(
                'exchange_rate',
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
     * @return ExchangeRate
     */
    private function getDefaultCurrency(array $exchangeRates): ExchangeRate
    {
        $defaultCurrency = null;
        $ratesEqualToOneCounter = 0;
        foreach ($exchangeRates as $exchangeRate) {
            if (0 === bccomp($exchangeRate->getRate(), '1')) {
                $ratesEqualToOneCounter++;
                $defaultCurrency = $exchangeRate;
            }
        }

        if (1 === $ratesEqualToOneCounter) {
            return $defaultCurrency;
        } elseif (0 === $ratesEqualToOneCounter) {
            throw new NoDefaultCurrencyProvidedException('No default currency provided while importing.');
        }

        throw new MultipleDefaultCurrenciesProvidedException('Multiple currency rates equal to 1 provided');
    }
}
