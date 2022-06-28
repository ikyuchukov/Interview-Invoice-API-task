<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    public function __construct(Valida)
    {

    }

    #[Route('/sumInvoices', name: 'sumInvoices')]
    public function sumInvoices(Request $request): Response
    {
        //use symfony csv decoder
        $invoicesCsv = $request->files->get('file');
        $outputCurrency = $request->get('outputCurrency');
        $exchangeRates = $request->get('exchangeRates', []);
        $customerVat = $request->get('customerVat');

        $this->
        dd($outputCurrency);
    }
}
