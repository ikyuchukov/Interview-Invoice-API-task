<?php
declare(strict_types = 1);

namespace App\Tests\Unit\Service;

use App\DTO\Currency;
use App\DTO\CustomerInvoiceSummary;
use App\DTO\Money;
use App\DTO\Transaction;
use App\Exception\NoTransactionsFoundForProvidedVatException;
use App\Service\CurrencyConvertor;
use App\Service\InvoiceCalculator;
use App\Service\TransactionGetter;
use PHPUnit\Framework\TestCase;

class InvoiceCalculatorTest extends TestCase
{
    private CurrencyConvertor $currencyConvertor;
    private TransactionGetter $transactionGetter;
    private InvoiceCalculator $invoiceCalculator;

    public function setUp(): void
    {
        $this->currencyConvertor = $this->createMock(CurrencyConvertor::class);
        $this->transactionGetter = $this->createMock(TransactionGetter::class);
        $this->invoiceCalculator = (new InvoiceCalculator($this->currencyConvertor, $this->transactionGetter));
    }

    /**
     * @dataProvider sumAllInvoicesProvider
     *
     * @param array $transactions
     * @param array $convertedMoney
     * @param array $customerInvoiceSummaries
     * @param Currency $outputCurrency
     */
    public function testSumAllInvoices(
        array $transactions,
        array $convertedMoney,
        array $customerInvoiceSummaries,
        Currency $outputCurrency
    ): void {
        $this->transactionGetter
            ->expects($this->once())
            ->method('getAllTransactions')
            ->willReturn($transactions)
        ;
        $this->currencyConvertor
            ->expects($this->any())
            ->method('convertTo')
            ->willReturnOnConsecutiveCalls(
                ...$convertedMoney
            )
        ;
        $customerInvoiceSummariesResult = $this->invoiceCalculator->sumAllInvoices($outputCurrency);
        $this->assertEquals($customerInvoiceSummaries, $customerInvoiceSummariesResult);
    }

    /**
     * @dataProvider sumInvoicesForClientProvider
     *
     * @param array $transactions
     * @param array $convertedMoney
     * @param array $customerInvoiceSummaries
     * @param Currency $outputCurrency
     */
    public function testSumInvoicesForClient(
        array $transactions,
        array $convertedMoney,
        array $customerInvoiceSummaries,
        Currency $outputCurrency
    ): void {
        $this->transactionGetter
            ->expects($this->once())
            ->method('getTransactionsByVat')
            ->with('1234')
            ->willReturn($transactions)
        ;
        $this->currencyConvertor
            ->expects($this->any())
            ->method('convertTo')
            ->willReturnOnConsecutiveCalls(
                ...$convertedMoney
            )
        ;
        $customerInvoiceSummariesResult = $this->invoiceCalculator->sumInvoicesForClient('1234', $outputCurrency);
        $this->assertEquals($customerInvoiceSummaries[0], $customerInvoiceSummariesResult);
    }

    public function testSumInvoicesForClientNoTransactionFound(): void
    {
        $this->transactionGetter
            ->expects($this->once())
            ->method('getTransactionsByVat')
            ->with('1234')
            ->willReturn([])
        ;
        $this->expectException(NoTransactionsFoundForProvidedVatException::class);
        $customerInvoiceSummariesResult = $this->invoiceCalculator->sumInvoicesForClient('1234', (new Currency())->setCode('USD'));
    }


