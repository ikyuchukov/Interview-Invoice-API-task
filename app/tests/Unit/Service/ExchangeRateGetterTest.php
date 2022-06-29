<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\Currency;
use App\DTO\ExchangeRate;
use App\DTO\Money;
use App\Service\CurrencyConvertor;
use App\Service\ExchangeRateGetter;
use App\Storage\StorageAdapter;
use PHPUnit\Framework\TestCase;

class ExchangeRateGetterTest extends TestCase
{
    private StorageAdapter $storageAdapter;
    private ExchangeRateGetter $exchangeRateGetter;

    public function setUp(): void
    {
        $this->storageAdapter = $this->createMock(StorageAdapter::class);
        $this->exchangeRateGetter = (new ExchangeRateGetter($this->storageAdapter));
    }

    public function testGetExchangeRateWhereItExists(): void
    {
        $eurCurrency = (new Currency())->setCode('EUR');
        $exchangeRateToReturn = ['EUR' => (new ExchangeRate())->setCurrency($eurCurrency)->setRate('1')];
        $this->storageAdapter
            ->expects($this->once())
            ->method('get')
            ->with('exchange_rate')
            ->willReturn($exchangeRateToReturn)
        ;

        $expectedExchangeRate = (new ExchangeRate())->setCurrency($eurCurrency)->setRate('1');
        $exchangeRate = $this->exchangeRateGetter->getExchangeRate('EUR');
        $this->assertEquals($expectedExchangeRate, $exchangeRate);
    }


    public function testGetExchangeRateWhichDoesNotExist(): void
    {
        $this->storageAdapter
            ->expects($this->once())
            ->method('get')
            ->with('exchange_rate')
            ->willReturn(null)
        ;

        $exchangeRate = $this->exchangeRateGetter->getExchangeRate('EUR');
        $this->assertEquals(null, $exchangeRate);
    }
}
