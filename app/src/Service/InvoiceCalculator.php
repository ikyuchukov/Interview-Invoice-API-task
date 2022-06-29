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

class InvoiceCalculator
{
    public function __construct(
        private StorageAdapter $storageAdapter,
        private ExchangeRateGetter $exchangeRateGetter,
        private CurrencyConvertor $currencyConvertor
    ) {
    }

    public function sumAllInvoices(Currency $outputCurrency)
    {
        $transactions = $this->storageAdapter->get(StorageAdapter::REPOSITORY_TRANSACTION);
        $segregatedTransactions = $this->segregateTransactionsByClient($transactions);
        $totalByClient = [];
        foreach ($segregatedTransactions as $customerTransactions) {
            $totalByClient[] = $this->sumInvoices($customerTransactions, $outputCurrency);
        }


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

    /**
     * @param Transaction[] $transactions
     * @param Currency $outputCurrency
     *
     * @return Money
     */
    private function sumInvoices(array $transactions, Currency $outputCurrency): Money
    {
        /**
         * Calculating by currency first will allow us to make as little currency conversions as possible,
         * resulting in smaller money loss because of conversions
         * */
        $calculatedInvoicesByCurrency = $this->sumInvoicesOfTheSameCurrency($transactions, $outputCurrency);
        $calculatedInvoicesSameCurrency = [];
        foreach ($calculatedInvoicesByCurrency as $currency => $calculatedSumForOneCurrency) {
            if ($currency !== $outputCurrency->getCode()) {
                $calculatedInvoicesSameCurrency[] = $this->currencyConvertor->convertTo(
                    $calculatedSumForOneCurrency,
                    $outputCurrency
                );
            } else {
                $calculatedInvoicesSameCurrency[] = $calculatedSumForOneCurrency;
            }
        }

        $total = (new Money())->setCurrency($outputCurrency)->setAmount('0');
        foreach ($calculatedInvoicesSameCurrency as $calculatedInvoice) {
            $total->setAmount(bcadd($total->getAmount(), $calculatedInvoice->getAmount(), 6));
        }

        return $total;
    }

    private function transactionCurrencyIsSameAsDefault(Transaction $transaction): bool
    {
        return
            $this->exchangeRateGetter->getDefaultExchangeRate()->getCurrency()->getCode()
            === $transaction->getTotal()->getCurrency()->getCode()
        ;
    }

    /**
     * @param Transaction[] $transactions
     * @param Currency $outputCurrency
     *
     * @return Money[]
     */
    private function sumInvoicesOfTheSameCurrency(array $transactions, Currency $outputCurrency): array
    {
        $total = (new Money())->setCurrency($outputCurrency)->setAmount('0');
        $totalPerCurrency = [];
        foreach ($transactions as $transaction) {
            $currentTransactionCurrency = $transaction->getTotal()->getCurrency()->getCode();
            if (!isset($totalPerCurrency[$currentTransactionCurrency])) {
                $totalPerCurrency[$currentTransactionCurrency] = clone($total);
            }
            if (
                Transaction::TYPE_INVOICE === $transaction->getType()
                || Transaction::TYPE_DEBIT === $transaction->getType()
            ) {
                $newTotal = (new Money())
                    ->setAmount(
                        bcadd(
                            $totalPerCurrency[$currentTransactionCurrency]->getAmount(),
                            $transaction->getTotal()->getAmount(),
                            6
                        )
                    )
                    ->setCurrency($transaction->getTotal()->getCurrency());
            } else {
                $newTotal = (new Money())
                    ->setAmount(
                        bcsub(
                            $totalPerCurrency[$currentTransactionCurrency]->getAmount(),
                            $transaction->getTotal()->getAmount(),
                            6
                        )
                    )
                    ->setCurrency($transaction->getTotal()->getCurrency());
            }
            $totalPerCurrency[$currentTransactionCurrency] = $newTotal;
        }

        return $totalPerCurrency;
    }
}