<?php
// src/Service/PdfGeneratorService.php
namespace App\Entity;

use TCPDF;

class PdfGeneratorService
{
    public function generatePdf($html)
    {
        $pdf = new TCPDF();
        $pdf->AddPage();
        // Add logo to the PDF and scale it to fit the entire page
        $image_file = 'public/img/logo.png';
        $pdf->Image($image_file, 0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0, false, false, false);

        $pdf->writeHTML($html, true, false, true, false, '');
        return $pdf->Output('document.pdf', 'S');
    }
}
?>
