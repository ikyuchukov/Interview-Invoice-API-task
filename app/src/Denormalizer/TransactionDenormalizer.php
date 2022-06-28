<?php
declare(strict_types=1);

namespace App\Denormalizer;

use App\DTO\Currency;
use App\DTO\Money;
use App\DTO\Transaction;
use App\Exception\NoExchangeRateForCurrencyException;
use App\Service\ExchangeRateGetter;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TransactionDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private ExchangeRateGetter $exchangeRateGetter,
    ) {
    }

    /**
     * @param string $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     *
     * @throws NoExchangeRateForCurrencyException
     * @return Transaction
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): Transaction
    {
        if (null === $this->exchangeRateGetter->getExchangeRate($data['Currency'])) {
            throw new NoExchangeRateForCurrencyException(sprintf(
                'No exchange rate found for currency %s',
                $data['Currency'])
            );
        }

        return (new Transaction())
            ->setCustomer($data['Customer'])
            ->setType((int) $data['Type'])
            ->setVat($data['Vat number'])
            ->setDocumentId($data['Document number'])
            ->setParentDocumentId($data['Parent document'])
            ->setTotal(
                (new Money())
                    ->setCurrency((new Currency())->setCode($data['Currency']))
                    ->setAmount($data['Total'])
            )
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
