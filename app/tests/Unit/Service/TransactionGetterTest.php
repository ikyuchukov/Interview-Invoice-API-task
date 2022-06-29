<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\Currency;
use App\DTO\ExchangeRate;
use App\DTO\Money;
use App\DTO\Transaction;
use App\Service\CurrencyConvertor;
use App\Service\ExchangeRateGetter;
use App\Service\TransactionGetter;
use App\Storage\StorageAdapter;
use PHPUnit\Framework\TestCase;

class TransactionGetterTest extends TestCase
{
    private StorageAdapter $storageAdapter;
    private TransactionGetter $transactionGetter;

    public function setUp(): void
    {
        $this->storageAdapter = $this->createMock(StorageAdapter::class);
        $this->transactionGetter = (new TransactionGetter($this->storageAdapter));
    }

    public function testGetTransactionsByVatWhichExist(): void
    {
        $transactionsInStorage = [
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
                )
        ];
        $this->storageAdapter
            ->expects($this->once())
            ->method('get')
            ->with('transaction')
            ->willReturn($transactionsInStorage)
        ;

        $expectedTransactionsForVat = [
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
                )
        ];
        $transactionsForVat = $this->transactionGetter->getTransactionsByVat('123456789');
        $this->assertEquals($expectedTransactionsForVat, $transactionsForVat);
    }


    public function testTransactionByVatWhichDoesNotExist(): void
    {
        $transactions = [
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
                )
        ];
        $this->storageAdapter
            ->expects($this->once())
            ->method('get')
            ->with('transaction')
            ->willReturn($transactions)
        ;
        $transactionsForVat = $this->transactionGetter->getTransactionsByVat('9999999');
        $this->assertEquals([], $transactionsForVat);
    }
}
