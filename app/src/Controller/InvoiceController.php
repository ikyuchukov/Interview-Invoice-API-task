<?php
declare(strict_types=1);

namespace App\Controller;

use App\Validation\CurrencyValidationConstrain;
use Currency;
use ExchangeRate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Currency as CurrencyConstraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InvoiceController extends AbstractController
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
    ) {

    }

    #[Route('/sumInvoices', name: 'sumInvoices')]
    public function sumInvoices(Request $request): Response
    {
        //use symfony csv decoder
        $invoicesCsv = $request->files->get('file');
        $outputCurrency = $request->get('outputCurrency');
        $exchangeRates = $request->get('exchangeRates', []);
        $customerVat = $request->get('customerVat');

        $validationErrors = $this->validator->validate($outputCurrency, (new CurrencyConstraint()));

        dd($validationErrors);
        $outputCurrency = $this->denormalizer->denormalize($exchangeRates, ExchangeRate::class);
        dd($outputCurrency);
    }
}
