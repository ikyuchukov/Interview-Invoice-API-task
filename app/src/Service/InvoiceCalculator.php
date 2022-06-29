<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\Currency;
use App\DTO\CustomerInvoiceSummary;
use App\DTO\Money;
use App\DTO\Transaction;
use App\Exception\NoTransactionsFoundForProvidedVatException;

class InvoiceCalculator
{
    public function __construct(
        private CurrencyConvertor $currencyConvertor,
        private TransactionGetter $transactionGetter,
    ) {
    }

    /**
     * @param Currency $outputCurrency
     *
     * @return CustomerInvoiceSummary[]
     */
    public function sumAllInvoices(Currency $outputCurrency): array
    {
        $transactions = $this->transactionGetter->getAllTransactions();
        $segregatedTransactions = $this->segregateTransactionsByClient($transactions);
        $totalByClient = [];
        foreach ($segregatedTransactions as $client => $customerTransactions) {
            $total = $this->sumInvoices($customerTransactions, $outputCurrency);
            $customerInvoiceSummary = (new CustomerInvoiceSummary())
                ->setCustomer($client)
                ->setTotal($total)
            ;
            $totalByClient[] = $customerInvoiceSummary;
        }

        return $totalByClient;
    }

    /**
     * @param string $vat
     * @param Currency $outputCurrency
     *
     * @throws NoTransactionsFoundForProvidedVatException
     * @return CustomerInvoiceSummary
     */
    public function sumInvoicesForClient(string $vat, Currency $outputCurrency): CustomerInvoiceSummary
    {
        $transactions = $this->transactionGetter->getTransactionsByVat($vat);
        if (0 === count($transactions)) {
            throw new NoTransactionsFoundForProvidedVatException(sprintf(
                'No transactions found for provided VAT %s',
                $vat
            ));
        }
        $total = $this->sumInvoices($transactions, $outputCurrency);
        return (new CustomerInvoiceSummary())
            ->setCustomer($transactions[0]->getCustomer())
            ->setTotal($total)
        ;
    }

    /**
     * @param Transaction[] $transactions
     *
     * @return array [string][Transaction[]]
     */
    private function segregateTransactionsByClient(array $transactions): array
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
