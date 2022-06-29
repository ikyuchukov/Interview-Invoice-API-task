<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\Currency;
use App\DTO\ExchangeRate;
use App\Exception\MultipleDefaultCurrenciesProvidedException;
use App\Exception\NoDefaultCurrencyProvidedException;
use App\Service\ExchangeRateImporter;
use App\Storage\StorageAdapter;
use PHPUnit\Framework\TestCase;

class ExchangeRateImporterTest extends TestCase
{
    private StorageAdapter $storageAdapter;
    private ExchangeRateImporter $exchangeRateImporter;

    public function setUp(): void
    {
        $this->storageAdapter = $this->createMock(StorageAdapter::class);
        $this->exchangeRateImporter = (new ExchangeRateImporter($this->storageAdapter));
    }

    public function testImportMultipleRatesDefaultCase(): void
    {
        $defaultCurrency = (new Currency())->setCode('GBP');
        $exchangeRates = [
            (new ExchangeRate())->setCurrency((new Currency())->setCode('EUR'))->setRate('2'),
            (new ExchangeRate())->setCurrency($defaultCurrency)->setRate('1'),
            (new ExchangeRate())->setCurrency((new Currency())->setCode('USD'))->setRate('3'),
        ];
        $this->storageAdapter
            ->expects($this->once())
            ->method('get')
            ->with('exchange_rate')
            ->willReturn(null)
        ;
        $this->storageAdapter
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(['exchange_rate', []], ['default_currency', $defaultCurrency])
        ;
        $this->storageAdapter
            ->expects($this->exactly(3))
            ->method('addWithAssociativeKey')
            ->withConsecutive(
                ['exchange_rate', 'EUR', $exchangeRates[0]],
                ['exchange_rate', 'GBP', $exchangeRates[1]],
                ['exchange_rate', 'USD', $exchangeRates[2]],
            )
        ;

        $this->exchangeRateImporter->importMultipleRates($exchangeRates);
    }

    public function testImportMultipleRatesNoDefaultCurrency(): void
    {
        $exchangeRates = [
            (new ExchangeRate())->setCurrency((new Currency())->setCode('EUR'))->setRate('2'),
            (new ExchangeRate())->setCurrency((new Currency())->setCode('BGN'))->setRate('2'),
            (new ExchangeRate())->setCurrency((new Currency())->setCode('USD'))->setRate('3'),
        ];

        $this->expectException(NoDefaultCurrencyProvidedException::class);
        $this->exchangeRateImporter->importMultipleRates($exchangeRates);
    }

    public function testImportMultipleRatesMultipleDefaultCurrency(): void
    {
        $exchangeRates = [
            (new ExchangeRate())->setCurrency((new Currency())->setCode('EUR'))->setRate('1'),
            (new ExchangeRate())->setCurrency((new Currency())->setCode('BGN'))->setRate('1'),
            (new ExchangeRate())->setCurrency((new Currency())->setCode('USD'))->setRate('3'),
        ];

        $this->expectException(MultipleDefaultCurrenciesProvidedException::class);
        $this->exchangeRateImporter->importMultipleRates($exchangeRates);
    }
}