    public function sumAllInvoicesProvider(): array
    {
        return [
            'USD:1, EUR:2, GBP:0.5' => [
                [
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000257')
                        ->setType(1)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('400')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 2')
                        ->setVat('987654321')
                        ->setDocumentId('1000000258')
                        ->setType(1)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('EUR'))
                                ->setAmount('900')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 3')
                        ->setVat('123465123')
                        ->setDocumentId('1000000259')
                        ->setType(1)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('GBP'))
                                ->setAmount('1300')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000260')
                        ->setType(2)
                        ->setParentDocumentId('1000000257')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('EUR'))
                                ->setAmount('100')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000261')
                        ->setType(3)
                        ->setParentDocumentId('1000000257')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('GBP'))
                                ->setAmount('50')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 2')
                        ->setVat('1000000262')
                        ->setDocumentId('1000000258')
                        ->setType(2)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('200')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 3')
                        ->setVat('123465123')
                        ->setDocumentId('1000000259')
                        ->setType(3)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('EUR'))
                                ->setAmount('100')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000264')
                        ->setType(1)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('EUR'))
                                ->setAmount('1600')
                        ),
                ],
                [
                    (new Money())
                        ->setCurrency((new Currency())->setCode('USD'))
                        ->setAmount('750.000000'),
                    (new Money())
                        ->setCurrency((new Currency())->setCode('USD'))
                        ->setAmount('100.000000'),
                    (new Money())
                        ->setCurrency((new Currency())->setCode('USD'))
                        ->setAmount('450.000000'),
                    (new Money())
                        ->setCurrency((new Currency())->setCode('USD'))
                        ->setAmount('2600.000000'),
                    (new Money())
                        ->setCurrency((new Currency())->setCode('EUR'))
                        ->setAmount('50.000000'),
                ],
                [
                    (new CustomerInvoiceSummary())
                        ->setCustomer('Vendor 1')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('1250.000000')
                        )
                    ,
                    (new CustomerInvoiceSummary())
                        ->setCustomer('Vendor 2')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('250.000000')
                        )
                    ,
                    (new CustomerInvoiceSummary())
                        ->setCustomer('Vendor 3')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('2650.000000')
                        )
                    ,
                ],
                (new Currency())->setCode('USD'),
            ],
            'All are default currency - USD' => [
                [
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000257')
                        ->setType(1)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('400')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 2')
                        ->setVat('987654321')
                        ->setDocumentId('1000000258')
                        ->setType(1)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('900')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 3')
                        ->setVat('123465123')
                        ->setDocumentId('1000000259')
                        ->setType(1)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('1300')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000260')
                        ->setType(2)
                        ->setParentDocumentId('1000000257')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('100')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000261')
                        ->setType(3)
                        ->setParentDocumentId('1000000257')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('50')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 2')
                        ->setVat('1000000262')
                        ->setDocumentId('1000000258')
                        ->setType(2)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('200')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 3')
                        ->setVat('123465123')
                        ->setDocumentId('1000000259')
                        ->setType(3)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('100')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000264')
                        ->setType(1)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('1600')
                        ),
                ],
                [],
                [
                    (new CustomerInvoiceSummary())
                        ->setCustomer('Vendor 1')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('1950.000000')
                        )
                    ,
                    (new CustomerInvoiceSummary())
                        ->setCustomer('Vendor 2')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('700.000000')
                        )
                    ,
                    (new CustomerInvoiceSummary())
                        ->setCustomer('Vendor 3')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('1400.000000')
                        )
                    ,
                ],
                (new Currency())->setCode('USD'),
            ],
        ];
    }

    public function sumInvoicesForClientProvider(): array
    {
        return [
            'USD:1, EUR:2, GBP:0.5' => [
                [
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000257')
                        ->setType(1)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('400')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000260')
                        ->setType(2)
                        ->setParentDocumentId('1000000257')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('EUR'))
                                ->setAmount('100')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000261')
                        ->setType(3)
                        ->setParentDocumentId('1000000257')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('GBP'))
                                ->setAmount('50')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000264')
                        ->setType(1)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('EUR'))
                                ->setAmount('1600')
                        ),
                ],
                [
                    (new Money())
                        ->setCurrency((new Currency())->setCode('USD'))
                        ->setAmount('750.000000'),
                    (new Money())
                        ->setCurrency((new Currency())->setCode('USD'))
                        ->setAmount('100.000000'),
                ],
                [
                    (new CustomerInvoiceSummary())
                        ->setCustomer('Vendor 1')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('1250.000000')
                        )
                ],
                (new Currency())->setCode('USD'),
            ],
            'All are default currency - USD' => [
                [
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000257')
                        ->setType(1)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('400')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000260')
                        ->setType(2)
                        ->setParentDocumentId('1000000257')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('100')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000261')
                        ->setType(3)
                        ->setParentDocumentId('1000000257')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('50')
                        ),
                    (new Transaction())
                        ->setCustomer('Vendor 1')
                        ->setVat('123456789')
                        ->setDocumentId('1000000264')
                        ->setType(1)
                        ->setParentDocumentId()
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('1600')
                        ),
                ],
                [],
                [
                    (new CustomerInvoiceSummary())
                        ->setCustomer('Vendor 1')
                        ->setTotal(
                            (new Money())
                                ->setCurrency((new Currency())->setCode('USD'))
                                ->setAmount('1950.000000')
                        )
                    ,
                ],
                (new Currency())->setCode('USD'),
            ],
        ];
    }
}
