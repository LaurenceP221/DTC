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

function scaleimage($img_path, $maxw = NULL, $maxh = NULL) {
    $img = getimagesize($img_path);
    if ($img) {
        $w = $img[0];
        $h = $img[1];

        $dim = array('w', 'h');
        foreach ($dim AS $val) {
            $max = "max{$val}";
            if (${$val} > ${$max} && ${$max}) {
                $alt = ($val == 'w') ? 'h' : 'w';
                $ratio = ${$alt} / ${$val};
                ${$val} = ${$max};
                ${$alt} = ${$val} * $ratio;
            }
        }
        return array((int) $w, (int) $h);
    }
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

        if(substr($data[$i],-3) == 'jpg' || substr($data[$i],-3) == 'png')
        {
        $ih = $h - 0.5;
        $iw = $w - 0.5;
        $ix = $x + 0.25;
        $iy = $y + 0.25;

        // adjusted display width/height
        $imgw = $this->scaleimage($data[$i], $iw, $ih)[0];
        $imgh = $this->scaleimage($data[$i], $iw, $ih)[1];

        //show image
        $this->MultiCell($w, 5, $this->Image($data[$i], $ix, $iy, $imgw, $imgh),0,$a);
        }else
        {
        //Print the text
        $this->MultiCell($w,5,$data[$i],0,$a);
        // Put the position to the right of the cell
        $this->SetXY($x+$w,$y);
        }
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
$visiting = mysqli_real_escape_string($conn, $_POST['visiting']);
$period = mysqli_real_escape_string($conn, $_POST['period']);

if ($period== 'today'){
    $visitors = mysqli_query($conn, "SELECT * FROM `visitors` WHERE visiting = '$visiting' 
                            AND time BETWEEN CURDATE() AND NOW()");
}elseif ($period== "yesterday") {
    $visitors = mysqli_query($conn, "SELECT * FROM `visitors` WHERE visiting = '$visiting' 
                            AND time BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY)  AND NOW() " );
}elseif ($period== "week") {
    $visitors = mysqli_query($conn, "SELECT * FROM `visitors` WHERE visiting = '$visiting' 
                            AND time BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY)  AND NOW() " );
}elseif ($period== "month") {
    $visitors = mysqli_query($conn, "SELECT * FROM `visitors` WHERE visiting = '$visiting' 
                            AND time BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY)  AND NOW() " );
}elseif ($period== "all") {
        $visitors = mysqli_query($conn, "SELECT * FROM `visitors` WHERE visiting = '$visiting'" );
}else{
    echo "Error fetching data.";
}

// Instanciation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage("L", "Legal");
$pdf->SetFont('Times','B',12);

// Table with 9 columns
$pdf->SetWidths(array(50, 20, 40, 30, 40, 40, 20, 60, 40));
$pdf->Row(array("Name", "Sex", "Position/Designation", "Affiliation", "Mobile Number", "Email Address",
                "Visiting", "Time", "Sign"));

$pdf->SetFont('Times','',12);
while($row = $visitors->fetch_assoc()){
    $pdf->Row(array($row["name"], $row["sex"], $row["desig"], $row["affil"], 
                    $row["mobileNum"], $row["emailAdd"], $row["visiting"],$row["time"], $row["sign"]));
}                

$pdf->Output();