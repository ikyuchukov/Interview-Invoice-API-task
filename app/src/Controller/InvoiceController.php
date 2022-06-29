<?php
declare(strict_types = 1);

namespace App\Controller;

use App\DTO\CalculateResponse;
use App\DTO\Currency;
use App\DTO\Customer;
use App\DTO\CustomerInvoiceSummary;
use App\DTO\ExchangeRate;
use App\Exception\InvalidArgumentException;
use App\Exception\InvalidCurrencyException;
use App\Exception\InvalidCurrencyRatesException;
use App\Exception\InvalidVatException;
use App\Exception\NoTransactionsFoundForProvidedVatException;
use App\Service\ExchangeRateImporter;
use App\Service\InvoiceCalculator;
use App\Service\InvoiceImporter;
use App\Validation\ExchangeRateConstrains;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\All as AllConstraint;
use Symfony\Component\Validator\Constraints\Currency as CurrencyConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InvoiceController extends AbstractController
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
        private ExchangeRateConstrains $exchangeRateConstrains,
        private ExchangeRateImporter $exchangeRateImporter,
        private InvoiceImporter $invoiceImporter,
        private InvoiceCalculator $invoiceCalculator,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/sumInvoices', name: 'sumInvoices')]
    public function sumInvoices(Request $request): Response
    {
        /**
         * Depending on architecture needs/design philosophy, this can be substituted by
         * implementing FOSRestBundle and using ParamConverter for all future endpoints,
         * however in this case I think that is considered overengineering
         **/
        $invoicesCsv = $request->files->get('file');
        $outputCurrency = $request->get('outputCurrency', '');
        $exchangeRates = $request->get('exchangeRates', []);
        $customerVat = $request->get('customerVat');

        if (null === $invoicesCsv) {
            return (new JsonResponse(status: 400));
        }

        try {
            $this->validateSumInvoicesArguments($outputCurrency, $exchangeRates, $customerVat);
        } catch (InvalidArgumentException $invalidArgumentException) {
            return (new JsonResponse(status: 400));
        }

        try {
            $exchangeRateDTOs = $this->denormalizeExchangeRates($exchangeRates);
        } catch (InvalidCurrencyException $invalidCurrencyException) {
            return (new JsonResponse(status: 400));
        }

        try {
            $this->exchangeRateImporter->importMultipleRates($exchangeRateDTOs);
        } catch (InvalidCurrencyException $invalidCurrencyException) {
            return (new JsonResponse(status: 400));
        }

        try {
            $this->invoiceImporter->importInvoicesFromCsv($invoicesCsv->getRealPath());
        } catch (InvalidCurrencyException $invalidCurrencyException) {
            return (new JsonResponse(status: 400));
        }

        $outputCurrency = (new Currency())->setCode($outputCurrency);
        if (null !== $customerVat) {
            try {
                $summedInvoices[]= $this->invoiceCalculator->sumInvoicesForClient($customerVat, $outputCurrency);
            } catch (NoTransactionsFoundForProvidedVatException $noTransactionsFoundForProvidedVatException) {
                return (new JsonResponse(status: 404));
            }
        } else {
            $summedInvoices = $this->invoiceCalculator->sumAllInvoices($outputCurrency);
        }

        $calculateResponse = $this->createCalculateResponse($summedInvoices);
        return (new JsonResponse())->setJson($this->serializer->serialize($calculateResponse, JsonEncoder::FORMAT));
    }

    /**
     * @param string $outputCurrency
     * @param array $exchangeRates
     * @param ?string $customerVat
     * @throws InvalidVatException
     * @throws InvalidCurrencyRatesException
     * @throws InvalidCurrencyException
     */
    private function validateSumInvoicesArguments(
        string $outputCurrency,
        array $exchangeRates,
        ?string $customerVat
    ): void {
        if (
            0 < count($this->validator->validate($outputCurrency, [new (new NotBlank()), (new CurrencyConstraint())]))
        ) {
            throw new InvalidCurrencyException(sprintf('Provided Currency %s is invalid', $outputCurrency));
        }

        $validationErrors = $this->validator->validate(
            $exchangeRates, new AllConstraint($this->exchangeRateConstrains->getConstraints())
        );
        if (0 < count($validationErrors)) {
            $validationErrorMessage = '';
            foreach ($validationErrors as $validationError) {
                $validationErrorMessage .= $validationError->getMessage();
            }
            throw new InvalidCurrencyRatesException(
                sprintf(
                    'Provided Currency Rates are invalid %s',
                    $validationErrorMessage
                )
            );
        }

        if (
            $customerVat !== null
            && 0 < count($this->validator->validate($customerVat, new NotBlank()))
        ) {
            throw new InvalidVatException(sprintf('Provided VAT %s is invalid', $customerVat));
        }
    }

    /**
     * @param array $exchangeRates
     *
     * @return ExchangeRate[]
     * @throws InvalidCurrencyException
     * @throws ExceptionInterface
     */
    private function denormalizeExchangeRates(array $exchangeRates): array
    {
        $exchangeRateDTOs = [];
        foreach ($exchangeRates as $exchangeRate) {
            $exchangeRateDTOs[] = $this->denormalizer->denormalize($exchangeRate, ExchangeRate::class);
        }

        return $exchangeRateDTOs;
    }

    /**
     * @param CustomerInvoiceSummary[] $summedInvoices
     *
     * @return CalculateResponse
     */
    private function createCalculateResponse(array $summedInvoices): CalculateResponse
    {
        $calculateResponse = (new CalculateResponse());
        foreach ($summedInvoices as $summedInvoice) {
            $calculateResponse->addCustomer(
                (new Customer())
                    ->setName($summedInvoice->getCustomer())
                    ->setBalance($summedInvoice->getTotal()->getAmount())
            );
        }
        $calculateResponse->setCurrency($summedInvoice->getTotal()->getCurrency()->getCode());

        return $calculateResponse;
    }
}
