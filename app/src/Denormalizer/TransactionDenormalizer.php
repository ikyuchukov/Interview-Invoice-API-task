<?php
declare(strict_types=1);

namespace App\Denormalizer;

use App\DTO\Currency;
use App\DTO\ExchangeRate;
use App\DTO\Transaction;
use App\Exception\InvalidCurrencyException;
use App\Validation\ExchangeRateConstrains;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraints\Currency as CurrencyConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransactionDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @param string $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     *
     * @return Transaction
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): Transaction
    {

        return (new Transaction())
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
        return Transaction::class === $type;
    }
}
