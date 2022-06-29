<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\Currency;
use App\DTO\ExchangeRate;
use App\DTO\Money;
use App\Service\CurrencyConvertor;
use App\Service\ExchangeRateGetter;
use PHPUnit\Framework\TestCase;

class CurrencyConvertorTest extends TestCase
{
    private ExchangeRateGetter $exchangeRateGetter;
    private CurrencyConvertor $currencyConvertor;

    public function setUp(): void
    {
        $this->exchangeRateGetter = $this->createMock(ExchangeRateGetter::class);
        $this->currencyConvertor = (new CurrencyConvertor($this->exchangeRateGetter));
    }

    public function testConvertToDefaultCase(): void
    {
        $eurCurrency = (new Currency())->setCode('EUR');
        $moneyToBeConverted = (new Money())
            ->setCurrency($eurCurrency)
            ->setAmount('100')
        ;
        $bgnCurrency = (new Currency())->setCode('BGN');
        $exchangeRateOldCurrency = (new ExchangeRate())->setCurrency($eurCurrency)->setRate('1');
        $exchangeRateNewCurrency = (new ExchangeRate())->setCurrency($bgnCurrency)->setRate('2');
        $this->exchangeRateGetter
            ->expects($this->exactly(2))
            ->method('getExchangeRate')
            ->withConsecutive(['EUR'], ['BGN'])
            ->willReturnOnConsecutiveCalls($exchangeRateOldCurrency, $exchangeRateNewCurrency)
        ;

        $convertedAmount = $this->currencyConvertor->convertTo($moneyToBeConverted, $bgnCurrency);
        $expectedAmount = (new Money())
            ->setCurrency($bgnCurrency)
            ->setAmount('200.000000')
        ;
        $this->assertEquals($expectedAmount, $convertedAmount);
    }

    public function testConvertToNonDefaultCurrencies(): void
    {
        $eurCurrency = (new Currency())->setCode('EUR');
        $moneyToBeConverted = (new Money())
            ->setCurrency($eurCurrency)
            ->setAmount('100')
        ;
        $bgnCurrency = (new Currency())->setCode('BGN');
        $exchangeRateOldCurrency = (new ExchangeRate())->setCurrency($eurCurrency)->setRate('2');
        $exchangeRateNewCurrency = (new ExchangeRate())->setCurrency($bgnCurrency)->setRate('3');
        $this->exchangeRateGetter
            ->expects($this->exactly(2))
            ->method('getExchangeRate')
            ->withConsecutive(['EUR'], ['BGN'])
            ->willReturnOnConsecutiveCalls($exchangeRateOldCurrency, $exchangeRateNewCurrency)
        ;

        $convertedAmount = $this->currencyConvertor->convertTo($moneyToBeConverted, $bgnCurrency);
        $expectedAmount = (new Money())
            ->setCurrency($bgnCurrency)
            ->setAmount('150.000000')
        ;
        $this->assertEquals($expectedAmount, $convertedAmount);
    }

    public function testConvertToNonDefaultCurrenciesWithBelowOneRates(): void
    {
        $eurCurrency = (new Currency())->setCode('EUR');
        $moneyToBeConverted = (new Money())
            ->setCurrency($eurCurrency)
            ->setAmount('100')
        ;
        $bgnCurrency = (new Currency())->setCode('BGN');
        $exchangeRateOldCurrency = (new ExchangeRate())->setCurrency($eurCurrency)->setRate('0.5');
        $exchangeRateNewCurrency = (new ExchangeRate())->setCurrency($bgnCurrency)->setRate('3');
        $this->exchangeRateGetter
            ->expects($this->exactly(2))
            ->method('getExchangeRate')
            ->withConsecutive(['EUR'], ['BGN'])
            ->willReturnOnConsecutiveCalls($exchangeRateOldCurrency, $exchangeRateNewCurrency)
        ;

        $convertedAmount = $this->currencyConvertor->convertTo($moneyToBeConverted, $bgnCurrency);
        $expectedAmount = (new Money())
            ->setCurrency($bgnCurrency)
            ->setAmount('600.000000')
        ;
        $this->assertEquals($expectedAmount, $convertedAmount);
    }
}
