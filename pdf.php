<?php
require('mc_table.php');

class PDF extends FPDF
{
// Page header
function Header()
{
    // Logo
    $this->Image('assets\logo.png',10,6,30);
    // Arial bold 15
    $this->SetFont('Times','B',25);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(30,20,'Steer Hub Visitation Logbook',0,0,'C');
    // Line break
    $this->Ln(30);
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Times','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}

include('conn.php');

// Instanciation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);
for($i=1;$i<=40;$i++)
    $pdf->Cell(0,10,'Printing line number '.$i,0,1);
$pdf->Output();

