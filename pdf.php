<?php
require('fpdf186\fpdf.php');

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
    $this->Cell(135);
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

protected $widths;
protected $aligns;

function SetWidths($w)
{
    // Set the array of column widths
    $this->widths = $w;
}

function SetAligns($a)
{
    // Set the array of column alignments
    $this->aligns = $a;
}



function Row($data)
{
    // Calculate the height of the row
    $nb = 0;
    for($i=0;$i<count($data);$i++)
        $nb = max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    $h = 5*$nb;
    // Issue a page break first if needed
    $this->CheckPageBreak($h);
    // Draw the cells of the row
    for($i=0;$i<count($data);$i++)
    {
        $w = $this->widths[$i];
        $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
        // Save the current position
        $x = $this->GetX();
        $y = $this->GetY();
        // Draw the border
        $this->Rect($x,$y,$w,$h);
        // Print the text
        $this->MultiCell($w,5,$data[$i],0,$a);
        // Put the position to the right of the cell
        $this->SetXY($x+$w,$y);
    }
    // Go to the next line
    $this->Ln($h);
}

function CheckPageBreak($h)
{
    // If the height h would cause an overflow, add a new page immediately
    if($this->GetY()+$h>$this->PageBreakTrigger)
        $this->AddPage($this->CurOrientation);
}

function NbLines($w, $txt)
{
    // Compute the number of lines a MultiCell of width w will take
    if(!isset($this->CurrentFont))
        $this->Error('No font has been set');
    $cw = $this->CurrentFont['cw'];
    if($w==0)
        $w = $this->w-$this->rMargin-$this->x;
    $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
    $s = str_replace("\r",'',(string)$txt);
    $nb = strlen($s);
    if($nb>0 && $s[$nb-1]=="\n")
        $nb--;
    $sep = -1;
    $i = 0;
    $j = 0;
    $l = 0;
    $nl = 1;
    while($i<$nb)
    {
        $c = $s[$i];
        if($c=="\n")
        {
            $i++;
            $sep = -1;
            $j = $i;
            $l = 0;
            $nl++;
            continue;
        }
        if($c==' ')
            $sep = $i;
        $l += $cw[$c];
        if($l>$wmax)
        {
            if($sep==-1)
            {
                if($i==$j)
                    $i++;
            }
            else
                $i = $sep+1;
            $sep = -1;
            $j = $i;
            $l = 0;
            $nl++;
        }
        else
            $i++;
    }
    return $nl;
}
}

include('conn.php');
$visitors = mysqli_query($conn, "SELECT * FROM visitors");

// Instanciation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage("L", "Legal");
$pdf->SetFont('Times','B',12);

for($i=1; $i<=5; $i++)
    $pdf->Cell(0,10,'Printing line number '.$i,0,1);

// Table with 20 rows and  columns
$pdf->SetWidths(array(30, 20, 40, 30, 40, 40, 20, 60));
$pdf->Row(array("Name", "Sex", "Position/Designation", "Affiliation", "Mobile Number", "Email Address",
                "Visiting", "Sign"));

$pdf->SetFont('Times','',12);
while($row = $visitors->fetch_assoc()){
    $pdf->Row(array($row["name"], $row["sex"], $row["desig"], $row["affil"], 
                    $row["mobileNum"], $row["emailAdd"], $row["visiting"]));

    if ($row["sign"] != null){
        $pdf->Image($row["sign"], null, null, 30);
    }
}                

$pdf->Output();
