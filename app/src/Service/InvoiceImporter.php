<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\ExchangeRate;
use App\DTO\Transaction;
use App\Exception\InvalidCurrencyException;
use App\Exception\InvalidTransactionException;
use App\Exception\MultipleDefaultCurrenciesProvidedException;
use App\Exception\NoDefaultCurrencyProvidedException;
use App\Storage\StorageAdapter;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InvoiceImporter
{
    public function __construct(
        private DecoderInterface $decoder,
        private ValidatorInterface $validator,
        private DenormalizerInterface $denormalizer,
    ) {
    }

    /**
     * @param string $pathToCsv
     */
    public function importInvoicesFromCsv(string $pathToCsv): void
    {
        $transactionRecords = $this->decoder->decode(file_get_contents($pathToCsv), 'csv');
        $this->validateTransactionArray($transactionRecords);
        $this->denormalizer->denormalize($transactionRecords, Transaction::class);
    }

    /**
     * @param array $transactionRecords
     * @throws InvalidTransactionException
     */
    private function validateTransactionArray(array $transactionRecords): void
    {
        foreach ($transactionRecords as $transactionRecord) {
            $validationErrors = $this->validator->validate(
                $transactionRecord,
                new Assert\Collection(
                    [
                        'Customer' => new Assert\Length(['min' => 1]),
                        //Romania can have VAT Numbers with as low as two numbers
                        'Vat number' => new Assert\Length(['min' => 2]),
                        'Document number' => new Assert\Length(['min' => 1]),
                        'Type' => new Assert\Choice(["1", "2", "3"]),
                        'Parent document' => new Assert\NotNull(),
                        'Currency' => [new Assert\Currency(), new Assert\NotBlank()],
                        'Total' => [new Assert\NotBlank(), new Assert\Type('numeric')]
                    ]
                )
            );

            if (0 !== count($validationErrors)) {
                throw new InvalidTransactionException(
                    sprintf(
                        'Invalid transaction provided %s for value %s type %s',
                        $validationErrors->get(0)->getMessage(),
                        $validationErrors->get(0)->getInvalidValue(),
                        $validationErrors->get(0)->getPropertyPath()
                    )
                );

            }
        }
    }
}
