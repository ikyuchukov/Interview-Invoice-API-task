<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\Transaction;
use App\Storage\StorageAdapter;

class TransactionGetter
{
    public function __construct(
        private StorageAdapter $storageAdapter
    ) {
    }

    /**
     * @return Transaction[]
     */
    public function getAllTransactions(): array
    {
        $transactions = $this->storageAdapter->get(StorageAdapter::REPOSITORY_TRANSACTION);
        return $transactions ?? [];
    }

    /**
     * @return Transaction[]
     */
    public function getTransactionsByVat(string $vat): array
    {
        $transactions = $this->storageAdapter->get(StorageAdapter::REPOSITORY_TRANSACTION);
        $transactionsForVat = [];
        foreach ($transactions as $transaction) {
           if ($transaction->getVat() === $vat) {
               $transactionsForVat[] = $transaction;
           }
        }
        return $transactionsForVat ?? [];
    }
}
