<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    #[Route('/generate-pdf', name: 'generate_pdf')]
    public function generatePdf(string $htmlContent, string $filename): string
    {
        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        // Instantiate Dompdf
        $dompdf = new Dompdf($options);

        // Load HTML content
        $dompdf->loadHtml($htmlContent);

        // Render PDF
        $dompdf->render();

        // Output PDF as string
        return $dompdf->output();
    }
}
