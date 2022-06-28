<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\Currency;
use App\DTO\Transaction;
use App\Exception\InvalidTransactionException;
use App\Exception\NoExchangeRateForCurrencyException;
use App\Storage\StorageAdapter;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InvoiceCalculator
{
    public function __construct(
        private StorageAdapter $storageAdapter,
    ) {
    }

    public function sumAllInvoices(Currency $outputCurrency)
    {
        $transactions = $this->storageAdapter->get(StorageAdapter::REPOSITORY_TRANSACTION);
        $segregatedTransactions = $this->segregateTransactionsByClient($transactions);


    }

    public function sumInvoicesForClient(string $vat)
    {

    }

    /**
     * @param Transaction[] $transactions
     */
    private function segregateTransactionsByClient(array $transactions)
    {
        $transactionsByClient = [];
        foreach ($transactions as $transaction) {
            $transactionsByClient[$transaction->getCustomer()][] = $transaction;
        }

        return $transactionsByClient;
    }
}
