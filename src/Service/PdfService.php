<?php

namespace App\Service;

use Dompdf\Adapter\PDFLib;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    private $domPDF;

    public function  __construct()
    {
        $this->domPDF = new DomPDF();
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Garamond');
        $this->domPDF->setOptions($pdfOptions);

    }

    public function showPdfFIle($html){
        $this->domPDF->loadHtml($html);
        $this->domPDF->render();
        $this->domPDF->stream('invoice.pdf');

    }
}