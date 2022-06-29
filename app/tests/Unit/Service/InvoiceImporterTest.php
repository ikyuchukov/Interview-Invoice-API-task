<?php
declare(strict_types = 1);

namespace App\Tests\Unit\Service;

use App\DTO\Currency;
use App\DTO\Money;
use App\DTO\Transaction;
use App\Service\FileReader;
use App\Service\InvoiceImporter;
use App\Storage\StorageAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InvoiceImporterTest extends TestCase
{
    private DecoderInterface $decoder;
    private ValidatorInterface $validator;
    private DenormalizerInterface $denormalizer;
    private StorageAdapter $storageAdapter;
    private FileReader $fileReader;
    private InvoiceImporter $invoiceImporter;

    public function setUp(): void
    {
        $this->decoder = $this->createMock(DecoderInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->storageAdapter = $this->createMock(StorageAdapter::class);
        $this->fileReader = $this->createMock(FileReader::class);
        $this->invoiceImporter = (new InvoiceImporter(
            $this->decoder,
            $this->validator,
            $this->denormalizer,
            $this->storageAdapter,
            $this->fileReader,
        ));
    }

    public function testImportInvoicesFromCsvDefaultCase(): void
    {
        $transactionRecords = [
            [
                'Customer' => 'Vendor 1',
                'Vat number' => '123456789',
                'Document number' => '1000000257',
                'Type' => '1',
                'Parent document' => '',
                'Currency' => 'USD',
                'Total' => '400',
            ],
            [
                'Customer' => 'Vendor 2',
                'Vat number' => '987654321',
                'Document number' => '1000000258',
                'Type' => '1',
                'Parent document' => '',
                'Currency' => 'EUR',
                'Total' => '900',
            ],
            [
                'Customer' => 'Vendor 3',
                'Vat number' => '123465123',
                'Document number' => '1000000259',
                'Type' => '1',
                'Parent document' => '',
                'Currency' => 'GBP',
                'Total' => '1300',
            ],
            [
                'Customer' => 'Vendor 1',
                'Vat number' => '123456789',
                'Document number' => '1000000260',
                'Type' => '2',
                'Parent document' => '1000000257',
                'Currency' => 'EUR',
                'Total' => '100',
            ],
            [
                'Customer' => 'Vendor 1',
                'Vat number' => '123456789',
                'Document number' => '1000000261',
                'Type' => '3',
                'Parent document' => '1000000257',
                'Currency' => 'GBP',
                'Total' => '50',
            ],
            [
                'Customer' => 'Vendor 2',
                'Vat number' => '987654321',
                'Document number' => '1000000262',
                'Type' => '2',
                'Parent document' => '1000000258',
                'Currency' => 'USD',
                'Total' => '200',
            ],
            [
                'Customer' => 'Vendor 3',
                'Vat number' => '123465123',
                'Document number' => '1000000263',
                'Type' => '3',
                'Parent document' => '1000000259',
                'Currency' => 'EUR',
                'Total' => '100',
            ],
            [
                'Customer' => 'Vendor 1',
                'Vat number' => '123456789',
                'Document number' => '1000000264',
                'Type' => '1',
                'Parent document' => '',
                'Currency' => 'EUR',
                'Total' => '1600',
            ],
        ];
        $this->fileReader
            ->expects($this->once())
            ->method('readFileToString')
            ->with('/tmp/csv')
        ;
        $this->decoder
            ->expects($this->once())
            ->method('decode')
            ->willReturn($transactionRecords)
        ;
        $this->validator
            ->expects($this->exactly(8))
            ->method('validate')
            ->withConsecutive(
                [
                    $transactionRecords[0],
                    new Assert\Collection(
                        [
                            'Customer' => new Assert\Length(['min' => 1]),
                            'Vat number' => new Assert\Length(['min' => 2]),
                            'Document number' => new Assert\Length(['min' => 1]),
                            'Type' => new Assert\Choice(["1", "2", "3"]),
                            'Parent document' => new Assert\NotNull(),
                            'Currency' => [new Assert\Currency(), new Assert\NotBlank()],
                            'Total' => [new Assert\NotBlank(), new Assert\Type('numeric')],
                        ]
                    ),
                ],
                [
                    $transactionRecords[1],
                    new Assert\Collection(
                        [
                            'Customer' => new Assert\Length(['min' => 1]),
                            'Vat number' => new Assert\Length(['min' => 2]),
                            'Document number' => new Assert\Length(['min' => 1]),
                            'Type' => new Assert\Choice(["1", "2", "3"]),
                            'Parent document' => new Assert\NotNull(),
                            'Currency' => [new Assert\Currency(), new Assert\NotBlank()],
                            'Total' => [new Assert\NotBlank(), new Assert\Type('numeric')],
                        ]
                    ),
                ],
                [
                    $transactionRecords[2],
                    new Assert\Collection(
                        [
                            'Customer' => new Assert\Length(['min' => 1]),
                            'Vat number' => new Assert\Length(['min' => 2]),
                            'Document number' => new Assert\Length(['min' => 1]),
                            'Type' => new Assert\Choice(["1", "2", "3"]),
                            'Parent document' => new Assert\NotNull(),
                            'Currency' => [new Assert\Currency(), new Assert\NotBlank()],
                            'Total' => [new Assert\NotBlank(), new Assert\Type('numeric')],
                        ]
                    ),
                ],
                [
                    $transactionRecords[3],
                    new Assert\Collection(
                        [
                            'Customer' => new Assert\Length(['min' => 1]),
                            'Vat number' => new Assert\Length(['min' => 2]),
                            'Document number' => new Assert\Length(['min' => 1]),
                            'Type' => new Assert\Choice(["1", "2", "3"]),
                            'Parent document' => new Assert\NotNull(),
                            'Currency' => [new Assert\Currency(), new Assert\NotBlank()],
                            'Total' => [new Assert\NotBlank(), new Assert\Type('numeric')],
                        ]
                    ),
                ],
                [
                    $transactionRecords[4],
                    new Assert\Collection(
                        [
                            'Customer' => new Assert\Length(['min' => 1]),
                            'Vat number' => new Assert\Length(['min' => 2]),
                            'Document number' => new Assert\Length(['min' => 1]),
                            'Type' => new Assert\Choice(["1", "2", "3"]),
                            'Parent document' => new Assert\NotNull(),
                            'Currency' => [new Assert\Currency(), new Assert\NotBlank()],
                            'Total' => [new Assert\NotBlank(), new Assert\Type('numeric')],
                        ]
                    ),
                ],
                [
                    $transactionRecords[5],
                    new Assert\Collection(
                        [
                            'Customer' => new Assert\Length(['min' => 1]),
                            'Vat number' => new Assert\Length(['min' => 2]),
                            'Document number' => new Assert\Length(['min' => 1]),
                            'Type' => new Assert\Choice(["1", "2", "3"]),
                            'Parent document' => new Assert\NotNull(),
                            'Currency' => [new Assert\Currency(), new Assert\NotBlank()],
                            'Total' => [new Assert\NotBlank(), new Assert\Type('numeric')],
                        ]
                    ),
                ],
                [
                    $transactionRecords[6],
                    new Assert\Collection(
                        [
                            'Customer' => new Assert\Length(['min' => 1]),
                            'Vat number' => new Assert\Length(['min' => 2]),
                            'Document number' => new Assert\Length(['min' => 1]),
                            'Type' => new Assert\Choice(["1", "2", "3"]),
                            'Parent document' => new Assert\NotNull(),
                            'Currency' => [new Assert\Currency(), new Assert\NotBlank()],
                            'Total' => [new Assert\NotBlank(), new Assert\Type('numeric')],
                        ]
                    ),
                ],
                [
                    $transactionRecords[7],
                    new Assert\Collection(
                        [
                            'Customer' => new Assert\Length(['min' => 1]),
                            'Vat number' => new Assert\Length(['min' => 2]),
                            'Document number' => new Assert\Length(['min' => 1]),
                            'Type' => new Assert\Choice(["1", "2", "3"]),
                            'Parent document' => new Assert\NotNull(),
                            'Currency' => [new Assert\Currency(), new Assert\NotBlank()],
                            'Total' => [new Assert\NotBlank(), new Assert\Type('numeric')],
                        ]
                    ),
                ],
            )
        ;
        $this->denormalizer
            ->expects($this->exactly(8))
            ->method('denormalize')
            ->withConsecutive(
                [
                    $transactionRecords[0],
                    Transaction::class,
                ],
                [
                    $transactionRecords[1],
                    Transaction::class,
                ],
                [
                    $transactionRecords[2],
                    Transaction::class,
                ],
                [
                    $transactionRecords[3],
                    Transaction::class,
                ],
                [
                    $transactionRecords[4],
                    Transaction::class,
                ],
                [
                    $transactionRecords[5],
                    Transaction::class,
                ],
                [
                    $transactionRecords[6],
                    Transaction::class,
                ],
                [
                    $transactionRecords[7],
                    Transaction::class,
                ],
            )
            ->willReturnOnConsecutiveCalls(
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
            )
        ;
        $this->invoiceImporter->importInvoicesFromCsv('/tmp/csv');
    }
}
