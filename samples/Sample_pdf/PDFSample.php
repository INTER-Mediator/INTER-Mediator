<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 14/08/22
 * Time: 14:10
 */
class PDFSample
{

    function processing($contextData, $options)
    {
        $prodId = $contextData[0]['id'];
        $prodName = $contextData[0]['name'];
        $unitPrice = $contextData[0]['unitprice'];
        $pFile = $contextData[0]['photofile'];
        $timestamp = new DateTime();
        $tsString = $timestamp->format("Y-m-d H:i:s");
        $fileName = "{$prodId}.pdf";

        require_once './tcpdf/tcpdf.php';
        $pdf = new TCPDF("P", "mm", "A4", true, "UTF-8");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0, 0);
        $pdf->AddPage();
        $pdf->setTextColor(100, 100, 100);
        $pdf->SetFont('', '', 14);
        $pdf->Text(40, 40, "Product ID: {$prodId}");
        $pdf->Text(40, 50, "Product Name: {$prodName}");
        $pdf->Text(40, 60, "Unit Price: {$unitPrice}");
        $pdf->Text(40, 70, "Today: {$tsString}");
        $pdf->Image("../Sample_products/images/{$pFile}", 40, 80, 100);

        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('X-Frame-Options: SAMEORIGIN');
        $pdf->Output();
    }
}