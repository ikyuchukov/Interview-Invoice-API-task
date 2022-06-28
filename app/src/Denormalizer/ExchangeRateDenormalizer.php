<?php
declare(strict_types=1);

namespace App\Denormalizer;

use App\DTO\Currency;
use App\DTO\ExchangeRate;
use App\Exception\InvalidCurrencyException;
use App\Validation\ExchangeRateConstrains;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraints\Currency as CurrencyConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExchangeRateDenormalizer implements DenormalizerInterface
{
    private const CURRENCY_MATCHER_REGEX = '%^(?<currency>[A-Z]{3})%';
    private const RATE_MATCHER_REGEX = '%:(?<rate>\d*.\d*)$%';

    public function __construct(
        private ValidatorInterface $validator,
        private ExchangeRateConstrains $exchangeRateConstrains,
    ) {
    }

    /**
     * @param string $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     * @throws InvalidCurrencyException
     *
     * @return ExchangeRate
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): ExchangeRate
    {

        preg_match(self::CURRENCY_MATCHER_REGEX, $data, $currency);
        if (0 !== count($this->validator->validate($currency['currency'], [new (new NotBlank()), (new CurrencyConstraint())]))) {
            throw new InvalidCurrencyException(sprintf('Provided Currency %s is invalid', $currency['currency']));
        }
        preg_match(self::RATE_MATCHER_REGEX, $data, $rate);

        return (new ExchangeRate())
            ->setCurrency((new Currency())->setCode($currency['currency']))
            ->setRate($rate['rate'])
        ;
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     *
     * @return bool
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return
            ExchangeRate::class === $type
            && 0 === count($this->validator->validate($data, $this->exchangeRateConstrains->getConstraints()))
        ;
    }
}
