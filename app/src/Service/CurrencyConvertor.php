<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\Currency;
use App\DTO\Money;
use App\DTO\Transaction;
use App\Exception\InvalidTransactionException;
use App\Exception\NoExchangeRateForCurrencyException;
use App\Storage\StorageAdapter;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
