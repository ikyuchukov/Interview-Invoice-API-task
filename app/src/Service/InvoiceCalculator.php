<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\Transaction;
use App\Exception\InvalidTransactionException;
use App\Exception\NoExchangeRateForCurrencyException;
use App\Storage\StorageAdapter;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InvoiceImporter
{
    public function __construct(
        private StorageAdapter $storageAdapter,
    ) {
    }

    
}
