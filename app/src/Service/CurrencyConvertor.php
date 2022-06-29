<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\Currency;
use App\DTO\Money;

class CurrencyConvertor
{
    public function __construct(
        private ExchangeRateGetter $exchangeRateGetter
    ) {
    }

    public function convertTo(Money $money, Currency $currency): Money
    {
        $convertedMoney = (new Money())->setCurrency($currency)->setAmount('0');
        $exchangeRateOldCurrency = $this->exchangeRateGetter->getExchangeRate($money->getCurrency()->getCode());
        $exchangeRateNewCurrency = $this->exchangeRateGetter->getExchangeRate($currency->getCode());
        $convertedMoney->setAmount(bcdiv($money->getAmount(), $exchangeRateOldCurrency->getRate(), 6));
        $convertedMoney->setAmount(bcmul($convertedMoney->getAmount(), $exchangeRateNewCurrency->getRate(), 6));

        return $convertedMoney;
    }
}
